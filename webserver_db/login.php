<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            width: 90%;
            max-width: 400px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .btn-container {
            text-align: center;
            margin-top: 30px;
        }
        
        .login-btn {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
            width: 100%;
        }
        
        .login-btn:hover {
            background-color: #0b7dda;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            margin: 0 10px;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>로그인</h1>
        <form action="login_process.php" method="post">
            <div class="form-group">
                <label for="userid">아이디</label>
                <input type="text" id="userid" name="userid" required>
            </div>
            
            <div class="form-group">
                <label for="userpw">비밀번호</label>
                <input type="password" id="userpw" name="userpw" required>
            </div>
            
            <div class="btn-container">
                <button type="submit" class="login-btn">로그인</button>
            </div>
        </form>
        
        <div class="links">
            <a href="signup.php">회원가입</a>
            <a href="index.php">메인으로</a>
        </div>
    </div>
</body>
</html>
