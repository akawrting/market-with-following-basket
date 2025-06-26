<?php
// 세션 시작
session_start();

// DB 연결 정보
$host = "127.0.0.1";
$db = "famarket";
$user = "famarket";
$pass = "qpalzm1029!";

// DB 연결
try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("데이터베이스 연결 실패: " . $e->getMessage());
}

// 상품 정보 가져오기 (10개만)
$stmt = $conn->prepare("SELECT itemid, itemname, itemprice, itemstock, image_url FROM itemtable LIMIT 10");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>상품 검색</title>
  <link href="https://fonts.googleapis.com/css2?family=Pretendard:wght@300;400;500;600;700&family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #5e72e4;
      --secondary-color: #f7fafc;
      --accent-color: #11cdef;
      --success-color: #2dce89;
      --text-color: #32325d;
      --light-text: #8898aa;
      --border-radius: 12px;
      --shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: 'Pretendard', 'Noto Sans KR', sans-serif;
      background-color: #f8f9fe;
      color: var(--text-color);
      line-height: 1.5;
      min-height: 100vh;
    }
    
    .header {
      background: linear-gradient(87deg, var(--primary-color) 0, #825ee4 100%);
      color: white;
      padding: 25px 20px;
      position: relative;
      border-bottom-left-radius: 30px;
      border-bottom-right-radius: 30px;
      box-shadow: var(--shadow);
    }
    
    .header-content {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      align-items: center;
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
      transition: all 0.3s ease;
    }
    
    .back-button:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-50%) scale(1.05);
    }
    
    .back-icon {
      color: white;
      font-size: 18px;
    }
    
    .page-title {
      font-size: 26px;
      font-weight: 600;
      margin-bottom: 5px;
      letter-spacing: -0.5px;
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 30px 20px;
    }
    
    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 25px;
    }
    
    .product-card {
      background-color: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--shadow);
      transition: transform 0.3s ease;
      display: flex;
      flex-direction: column;
    }
    
    .product-card:hover {
      transform: translateY(-5px);
    }
    
    .product-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-bottom: 1px solid #edf2f7;
    }
    
    .no-image {
      width: 100%;
      height: 200px;
      background-color: #f1f3f5;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #adb5bd;
      font-size: 24px;
      border-bottom: 1px solid #edf2f7;
    }
    
    .product-info {
      padding: 20px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }
    
    .product-name {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 10px;
      color: var(--text-color);
    }
    
    .product-price {
      font-size: 20px;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 10px;
    }
    
    .product-stock {
      font-size: 14px;
      color: var(--light-text);
      margin-bottom: 20px;
    }
    
    .product-button {
      background: linear-gradient(87deg, var(--success-color) 0, #2dcecc 100%);
      color: white;
      border: none;
      padding: 12px 0;
      border-radius: 50px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
      margin-top: auto;
      width: 100%;
    }
    
    .product-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
    }
    
    .product-button:active {
      transform: translateY(1px);
    }
    
    /* 모달 스타일 */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s ease;
      pointer-events: none;
    }
    
    .modal.show {
      opacity: 1;
      pointer-events: auto;
    }
    
    .modal-content {
      background-color: white;
      padding: 30px;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      text-align: center;
      max-width: 400px;
      width: 90%;
      transform: translateY(20px);
      transition: transform 0.3s ease;
    }
    
    .modal.show .modal-content {
      transform: translateY(0);
    }
    
    .modal-icon {
      font-size: 50px;
      color: var(--success-color);
      margin-bottom: 20px;
    }
    
    .modal-title {
      font-size: 22px;
      font-weight: 600;
      margin-bottom: 10px;
      color: var(--text-color);
    }
    
    .modal-message {
      font-size: 16px;
      color: var(--light-text);
      margin-bottom: 0;
    }
    
    /* 애니메이션 효과 */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .product-card {
      animation: fadeIn 0.5s ease forwards;
      animation-delay: calc(var(--animation-order) * 0.1s);
      opacity: 0;
    }
    
    /* 반응형 디자인 */
    @media (max-width: 768px) {
      .container {
        padding: 20px 15px;
      }
      
      .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 15px;
      }
      
      .page-title {
        font-size: 22px;
      }
      
      .product-name {
        font-size: 16px;
      }
      
      .product-price {
        font-size: 18px;
      }
      
      .product-button {
        padding: 10px 0;
        font-size: 14px;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="header-content">
      <button class="back-button" onclick="location.href='shoppingbag.php'">
        <i class="fas fa-arrow-left back-icon"></i>
      </button>
      <div class="page-title">상품 검색</div>
    </div>
  </div>
  
  <div class="container">
    <div class="product-grid">
      <?php
      if (count($products) > 0) {
        $animationOrder = 0;
        foreach ($products as $product) {
          $animationOrder++;
          $imageHtml = '';
          if (!empty($product['image_url']) && trim($product['image_url']) !== '') {
            $imageHtml = '<img src="' . htmlspecialchars($product['image_url']) . '" class="product-image" alt="' . htmlspecialchars($product['itemname']) . '">';
          } else {
            $imageHtml = '<div class="no-image"><i class="fas fa-image"></i></div>';
          }
          ?>
          <div class="product-card" style="--animation-order: <?php echo $animationOrder; ?>">
            <?php echo $imageHtml; ?>
            <div class="product-info">
              <div class="product-name"><?php echo htmlspecialchars($product['itemname']); ?></div>
              <div class="product-price"><?php echo number_format($product['itemprice']); ?> 원</div>
              <div class="product-stock">재고: <?php echo $product['itemstock']; ?>개</div>
              <button class="product-button" onclick="sendCommand(<?php echo $product['itemid']; ?>)">상품 선택하기</button>
            </div>
          </div>
          <?php
        }
      } else {
        echo '<div class="no-products">등록된 상품이 없습니다.</div>';
      }
      ?>
    </div>
  </div>
  
  <!-- 모달 -->
  <div class="modal" id="successModal">
    <div class="modal-content">
      <div class="modal-icon">
        <i class="fas fa-shopping-cart"></i>
      </div>
      <div class="modal-title">카트를 따라가세요!</div>
      <div class="modal-message">선택하신 상품으로 안내해 드리겠습니다.</div>
    </div>
  </div>

  <script>
    // 웹소켓 연결 및 명령어 전송 함수
    // 웹소켓 연결 및 명령어 전송 함수
// 웹소켓 연결 및 명령어 전송 함수
function sendCommand(itemId) {
  // 웹소켓 연결
  const socket = new WebSocket('ws://localhost:9090');
  
  socket.onopen = function(e) {
    console.log("웹소켓 연결 성공!");
    
    // 아이템 정보 가져오기
    fetch(`get_item_position.php?itemid=${itemId}`)
      .then(response => response.json())
      .then(item => {
        if (item.error) {
          console.error("아이템 위치 정보 가져오기 오류:", item.error);
          showModal();
          setTimeout(function() {
            window.location.href = 'shoppingbag.php';
          }, 5000);
          return;
        }

        // pos와 orientation 문자열을 그대로 사용
        // 예: item.pos = "{x: 1.255, y: -1.7, z: 0.0}"
        // 예: item.orientation = "{x: 0.0, y: 0.0, z: 0.26, w: 0.96}"
        const ros2Command = `ros2 action send_goal /navigate_to_pose nav2_msgs/action/NavigateToPose "{pose: {header: {frame_id: 'map'}, pose: {position: ${item.pos}, orientation: ${item.orientation}}}}"`;
        
        // JSON 형식으로 메시지 전송
        const message = {
          type: "item_select",
          command: ros2Command,
          item_id: itemId,
          timestamp: Math.floor(Date.now() / 1000)
        };
        
        // JSON 문자열로 변환하여 전송
        socket.send(JSON.stringify(message));
        
        // 모달 표시
        showModal();
        
        // 5초 후 장바구니 페이지로 리다이렉트
        setTimeout(function() {
          window.location.href = 'shoppingbag.php';
        }, 5000);
      })
      .catch(error => {
        console.error("아이템 위치 정보 가져오기 실패:", error);
        showModal(); // 에러가 발생해도 모달은 표시
        setTimeout(function() {
          window.location.href = 'shoppingbag.php';
        }, 5000);
      });
  };
  
  socket.onclose = function(event) {
    if (event.wasClean) {
      console.log(`웹소켓 연결 종료, 코드=${event.code} 이유=${event.reason}`);
    } else {
      console.log('웹소켓 연결이 끊어졌습니다');
    }
  };
  
  socket.onerror = function(error) {
    console.log(`웹소켓 에러: ${error.message}`);
    showModal();
    setTimeout(function() {
      window.location.href = 'shoppingbag.php';
    }, 5000);
  };
}


    
    // 모달 표시 함수
    function showModal() {
      const modal = document.getElementById('successModal');
      modal.style.display = 'flex';
      setTimeout(() => {
        modal.classList.add('show');
      }, 10);
    }
  </script>
</body>
</html>

