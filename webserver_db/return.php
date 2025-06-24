<?php
// return.php - 웹소켓으로 라즈베리파이에 명령어를 자동 전송하는 페이지
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

// 자동 명령어 전송을 위한 플래그 (AJAX 요청에서 사용)
$auto_send = false;
$result = false;

// AJAX 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'auto_send_command') {
        // 네비게이션 명령어 설정
        $ros2_command = "ls";
        
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
    <title>ROS2 명령어 자동 전송</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .status-box {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
        .countdown {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin: 20px 0;
        }
        .result {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <h1>ROS2 명령어 자동 전송</h1>
    <p>이 페이지에 접속하면 10초 후 자동으로 라즈베리파이로 네비게이션 명령어가 전송됩니다.</p>
    
    <div class="status-box">
        <p>상태: <span id="status">준비 중...</span></p>
        <div class="countdown" id="countdown">10</div>
        <p id="message">10초 후 명령어가 자동으로 전송됩니다.</p>
    </div>
    
    <div id="result" class="result" style="display: none;"></div>
    
    <script>
        // 페이지 로드 시 카운트다운 시작
        let countdown = 10;
        let countdownTimer;
        
        window.onload = function() {
            document.getElementById('status').textContent = '카운트다운 중...';
            startCountdown();
        };
        
        function startCountdown() {
            countdownTimer = setInterval(function() {
                countdown--;
                document.getElementById('countdown').textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(countdownTimer);
                    sendCommandAuto();
                }
            }, 1000);
        }
        
        function sendCommandAuto() {
            document.getElementById('status').textContent = '명령어 전송 중...';
            document.getElementById('message').textContent = '라즈베리파이로 명령어를 전송하는 중입니다...';
            
            // AJAX 요청 생성
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo $_SERVER["PHP_SELF"]; ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                const resultDiv = document.getElementById('result');
                resultDiv.style.display = 'block';
                
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            document.getElementById('status').textContent = '전송 완료!';
                            document.getElementById('message').textContent = '명령어가 성공적으로 전송되었습니다. 잠시 후 welcome.php로 이동합니다...';
                            resultDiv.className = 'result success';
                            resultDiv.innerHTML = '<p>네비게이션 명령어가 성공적으로 전송되었습니다!</p>' +
                                '<p>명령어: ros2 action send_goal /navigate_to_pose nav2_msgs/action/NavigateToPose "..."</p>';
                            
                            // 3초 후 welcome.php로 리다이렉트
                            setTimeout(function() {
                                window.location.href = 'http://192.168.137.1:8080/welcome.php';
                            }, 3000);
                        } else {
                            document.getElementById('status').textContent = '전송 실패';
                            document.getElementById('message').textContent = '명령어 전송에 실패했습니다.';
                                                        resultDiv.innerHTML = '<p>명령어 전송에 실패했습니다.</p>' +
                                '<p><a href="http://192.168.137.1:8080/welcome.php">welcome.php로 돌아가기</a></p>';
                        }
                    } catch (e) {
                        document.getElementById('status').textContent = '오류 발생';
                        document.getElementById('message').textContent = '응답 처리 중 오류가 발생했습니다.';
                        resultDiv.className = 'result error';
                        resultDiv.innerHTML = '<p>응답 처리 중 오류가 발생했습니다.</p>' +
                            '<p><a href="http://192.168.137.1:8080/welcome.php">welcome.php로 돌아가기</a></p>';
                    }
                } else {
                    document.getElementById('status').textContent = '서버 오류';
                    document.getElementById('message').textContent = '서버에서 오류가 발생했습니다.';
                    resultDiv.className = 'result error';
                    resultDiv.innerHTML = '<p>서버 오류: ' + xhr.status + '</p>' +
                        '<p><a href="http://192.168.137.1:8080/welcome.php">welcome.php로 돌아가기</a></p>';
                }
            };
            
            xhr.onerror = function() {
                document.getElementById('status').textContent = '네트워크 오류';
                document.getElementById('message').textContent = '네트워크 연결에 문제가 있습니다.';
                const resultDiv = document.getElementById('result');
                resultDiv.style.display = 'block';
                resultDiv.className = 'result error';
                resultDiv.innerHTML = '<p>네트워크 오류가 발생했습니다. 인터넷 연결을 확인해주세요.</p>' +
                    '<p><a href="http://192.168.137.1:8080/welcome.php">welcome.php로 돌아가기</a></p>';
            };
            
            // 요청 전송
            xhr.send('action=auto_send_command');
        }
    </script>
</body>
</html>

