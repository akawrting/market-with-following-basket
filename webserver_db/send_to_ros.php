<?php
// start_shopping.php - 페이지 접속 시 자동으로 웹소켓 명령어를 전송하는 스크립트

require_once __DIR__ . '/vendor/autoload.php';

// 웹소켓 서버 주소
$websocket_server = "ws://192.168.137.1:9090";

/**
 * 웹소켓으로 명령어를 JSON 형식으로 전송
 *
 * @param string $command 실행할 명령어
 * @return bool 성공 여부
 */
function send_command_via_websocket($command) {
    global $websocket_server;

    try {
        // 웹소켓 클라이언트 생성 및 연결
        $client = new \WebSocket\Client($websocket_server, [
            'timeout' => 5
        ]);

        // JSON 데이터 생성
        $data_to_send = json_encode([
            "type" => "ros2_command",
            "command" => $command,
            "timestamp" => time()
        ]);

        // JSON 메시지 전송
        $client->send($data_to_send);
        $client->close();

        return true;
    } catch (Exception $e) {
        error_log("웹소켓 전송 실패: " . $e->getMessage());
        return false;
    }
}

// 전송할 ros2 명령어
$command_to_execute = "ros2 run teleop_control motor_control";

// 명령어 전송
$is_sent = send_command_via_websocket($command_to_execute);

// 상태 메시지 준비
if ($is_sent) {
    $status_message = "명령어가 성공적으로 전송되었습니다!";
    $status_class = "success";
} else {
    $status_message = "명령어 전송에 실패했습니다. 서버를 확인해주세요.";
    $status_class = "error";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>쇼핑 시작</title>
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
    <meta http-equiv="refresh" content="3;url=welcome.php">
</head>
<body>
    <div class="container">
        <h1>쇼핑 시작</h1>
        <p>페이지 접속 시 ROS2 명령어를 자동으로 전송합니다.</p>
        <div class="status-box <?php echo $status_class; ?>">
            <?php echo $status_message; ?>
        </div>
        <p>잠시 후 자동으로 이동합니다...</p>
    </div>
</body>
</html>
