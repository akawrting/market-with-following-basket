<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
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
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .btn-container {
            text-align: center;
            margin-top: 30px;
        }
        
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #45a049;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>회원가입</h1>
        <form action="register_process.php" method="post">
            <div class="form-group">
                <label for="username">아이디</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">비밀번호</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">비밀번호 확인</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <label for="name">이름</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">이메일</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="phone">전화번호</label>
                <input type="tel" id="phone" name="phone" placeholder="01000000000" required>
                <button type="button" id="send-code-btn" style="margin-top:10px;">인증번호 발송</button>
            </div>

            <div class="form-group">
                <label for="verify_code">인증번호</label>
                <input type="text" id="verify_code" placeholder="인증번호 입력" required>
            </div>

            
            <div class="btn-container">
                <button type="submit" class="submit-btn">가입하기</button>
            </div>
        </form>
        
        <a href="index.php" class="back-link">메인으로 돌아가기</a>
    </div>
    
    <script>
        // 비밀번호 확인 검증
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('비밀번호가 일치하지 않습니다.');
            }
        });
    </script>
    <script>
    let verificationPassed = false;

    // 인증번호 발송
    document.getElementById('send-code-btn').addEventListener('click', function() {
        const phone = document.getElementById('phone').value;

        if (!phone.match(/^01[0-9]{8,9}$/)) {
            alert('유효한 전화번호를 입력하세요.');
            return;
        }

        fetch('send_code.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'phone=' + encodeURIComponent(phone)
        })
        .then(response => response.text())
        .then(data => {
            alert(data); // 예: "인증번호가 발송되었습니다."
        });
    });

    // 폼 제출 시 검증
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const phone = document.getElementById('phone').value;
        const verifyCode = document.getElementById('verify_code').value;

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('비밀번호가 일치하지 않습니다.');
            return;
        }

        if (!verifyCode) {
            e.preventDefault();
            alert('인증번호를 입력하세요.');
            return;
        }

        // 인증번호 검증 요청
        e.preventDefault(); // 일단 기본 제출 막음

        fetch('verify_code.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'phone=' + encodeURIComponent(phone) + '&code=' + encodeURIComponent(verifyCode)
        })
        .then(response => response.text())
        .then(result => {
            if (result.trim() === 'success') {
                verificationPassed = true;
                e.target.submit(); // 검증 성공 시 폼 제출
            } else {
                alert('인증번호가 올바르지 않습니다.');
            }
        });

    });
    </script>

</body>
</html>

