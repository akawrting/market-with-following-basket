<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            background-color: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 40px;
            font-weight: 600;
            font-size: 32px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #555;
            font-size: 16px;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 15px;
            border: none;
            background-color: #f7f9fc;
            border-radius: 12px;
            font-size: 16px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            box-shadow: 0 0 0 2px #4a90e2;
        }
        
        .btn-container {
            text-align: center;
            margin-top: 40px;
        }
        
        .submit-btn {
            background: linear-gradient(45deg, #4a90e2, #5ca9fb);
            color: white;
            border: none;
            padding: 16px 40px;
            font-size: 18px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(74, 144, 226, 0.4);
        }
        
        .submit-btn:active {
            transform: translateY(1px);
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            color: #4a90e2;
        }
        
        /* 전화번호 인증 관련 스타일 */
        .phone-verification {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .input-with-button {
            display: flex;
            gap: 10px;
        }
        
        .input-with-button input {
            flex: 1;
        }
        
        .verification-btn {
            background: linear-gradient(45deg, #4a90e2, #5ca9fb);
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            white-space: nowrap;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
        }
        
        .verification-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(74, 144, 226, 0.4);
        }
        
        .verification-btn:active {
            transform: translateY(1px);
        }
        
        .timer {
            color: #4a90e2;
            font-size: 14px;
            font-weight: 500;
            display: none;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .verification-input {
            position: relative;
        }
        
        .password-requirements {
            margin-top: 8px;
            font-size: 14px;
            color: #888;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin-top: 5px;
        }
        
        .requirement-icon {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            margin-right: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: white;
            background-color: #ddd;
        }
        
        .requirement.valid .requirement-icon {
            background-color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>회원가입</h1>
        <form action="register_process.php" method="post" id="signup-form">
            <div class="form-group">
                <label for="username">아이디</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">비밀번호</label>
                <input type="password" id="password" name="password" required>
                <div class="password-requirements">
                    <div class="requirement" id="length-req">
                        <span class="requirement-icon">✓</span>
                        8자 이상
                    </div>
                    <div class="requirement" id="number-req">
                        <span class="requirement-icon">✓</span>
                        숫자 포함
                    </div>
                    <div class="requirement" id="special-req">
                        <span class="requirement-icon">✓</span>
                        특수문자 포함
                    </div>
                </div>
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

            <div class="form-group phone-verification">
                <label for="phone">전화번호</label>
                <div class="input-with-button">
                    <input type="tel" id="phone" name="phone" placeholder="01012345678" required>
                    <button type="button" id="send-code-btn" class="verification-btn">인증번호 발송</button>
                </div>
            </div>

            <div class="form-group">
                <label for="verify_code">인증번호</label>
                <div class="verification-input">
                    <input type="text" id="verify_code" placeholder="인증번호 6자리 입력" maxlength="6" required>
                    <span class="timer" id="verification-timer">3:00</span>
                </div>
                <button type="button" id="verify-code-btn" class="verification-btn" style="margin-top:10px; width:100%;">인증 확인</button>
            </div>
            
            <div class="btn-container">
                <button type="submit" class="submit-btn" id="submit-btn" disabled>가입하기</button>
            </div>
        </form>
        
        <a href="index.php" class="back-link">메인으로 돌아가기</a>
    </div>
    
    <script>
        let timerInterval;
        let verificationPassed = false;
        
        // 비밀번호 요구사항 검증
        const passwordInput = document.getElementById('password');
        const lengthReq = document.getElementById('length-req');
        const numberReq = document.getElementById('number-req');
        const specialReq = document.getElementById('special-req');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            // 8자 이상 검증
            if (password.length >= 8) {
                lengthReq.classList.add('valid');
            } else {
                lengthReq.classList.remove('valid');
            }
            
            // 숫자 포함 검증
            if (/\d/.test(password)) {
                numberReq.classList.add('valid');
            } else {
                numberReq.classList.remove('valid');
            }
            
            // 특수문자 포함 검증
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                specialReq.classList.add('valid');
            } else {
                specialReq.classList.remove('valid');
            }
        });
        
        // 비밀번호 확인 검증
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password === confirmPassword) {
                this.style.borderColor = '#4CAF50';
            } else {
                this.style.borderColor = '#ff3b30';
            }
        });

        // 폼 제출 전 검증
        document.getElementById('signup-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('비밀번호가 일치하지 않습니다.');
                return;
            }
            
            if (!verificationPassed) {
                e.preventDefault();
                alert('전화번호 인증이 필요합니다.');
                return;
            }
        });
        
        // 인증번호 발송
        document.getElementById('send-code-btn').addEventListener('click', function() {
            const phone = document.getElementById('phone').value;

            if (!phone.match(/^01[0-9]{8,9}$/)) {
                alert('유효한 전화번호를 입력하세요.');
                return;
            }
            
            // 타이머 시작
            clearInterval(timerInterval);
            startTimer(180);
            
            this.textContent = '재발송';
            
            fetch('send_code.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'phone=' + encodeURIComponent(phone)
            })
            .then(response => response.text())
            .then(data => {
                // 토스트 메시지 표시
                const toast = document.createElement('div');
                toast.style.position = 'fixed';
                toast.style.bottom = '30px';
                toast.style.left = '50%';
                toast.style.transform = 'translateX(-50%)';
                toast.style.backgroundColor = 'rgba(0,0,0,0.7)';
                toast.style.color = 'white';
                toast.style.padding = '12px 20px';
                toast.style.borderRadius = '8px';
                toast.style.zIndex = '1000';
                toast.textContent = '인증번호가 발송되었습니다';
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.5s';
                    setTimeout(() => document.body.removeChild(toast), 500);
                }, 2000);
            })
            .catch(err => alert('오류가 발생했습니다: ' + err));
        });
        
        // 인증번호 확인
        // 인증번호 확인
