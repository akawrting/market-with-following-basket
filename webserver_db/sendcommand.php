<?php
// control_buttons.php - run과 stop 명령어를 웹소켓으로 전송하는 버튼이 있는 페이지

// Composer autoload 파일 포함 (웹소켓 클라이언트 라이브러리 사용을 위해 필요)
require_once __DIR__ . '/vendor/autoload.php';

// 웹소켓 서버 주소 설정
$websocket_server = "ws://192.168.137.1:8989"; 

/**
 * 웹소켓으로 명령어를 JSON 형식으로 전송하는 함수
 *
 * @param string $command 실행할 명령어 문자열
 * @return bool 전송 성공 여부
 */
function send_command_via_websocket($command) {
    global $websocket_server;

    try {
        // 웹소켓 클라이언트 생성 및 연결 시도
        $client = new \WebSocket\Client($websocket_server, [
            'timeout' => 5, 
        ]);
        
        // 전송할 명령어 데이터를 JSON 형식으로 인코딩
        $data_to_send = json_encode([
            "type" => "ros2_command",
            "command" => $command,
            "timestamp" => time()
        ]);
        
        // 인코딩된 JSON 메시지를 웹소켓 서버로 전송
        $client->send($data_to_send);
        
        // 메시지 전송 후 웹소켓 연결 종료
        $client->close();
        
        return true;
    } catch (Exception $e) {
        error_log("웹소켓 명령어 전송 실패: " . $e->getMessage());
        return false;
    }
}

// POST 요청 처리 (버튼 클릭 시)
$status_message = "";
$status_class = "";
$command_sent = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["run_command"])) {
        // RUN 버튼이 클릭되었을 때
        $is_sent = send_command_via_websocket("run");
        $command_sent = "run";
    } elseif (isset($_POST["stop_command"])) {
        // STOP 버튼이 클릭되었을 때
        $is_sent = send_command_via_websocket("stop");
        $command_sent = "stop";
    }
    
    // 전송 결과에 따라 메시지 설정
    if (isset($is_sent)) {
        if ($is_sent) {
            $status_message = "명령어 '" . htmlspecialchars($command_sent) . "'가 성공적으로 전송되었습니다!";
            $status_class = "success";
        } else {
            $status_message = "명령어 '" . htmlspecialchars($command_sent) . "' 전송에 실패했습니다. 서버 로그를 확인해주세요.";
            $status_class = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>자율주행 제어 시스템</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: inline-block;
            max-width: 600px;
            width: 90%;
        }
        h1 {
            color: #0056b3;
            margin-bottom: 30px;
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
        }
        button {
            padding: 15px 30px;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .run-btn {
            background-color: #28a745;
            color: white;
        }
        .run-btn:hover {
            background-color: #218838;
        }
        .stop-btn {
            background-color: #dc3545;
            color: white;
        }
        .stop-btn:hover {
            background-color: #c82333;
        }
        .status-box {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>자율주행 제어 시스템</h1>
        <p>아래 버튼을 클릭하여 자율주행 시스템을 제어할 수 있습니다.</p>
        
        <div class="button-container">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <button type="submit" name="run_command" class="run-btn">시작 (RUN)</button>
            </form>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <button type="submit" name="stop_command" class="stop-btn">정지 (STOP)</button>
            </form>
        </div>
        
        <?php if (!empty($status_message)): ?>
            <div class="status-box <?php echo $status_class; ?>">
                <?php echo $status_message; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
