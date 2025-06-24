<?php
// start_shopping.php - 페이지 접속 시 자동으로 웹소켓 명령어를 전송하는 스크립트

// Composer autoload 파일 포함 (웹소켓 클라이언트 라이브러리 사용을 위해 필요)
require_once __DIR__ . '/vendor/autoload.php';

// 웹소켓 서버 주소 설정
// 라즈베리파이 또는 웹소켓 서버가 실행 중인 노트북의 IP 주소와 포트로 변경해야 해.
$websocket_server = "ws://192.168.137.1:9090"; 

/**
 * 웹소켓으로 명령어를 JSON 형식으로 전송하는 함수
 *
 * @param string $command 실행할 명령어 문자열 (예: "echo \"hello\"")
 * @return bool 전송 성공 여부
 */
function send_command_via_websocket($command) {
    global $websocket_server; // 전역 변수인 웹소켓 서버 주소를 사용

    try {
        // 웹소켓 클라이언트 생성 및 연결 시도
        // 연결 시도 시 최대 5초까지 기다림
        $client = new \WebSocket\Client($websocket_server, [
            'timeout' => 5, 
        ]);
        
        // 전송할 명령어 데이터를 JSON 형식으로 인코딩
        // "type"은 서버에서 어떤 종류의 명령인지 구분하는 데 사용될 수 있어.
        $data_to_send = json_encode([
            "type" => "ros2_command", // 또는 "ros2_command" 등 서버에 맞게
            "command" => $command,     // 실제 실행할 명령어 문자열
            "timestamp" => time()      // 전송 시간 기록 (선택 사항)
        ]);
        
        // 인코딩된 JSON 메시지를 웹소켓 서버로 전송
        $client->send($data_to_send);
        
        // 메시지 전송 후 웹소켓 연결 종료
        $client->close();
        
        return true; // 성공적으로 전송했음을 반환
    } catch (Exception $e) {
        // 웹소켓 연결 실패 또는 메시지 전송 중 오류 발생 시
        // 에러 로그에 기록하여 디버깅에 활용
        error_log("웹소켓 명령어 전송 실패: " . $e->getMessage());
        return false; // 전송 실패했음을 반환
    }
}

// --- 페이지 로드 시 자동으로 명령어 전송 ---
// 전송할 명령어 설정
$command_to_execute = "hello 9090 port"; 

// 명령어 전송 함수 호출
$is_sent = send_command_via_websocket($command_to_execute);

// 전송 결과에 따라 메시지 설정
$status_message = "";
$status_class = "";
if ($is_sent) {
    $status_message = "명령어 '" . htmlspecialchars($command_to_execute) . "'가 성공적으로 전송되었습니다!";
    $status_class = "success";
} else {
    $status_message = "명령어 '" . htmlspecialchars($command_to_execute) . "' 전송에 실패했습니다. 서버 로그를 확인해주세요.";
    $status_class = "error";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>쇼핑 시작 명령어 전송</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 100px;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        h1 {
            color: #0056b3;
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
        <h1>쇼핑 시작</h1>
        <p>페이지 접속 시 자동으로 명령어를 전송합니다.</p>
        <div class="status-box <?php echo $status_class; ?>">
            <?php echo $status_message; ?>
        </div>
        <p>이 페이지는 잠시 후 다른 페이지로 리다이렉트될 수 있습니다.</p>
    </div>
</body>
</html>