document.getElementById('verify-code-btn').addEventListener('click', function() {
    const phone = document.getElementById('phone').value;
    const code = document.getElementById('verify_code').value;
    
    if (!code) {
        alert('인증번호를 입력하세요.');
        return;
    }
    
    this.textContent = '확인 중...';
    this.disabled = true;
    
    fetch('verify_code.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'phone=' + encodeURIComponent(phone) + '&code=' + encodeURIComponent(code)
    })
    .then(response => response.text())
    .then(result => {
        if (result === 'success') {
            clearInterval(timerInterval);
            document.getElementById('verification-timer').style.display = 'none';
            
            // 인증 성공 표시
            document.getElementById('verify_code').style.borderColor = '#4CAF50';
            document.getElementById('verify_code').disabled = true;
            document.getElementById('phone').disabled = true;
            this.textContent = '인증 완료';
            this.style.backgroundColor = '#4CAF50';
            this.disabled = true;
            document.getElementById('send-code-btn').disabled = true;
            
            // 가입하기 버튼 활성화
            document.getElementById('submit-btn').disabled = false;
            
            verificationPassed = true;
        } else {
            this.textContent = '인증 확인';
            this.disabled = false;
            alert('인증번호가 일치하지 않습니다. 다시 확인해주세요.');
            document.getElementById('verify_code').value = '';
        }
    })
    .catch(err => {
        this.textContent = '인증 확인';
        this.disabled = false;
        alert('오류가 발생했습니다: ' + err);
    });
});

        
        // 타이머 함수
        function startTimer(duration) {
            const timer = document.getElementById('verification-timer');
            timer.style.display = 'block';
            
            let timeLeft = duration;
            timerInterval = setInterval(() => {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                
                timer.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                
                if (--timeLeft < 0) {
                    clearInterval(timerInterval);
                    timer.textContent = '만료됨';
                    timer.style.color = '#ff3b30';
                }
            }, 1000);
        }
    </script>
</body>
</html>


