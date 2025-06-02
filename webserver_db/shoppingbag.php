<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>쇼핑백</title>
  <style>
    body {
      font-family: 'Noto Sans KR', sans-serif;
      background-color: #f8f9fa;
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }
    
    .header {
      background: linear-gradient(135deg, #6e8efb, #a777e3);
      color: white;
      padding: 20px;
      text-align: center;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      position: relative;
    }
    
    .user-info {
      font-size: 24px;
      font-weight: 500;
    }
    
    .welcome-text {
      font-size: 16px;
      margin-top: 5px;
      opacity: 0.9;
    }
    
    .container {
      max-width: 800px;
      margin: 30px auto;
      padding: 20px;
    }
    
    .shopping-bag {
      background-color: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
      text-align: center;
      min-height: 300px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }
    
    .empty-bag-icon {
      font-size: 80px;
      color: #d1d8e0;
      margin-bottom: 20px;
    }
    
    .empty-message {
      font-size: 20px;
      color: #758398;
      margin-bottom: 30px;
    }
    
    .back-button {
      position: absolute;
      left: 20px;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(255, 255, 255, 0.2);
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    
    .back-icon {
      color: white;
      font-size: 20px;
    }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
  <?php
  // 세션 시작
  session_start();
  
  // DB 연결 정보
  $host = "127.0.0.1";
  $db = "famarket";
  $user = "famarket";
  $pass = "qpalzm1029!";
  
  // 사용자 정보 가져오기
  $username = "고객";  // 기본값
  
  // 세션에서 전화번호 가져오기
  $input_phonenum = $_SESSION['phonenum'] ?? '';
  
  if (!empty($input_phonenum)) {
    try {
      // DB 연결
      $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      
      // 전화번호로 사용자 이름 조회
      $stmt = $conn->prepare("SELECT username FROM usertbl WHERE phonenum = :phonenum");
      $stmt->bindParam(':phonenum', $input_phonenum);
      $stmt->execute();
      $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
      
      // 전화번호로 userid도 조회
      $stmt = $conn->prepare("SELECT username, userid FROM usertbl WHERE phonenum = :phonenum");
      $stmt->bindParam(':phonenum', $input_phonenum);
      $stmt->execute();
      $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user_data) {
        $username = $user_data['username'];
        $_SESSION['userid'] = $user_data['userid'];  // 이 줄을 추가
      }

      if ($user_data && isset($user_data['username'])) {
        $username = $user_data['username'];
      }
    } catch (PDOException $e) {
      // 오류 발생 시 기본값 사용
    }
  }
  ?>

  <div class="header">
    <button class="back-button" onclick="history.back()">
      <i class="fas fa-arrow-left back-icon"></i>
    </button>
    <div class="user-info"><?php echo htmlspecialchars($username); ?>님</div>
    <div class="welcome-text">오늘도 즐거운 쇼핑 되세요!</div>
  </div>
  
  <div class="container">
    <div class="shopping-bag">
      <div class="empty-bag-icon">
        <i class="fas fa-shopping-bag"></i>
      </div>
      <div class="empty-message">담은 상품이 없습니다.</div>
    </div>
  </div>

  <div style="text-align: center; margin-top: 20px;">
    <form action="pay.php" method="POST">
      <button type="submit" style="padding: 10px 20px; font-size: 18px; background-color: #6e8efb; color: white; border: none; border-radius: 8px; cursor: pointer;">
        결제하기
      </button>
    </form>
  </div>
<script>
function updateShoppingBag() {
  fetch("get_shoppingbag.php")
    .then(response => response.json())
    .then(data => {
      const bag = document.querySelector(".shopping-bag");
      bag.innerHTML = ""; // 초기화

      if (data.length === 0) {
        bag.innerHTML = `
          <div class="empty-bag-icon"><i class="fas fa-shopping-bag"></i></div>
          <div class="empty-message">담은 상품이 없습니다.</div>`;
        return;
      }

      let html = `<h2 style="margin-bottom: 20px;">🛍️ 담은 상품 목록</h2>`;
      html += `<table style="width: 100%; border-collapse: collapse;">
        <thead>
          <tr style="background-color: #f1f3f5;">
            <th style="padding: 10px;">상품명</th>
            <th style="padding: 10px;">수량</th>
            <th style="padding: 10px;">총 가격</th>
          </tr>
        </thead>
        <tbody>`;

      data.forEach(item => {
        html += `<tr>
          <td style="padding: 10px;">${item.itemname}</td>
          <td style="padding: 10px; text-align: center;">${item.itemnum}</td>
          <td style="padding: 10px; text-align: right;">${item.totalprice.toLocaleString()} 원</td>
        </tr>`;
      });

      html += `</tbody></table>`;
      bag.innerHTML = html;
    })
    .catch(error => {
      console.error("장바구니 불러오기 오류:", error);
    });
}

// 1초마다 장바구니 업데이트
setInterval(updateShoppingBag, 1000);

// 페이지 로딩 시 첫 실행
window.onload = updateShoppingBag;
</script>

</body>
</html>
