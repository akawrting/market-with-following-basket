<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>메인 페이지</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        
        .container {
            text-align: center;
            width: 90%;
            max-width: 500px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 40px;
        }
        
        .button-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .big-button {
            padding: 20px;
            font-size: 18px;
            cursor: pointer;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        
        .big-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .signup-btn {
            background-color: #4CAF50;
        }
        
        .login-btn {
            background-color: #2196F3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>환영합니다!</h1>
        <div class="button-container">
            <button class="big-button signup-btn" onclick="location.href='signup.php'">회원가입</button>
            <button class="big-button login-btn" onclick="location.href='login.php'">로그인</button>
        </div>
    </div>
</body>
</html>
