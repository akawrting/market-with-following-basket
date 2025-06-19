<?php
// 세션 시작
session_start();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>환영합니다</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
            padding: 60px 40px;
            text-align: center;
        }
        
        h1 {
            color: #333;
            font-size: 36px;
            margin-bottom: 40px;
        }
        
        .start-btn {
            background: linear-gradient(45deg, #4CAF50, #8BC34A);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 20px 40px;
            font-size: 24px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
            margin: 20px 0;
        }
        
        .start-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(76, 175, 80, 0.4);
        }
        
        .start-btn:active {
            transform: translateY(1px);
        }
        
        .logo {
            max-width: 200px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="img/logo.jpg" alt="로고" class="logo">
        <h1>스마트 쇼핑을 시작하세요!</h1>
        
        <form action="openning_door.php" method="post">
            <button type="submit" class="start-btn">장바구니 시작</button>
        </form>
    </div>
</body>
<script>
    // 페이지 로드 시 장바구니 비우기 실행
    window.onload = function() {
        clearCart();
    }
    
    // 장바구니 비우기 함수
    function clearCart() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'clear_cart.php', true);  // PHP 파일로 요청
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                console.log('장바구니가 성공적으로 비워졌습니다.');
            } else {
                console.error('장바구니 비우기 실패');
            }
        };
        
        xhr.send();
    }
</script>
</body>
</html>
