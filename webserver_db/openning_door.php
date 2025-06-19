<?php
// 세션 시작
session_start();

// 데이터베이스 연결
require_once 'db_connect.php';

// doorstatus 테이블이 없으면 생성
$conn->query("
    CREATE TABLE IF NOT EXISTS doorstatus (
        status TINYINT DEFAULT 0
    )
");

// 데이터가 있는지 확인
$check_data = $conn->query("SELECT * FROM doorstatus");
if ($check_data->num_rows == 0) {
    // 데이터가 없으면 초기값 삽입
    $conn->query("INSERT INTO doorstatus (status) VALUES (0)");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>카트 준비</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            text-align: center;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
        }
        .message {
            font-size: 24px;
            margin: 30px 0;
            color: #34495e;
        }
        .button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #2980b9;
        }
        .loading {
            display: none;
            margin-top: 20px;
        }
        .loading p {
            font-size: 20px;
            color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>카트 준비</h1>
        
        <div class="message">
            <p>카트의 문을 열고 장바구니를 넣어주세요.</p>
            <p>장바구니를 넣은 후 아래 버튼을 눌러주세요.</p>
        </div>
        
        <button id="confirmBtn" class="button">장바구니 준비 완료</button>
        
        <div id="loading" class="loading">
            <p>장바구니가 준비되었습니다!</p>
            <p>7초 후 쇼핑이 시작됩니다...</p>
        </div>
    </div>

    <script>
        document.getElementById('confirmBtn').addEventListener('click', function() {
            // 버튼 비활성화
            this.disabled = true;
            
            // AJAX 요청으로 DB 업데이트
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_door_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // 로딩 메시지 표시
                    document.getElementById('loading').style.display = 'block';
                    
                    // 7초 후 페이지 이동
                    setTimeout(function() {
                        window.location.href = 'start_shopping.php';
                    }, 7000);
                }
            };
            xhr.send();
        });
    </script>
</body>
</html>
