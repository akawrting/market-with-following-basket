<?php
// 웹소켓 클라이언트 라이브러리 로드
require_once __DIR__ . '/vendor/autoload.php';

// 웹소켓 서버 주소 설정
$websocket_server = "ws://192.168.137.1:8989"; // 노트북 IP 주소로 변경해야 함

/**
 * 웹소켓으로 ROS2 명령어 전송하는 함수
 * @param string $command 실행할 ROS2 명령어
 * @return bool 전송 성공 여부
 */
function send_ros2_command($command) {
    global $websocket_server;
    
    try {
        // 웹소켓 클라이언트 생성 및 연결
        $client = new \WebSocket\Client($websocket_server, [
            'timeout' => 5, // 5초 타임아웃
        ]);
        
        // 명령어 데이터 준비
        $data = json_encode([
            "type" => "ros2_command",
            "command" => $command
        ]);
        
        // 메시지 전송
        $client->send($data);
        
        // 연결 종료
        $client->close();
        
        return true;
    } catch (Exception $e) {
        error_log("웹소켓 명령어 전송 실패: " . $e->getMessage());
        return false;
    }
}

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'send_command') {
        // 네비게이션 명령어 설정
        $ros2_command = "ros2 action send_goal /navigate_to_pose nav2_msgs/action/NavigateToPose \"{pose: {header: {frame_id: 'map'}, pose: {position: {x: 1.255, y: -1.7, z: 0.0}, orientation: {x: 0.0, y: 0.0, z: 0.26, w: 0.96}}}}\"";
        
        // 명령어 전송
        $result = send_ros2_command($ros2_command);
        
        // AJAX 요청에 응답
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ROS2 명령어 전송</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }
        .result {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: none;
        }
    </style>
</head>
<body>
    <h1>ROS2 명령어 전송</h1>
    <p>아래 버튼을 클릭하면 라즈베리파이로 네비게이션 명령어를 전송합니다.</p>
    
    <button id="sendCommand" class="button">네비게이션 명령어 전송</button>
    
    <div id="result" class="result"></div>
    
    <script>
        document.getElementById('sendCommand').addEventListener('click', function() {
            // 버튼 비활성화
            this.disabled = true;
            this.textContent = '전송 중...';
            
            // 결과 영역 초기화
            const resultDiv = document.getElementById('result');
            resultDiv.style.display = 'block';
            resultDiv.textContent = '명령어 전송 중...';
            
            // AJAX 요청 생성
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo $_SERVER["PHP_SELF"]; ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            resultDiv.textContent = '명령어가 성공적으로 전송되었습니다!';
                            resultDiv.style.backgroundColor = '#dff0d8';
                        } else {
                            resultDiv.textContent = '명령어 전송에 실패했습니다.';
                            resultDiv.style.backgroundColor = '#f2dede';
                        }
                    } catch (e) {
                        resultDiv.textContent = '응답 처리 중 오류가 발생했습니다.';
                        resultDiv.style.backgroundColor = '#f2dede';
                    }
                } else {
                    resultDiv.textContent = '서버 오류: ' + xhr.status;
                    resultDiv.style.backgroundColor = '#f2dede';
                }
                
                // 버튼 다시 활성화
                document.getElementById('sendCommand').disabled = false;
                document.getElementById('sendCommand').textContent = '네비게이션 명령어 전송';
            };
            
            xhr.onerror = function() {
                resultDiv.textContent = '네트워크 오류가 발생했습니다.';
                resultDiv.style.backgroundColor = '#f2dede';
                
                // 버튼 다시 활성화
                document.getElementById('sendCommand').disabled = false;
                document.getElementById('sendCommand').textContent = '네비게이션 명령어 전송';
            };
            
            // 요청 전송
            xhr.send('action=send_command');
        });
    </script>
</body>
</html>
