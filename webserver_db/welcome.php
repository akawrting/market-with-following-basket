<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>환영합니다</title>
  <style>
    body {
      font-family: 'Noto Sans KR', sans-serif;
      background: linear-gradient(135deg, #6e8efb, #a777e3);
      margin: 0;
      padding: 0;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    
    .container {
      background-color: white;
      border-radius: 20px;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
      width: 90%;
      max-width: 500px;
      padding: 30px;
      text-align: center;
    }
    
    h2 {
      color: #333;
      font-size: 28px;
      margin-bottom: 20px;
    }
    
    .phone-display {
      background-color: #f5f5f5;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 20px;
      font-size: 24px;
      text-align: center;
      min-height: 30px;
      box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .keypad {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
      margin-bottom: 20px;
    }
    
    .key {
      background-color: #f0f0f0;
      border: none;
      border-radius: 10px;
      padding: 15px;
      font-size: 24px;
      cursor: pointer;
      transition: all 0.2s;
      box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }
    
    .key:hover {
      background-color: #e0e0e0;
      transform: translateY(-2px);
    }
    
    .key:active {
      background-color: #d0d0d0;
      transform: translateY(1px);
    }
    
    .action-buttons {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }
    
    .action-button {
      padding: 15px;
      border: none;
      border-radius: 10px;
      font-size: 18px;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .delete-button {
      background-color: #ff6b6b;
      color: white;
    }
    
    .submit-button {
      background: linear-gradient(to right, #6e8efb, #a777e3);
      color: white;
    }
    
    .action-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .error-message {
      color: #ff6b6b;
      font-size: 14px;
      margin-top: 10px;
      display: none;
    }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="container">
    <h2>환영합니다</h2>
    <p>전화번호를 입력하세요</p>
    
    <form method="POST" action="start_process.php" id="phoneForm">
      <input type="hidden" name="phonenum" id="phoneInput">
      <div class="phone-display" id="phoneDisplay">010</div>
      
      <div class="keypad">
        <button type="button" class="key" onclick="addDigit(1)">1</button>
        <button type="button" class="key" onclick="addDigit(2)">2</button>
        <button type="button" class="key" onclick="addDigit(3)">3</button>
        <button type="button" class="key" onclick="addDigit(4)">4</button>
        <button type="button" class="key" onclick="addDigit(5)">5</button>
        <button type="button" class="key" onclick="addDigit(6)">6</button>
        <button type="button" class="key" onclick="addDigit(7)">7</button>
        <button type="button" class="key" onclick="addDigit(8)">8</button>
        <button type="button" class="key" onclick="addDigit(9)">9</button>
        <button type="button" class="key" style="visibility: hidden"></button>
        <button type="button" class="key" onclick="addDigit(0)">0</button>
        <button type="button" class="key" onclick="deleteDigit()">←</button>
      </div>
      
      <div class="action-buttons">
        <button type="button" class="action-button delete-button" onclick="clearPhone()">지우기</button>
        <button type="button" class="action-button submit-button" onclick="submitForm()">시작하기</button>
      </div>
      
      <div class="error-message" id="errorMessage">올바른 전화번호 형식이 아닙니다 (010XXXXXXXX)</div>
    </form>
  </div>
  
  <script>
    // 초기값 설정
    let phoneNumber = "010";
    document.getElementById('phoneDisplay').textContent = phoneNumber;
    
    // 숫자 추가
    function addDigit(digit) {
      if (phoneNumber.length < 11) {
        phoneNumber += digit;
        document.getElementById('phoneDisplay').textContent = formatPhoneNumber(phoneNumber);
      }
    }
    
    // 숫자 삭제
    function deleteDigit() {
      if (phoneNumber.length > 3) {
        phoneNumber = phoneNumber.slice(0, -1);
        document.getElementById('phoneDisplay').textContent = formatPhoneNumber(phoneNumber);
      }
    }
    
    // 전체 지우기 (010은 남김)
    function clearPhone() {
      phoneNumber = "010";
      document.getElementById('phoneDisplay').textContent = phoneNumber;
      document.getElementById('errorMessage').style.display = 'none';
    }
    
    // 전화번호 형식화 (보기 좋게)
    function formatPhoneNumber(number) {
      if (number.length > 7) {
        return number.slice(0, 3) + "-" + number.slice(3, 7) + "-" + number.slice(7);
      } else if (number.length > 3) {
        return number.slice(0, 3) + "-" + number.slice(3);
      }
      return number;
    }
    
    // 폼 제출
        // 폼 제출
    function submitForm() {
      // 전화번호 유효성 검사
      if (phoneNumber.length === 11 && phoneNumber.startsWith('010')) {
        document.getElementById('phoneInput').value = phoneNumber;
        document.getElementById('phoneForm').submit();
      } else {
        document.getElementById('errorMessage').style.display = 'block';
        setTimeout(function() {
          document.getElementById('errorMessage').style.display = 'none';
        }, 3000); // 3초 후에 에러 메시지 숨김
      }
    }
    
    // 키보드 입력 처리 (접근성 향상)
    document.addEventListener('keydown', function(event) {
      const key = event.key;
      if (key >= '0' && key <= '9') {
        addDigit(parseInt(key));
      } else if (key === 'Backspace') {
        deleteDigit();
      } else if (key === 'Enter') {
        submitForm();
      }
    });
  </script>
</body>
</html>

