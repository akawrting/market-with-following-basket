  <?php
// 세션 시작 - 가장 먼저 호출해야 함
session_start();

// DB 연결 정보
$host = "127.0.0.1";
$db = "famarket";
$user = "famarket";
$pass = "qpalzm1029!";

// 사용자 정보 가져오기
$username = "고객";  // 기본값
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>쇼핑백</title>
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
      max-width: 800px;
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
    
    .user-info {
      font-size: 26px;
      font-weight: 600;
      margin-bottom: 5px;
      letter-spacing: -0.5px;
    }
    
    .welcome-text {
      font-size: 16px;
      opacity: 0.9;
      font-weight: 300;
    }
    
    .container {
      max-width: 800px;
      margin: 0 auto;
      padding: 30px 20px;
    }
    
    .shopping-bag {
      background-color: white;
      border-radius: var(--border-radius);
      padding: 40px 30px;
      box-shadow: var(--shadow);
      text-align: center;
      min-height: 350px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      transition: transform 0.3s ease;
    }
    
    .shopping-bag:hover {
      transform: translateY(-5px);
    }
    
    .empty-bag-icon {
      font-size: 90px;
      color: #cbd5e0;
      margin-bottom: 25px;
      animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }
    
    .empty-message {
      font-size: 22px;
      color: #64748b;
      margin-bottom: 30px;
      font-weight: 500;
    }
    
    .checkout-btn {
      background: linear-gradient(87deg, var(--success-color) 0, #2dcecc 100%);
      color: white;
      border: none;
      padding: 14px 30px;
      border-radius: 50px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
      margin-top: 20px;
    }
    
    .checkout-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
    }
    
    .checkout-btn:active {
      transform: translateY(1px);
    }
    
    .item-image {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .no-image {
      width: 70px;
      height: 70px;
      background-color: #f1f3f5;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #adb5bd;
      font-size: 20px;
    }
    
    /* 테이블 스타일링 */
    .shopping-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      margin-top: 20px;
    }
    
    .shopping-table thead th {
      background-color: #f8fafc;
      color: var(--text-color);
      font-weight: 600;
      padding: 15px;
      text-align: left; /* 기본 정렬 */
      border-bottom: 2px solid #edf2f7;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    /* 테이블 헤더 정렬 */
    .shopping-table thead th:first-child { /* 이미지 헤더 */
      border-top-left-radius: 10px;
      text-align: center; /* 이미지 헤더 중앙 정렬 */
    }
    
    .shopping-table thead th:nth-child(2) { /* 상품명 헤더 */
      text-align: left; /* 상품명 헤더 왼쪽 정렬 */
    }

    .shopping-table thead th:nth-child(3) { /* 수량 헤더 */
      text-align: center; /* 수량 헤더 중앙 정렬 */
    }
    
    .shopping-table thead th:last-child { /* 총 가격 헤더 */
      border-top-right-radius: 10px;
      text-align: right; /* 총 가격 헤더 오른쪽 정렬 */
    }
    
    .shopping-table tbody td {
      padding: 15px;
      border-bottom: 1px solid #edf2f7;
      vertical-align: middle; /* 세로 중앙 정렬 */
    }
    
    /* 테이블 바디 셀 정렬 */
    .shopping-table tbody td:first-child { /* 이미지 셀 */
      display: flex; /* flexbox를 사용하여 이미지/아이콘을 완벽하게 중앙 정렬 */
      justify-content: center;
      align-items: center;
    }

    .shopping-table tbody td:nth-child(2) { /* 상품명 셀 */
      text-align: left; /* 상품명 셀 왼쪽 정렬 */
    }

    .shopping-table tbody td:nth-child(3) { /* 수량 셀 */
      text-align: center; /* 수량 셀 중앙 정렬 */
    }

    .shopping-table tbody td:last-child { /* 총 가격 셀 */
      text-align: right; /* 총 가격 셀 오른쪽 정렬 */
    }
    
    .shopping-table tbody tr:last-child td {
      border-bottom: none;
    }
    
    .shopping-table tbody tr:last-child td:first-child {
      border-bottom-left-radius: 10px;
    }
    
    .shopping-table tbody tr:last-child td:last-child {
      border-bottom-right-radius: 10px;
    }
    
    .shopping-table tbody tr {
      transition: all 0.2s ease;
    }
    
    .shopping-table tbody tr:hover {
      background-color: #f8fafc;
      transform: translateY(-2px);
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }
    
    .item-name {
      font-weight: 600;
      color: var(--text-color);
    }
    
    .item-price {
      font-weight: 700;
      color: var(--primary-color);
      /* text-align: right; 이 부분은 이제 td:last-child에서 처리 */
    }
    
    .item-quantity {
      font-weight: 500;
      /* text-align: center; 이 부분은 이제 td:nth-child(3)에서 처리 */
    }
    
    .bag-title {
      font-size: 24px;
      font-weight: 700;
      color: var(--text-color);
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .bag-title i {
      margin-right: 10px;
      color: var(--primary-color);
    }
    
    .total-section {
      margin-top: 30px;
      padding-top: 20px;
      border-top: 2px dashed #edf2f7;
      display: flex;
      justify-content: flex-end;
      align-items: center;
    }
    
    .total-label {
      font-size: 18px;
      font-weight: 600;
      color: var(--text-color);
      margin-right: 15px;
    }
    
    .total-amount {
      font-size: 24px;
      font-weight: 700;
      color: var(--primary-color);
    }

    
    /* 애니메이션 효과 */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .shopping-bag {
      animation: fadeIn 0.5s ease forwards;
    }
    
    /* 반응형 디자인 */
    @media (max-width: 768px) {
      .container {
        padding: 20px 15px;
      }
      
      .shopping-bag {
        padding: 25px 15px;
      }
      
      .user-info {
        font-size: 22px;
      }
      
      .empty-bag-icon {
        font-size: 70px;
      }
      
      .empty-message {
        font-size: 18px;
      }
      
      .shopping-table thead th {
        padding: 10px 5px;
        font-size: 12px;
      }
      
      .shopping-table tbody td {
        padding: 10px 5px;
      }
      
      .item-image, .no-image {
        width: 50px;
        height: 50px;
      }
      
      .bag-title {
        font-size: 20px;
      }
      
      .total-label {
        font-size: 16px;
      }
      
      .total-amount {
        font-size: 20px;
      }
      
      .checkout-btn {
        padding: 12px 25px;
        font-size: 14px;
      }
    }
    .search-btn {
      background: linear-gradient(87deg, var(--primary-color) 0, #825ee4 100%);
      color: white;
      border: none;
      padding: 14px 30px;
      border-radius: 50px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
      margin-top: 20px;
    }

    .search-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
    }

    .search-btn:active {
      transform: translateY(1px);
    }

    .button-container {
      display: flex;
      justify-content: flex-end;
      margin-top: 20px;
    }

    /* 스크롤바 스타일링 */
    ::-webkit-scrollbar {
      width: 8px;
    }
    
    ::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb {
      background: #c5c5c5;
      border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
      background: #a8a8a8;
    }
    
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Pretendard:wght@300;400;500;600;700&family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>


  <div class="header">
    <div class="header-content">
      <button class="back-button" onclick="history.back()">
        <i class="fas fa-arrow-left back-icon"></i>
      </button>
      <div class="user-info"><?php echo htmlspecialchars($username); ?>님</div>
      <div class="welcome-text">오늘도 즐거운 쇼핑 되세요!</div>
    </div>
  </div>

  <!-- 비상정지 모달 -->
  <div id="emergency-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; text-align: center;">
    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 90%; margin: auto;">
      <h2 style="color: #e63946; margin-bottom: 15px;">⚠️ 비상 정지 알림</h2>
      <p style="color: #333; font-size: 18px;">안전 문제로 인해 모든 동작이 일시적으로 중지되었습니다.</p>
      <p style="color: #777; font-size: 14px;">아래 버튼을 눌러 동작을 재개하세요.</p>
      <button id="resume-button" style="margin-top: 20px; padding: 12px 25px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">동작 재개</button>
      <button onclick="closeEmergencyModal()" style="margin-top: 10px; padding: 10px 20px; background: #457b9d; color: white; border: none; border-radius: 5px; cursor: pointer;">닫기</button>
    </div>
  </div>


  
  <div class="container">
    <div class="shopping-bag">
      <div class="empty-bag-icon">
        <i class="fas fa-shopping-cart"></i>
      </div>
      <div class="empty-message">담은 상품이 없습니다.</div>
      <!-- 여기에 search.php로 리다이렉트되는 버튼 추가 -->
      <button class="search-btn" onclick="location.href='search.php';">찾는 상품이 있으신가요?</button>
    </div>
    
    <div class="checkout-container" style="text-align: center; margin-top: 40px;">
      <form action="pay.php" method="POST">
        <button type="submit" class="checkout-btn" style="padding: 16px 40px; font-size: 18px; font-weight: 600; transform: scale(1.3);">
          <i class="fas fa-credit-card" style="margin-right: 10px; font-size: 20px;"></i>결제하기
        </button>
      </form>
    </div>
  </div>

<script>
function updateShoppingBag() {
  fetch("get_shoppingbag.php")
    .then(response => response.json())
    .then(data => {
      const bag = document.querySelector(".shopping-bag");
      bag.innerHTML = "";

      if (data.length === 0) {
        bag.innerHTML = `
          <div class="empty-bag-icon"><i class="fas fa-shopping-cart"></i></div>
          <div class="empty-message">담은 상품이 없습니다.</div>
          <button class="search-btn" onclick="location.href='search.php';">찾는 상품이 있으신가요?</button>`;
        return;
      }

      let html = `<div class="bag-title"><i class="fas fa-shopping-bag"></i> 담은 상품 목록</div>`;
      html += `<table class="shopping-table">
        <thead>
          <tr>
            <th>이미지</th>
            <th>상품명</th>
            <th>수량</th>
            <th>총 가격</th>
          </tr>
        </thead>
        <tbody>`;

      let totalAmount = 0;

      data.forEach(item => {
        let imageHtml = '';
        if (item.image_url && item.image_url.trim() !== '') {
          imageHtml = `<img src="${item.image_url}" class="item-image" alt="${item.itemname}">`;
        } else {
          imageHtml = `<div class="no-image"><i class="fas fa-image"></i></div>`;
        }
        
        html += `<tr>
          <td style="text-align: center;">${imageHtml}</td>
          <td class="item-name">${item.itemname}</td>
          <td class="item-quantity">${item.itemnum}</td>
          <td class="item-price">${item.totalprice.toLocaleString()} 원</td>
        </tr>`;
        
        totalAmount += item.totalprice;
      });

      html += `</tbody></table>`;
      
      html += `
        <div class="total-section">
          <span class="total-label">총 결제금액:</span>
          <span class="total-amount">${totalAmount.toLocaleString()} 원</span>
        </div>
        <div class="button-container">
          <button class="search-btn" onclick="location.href='search.php';">찾는 상품이 있으신가요?</button>
        </div>
      `;
      
      bag.innerHTML = html;
    })
    .catch(error => {
      console.error("장바구니 불러오기 오류:", error);
    });
}

setInterval(updateShoppingBag, 1000);
window.onload = updateShoppingBag;

// 모달 열기/닫기 함수
function openEmergencyModal() {
  const modal = document.getElementById('emergency-modal');
  modal.style.display = 'flex';
  // 자동 닫기 제거
  // setTimeout(closeEmergencyModal, 10000);
}

function closeEmergencyModal() {
  const modal = document.getElementById('emergency-modal');
  modal.style.display = 'none';
}

// WebSocket 연결 및 비상정지 알림 처리
const ws = new WebSocket("ws://127.0.0.1:8989");

ws.onmessage = function (event) {
  const data = JSON.parse(event.data);

  if (data.event === "emergency_stop" && data.status === "activated") {
    console.log("비상정지 알림 수신");
    openEmergencyModal();
  }
};

ws.onerror = function (error) {
  console.error("WebSocket 오류:", error);
};

ws.onclose = function () {
  console.log("WebSocket 연결이 닫혔습니다.");
};

// DOM 준비 후 resume-button 이벤트 등록
window.addEventListener('DOMContentLoaded', () => {
  const resumeBtn = document.getElementById('resume-button');
  if (resumeBtn) {
    resumeBtn.addEventListener('click', () => {
      if (ws.readyState === WebSocket.OPEN) {
        const runCommand = {
          type: "ros2_command",
          command: "run",
          timestamp: Date.now()
        };
        ws.send(JSON.stringify(runCommand));
        console.log("동작 재개(run) 명령어 전송됨");
        closeEmergencyModal();
      } else {
        alert("WebSocket 연결이 열려있지 않습니다. 페이지를 새로고침해주세요.");
      }
    });
  }
});
</script>


</body>
</html>
