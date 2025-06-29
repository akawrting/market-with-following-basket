<?php
// 세션 시작
session_start();

// DB 연결 정보
$host = "127.0.0.1";
$db = "famarket";
$user = "famarket";
$pass = "qpalzm1029!";

// 사용자 정보 가져오기
$username = "고객";  // 기본값, 필요 시 세션이나 DB에서 동적 가져오기
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>쇼핑백</title>
  <style>
    /* CSS는 그대로 유지 */
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
    /* ... 나머지 CSS 동일 ... */
  </style>
  <link
    href="https://fonts.googleapis.com/css2?family=Pretendard:wght@300;400;500;600;700&family=Noto+Sans+KR:wght@300;400;500;700&display=swap"
    rel="stylesheet"
  />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
  />
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
  <div
    id="emergency-modal"
    style="
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      text-align: center;
      flex-direction: column;
    "
  >
    <div
      style="
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        max-width: 90%;
        margin: auto;
      "
    >
      <h2 style="color: #e63946; margin-bottom: 15px;">⚠️ 비상 정지 알림</h2>
      <p style="color: #333; font-size: 18px;">
        안전 문제로 인해 모든 동작이 일시적으로 중지되었습니다.
      </p>
      <p style="color: #777; font-size: 14px;">
        알림은 10초 후 자동으로 사라집니다.
      </p>
      <button
        onclick="closeEmergencyModal()"
        style="
          margin-top: 20px;
          padding: 10px 20px;
          background: #457b9d;
          color: white;
          border: none;
          border-radius: 5px;
          cursor: pointer;
        "
      >
        닫기
      </button>
    </div>
  </div>

  <div class="container">
    <div class="shopping-bag">
      <div class="empty-bag-icon"><i class="fas fa-shopping-cart"></i></div>
      <div class="empty-message">담은 상품이 없습니다.</div>
      <button
        class="search-btn"
        onclick="location.href='search.php';"
      >
        찾는 상품이 있으신가요?
      </button>
    </div>

    <div class="checkout-container" style="text-align: center; margin-top: 40px;">
      <form action="pay.php" method="POST">
        <button
          type="submit"
          class="checkout-btn"
          style="padding: 16px 40px; font-size: 18px; font-weight: 600; transform: scale(1.3);"
        >
          <i
            class="fas fa-credit-card"
            style="margin-right: 10px; font-size: 20px;"
          ></i
          >결제하기
        </button>
      </form>
    </div>
  </div>

  <script>
    // 쇼핑백 업데이트 함수
    function updateShoppingBag() {
      fetch("get_shoppingbag.php")
        .then((response) => response.json())
        .then((data) => {
          const bag = document.querySelector(".shopping-bag");
          bag.innerHTML = ""; // 초기화

          if (!Array.isArray(data) || data.length === 0) {
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

          data.forEach((item) => {
            let imageHtml = "";
            if (item.image_url && item.image_url.trim() !== "") {
              imageHtml = `<img src="${item.image_url}" class="item-image" alt="${item.itemname}">`;
            } else {
              imageHtml = `<div class="no-image"><i class="fas fa-image"></i></div>`;
            }

            // totalprice가 숫자인지 확인 후 toLocaleString 적용
            const price = Number(item.totalprice);
            const priceText = isNaN(price)
              ? "0 원"
              : price.toLocaleString() + " 원";

            html += `<tr>
              <td style="text-align: center;">${imageHtml}</td>
              <td class="item-name">${item.itemname}</td>
              <td class="item-quantity">${item.itemnum}</td>
              <td class="item-price">${priceText}</td>
            </tr>`;

            if (!isNaN(price)) totalAmount += price;
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
        .catch((error) => {
          console.error("장바구니 불러오기 오류:", error);
        });
    }

    // 1초마다 장바구니 업데이트
    setInterval(updateShoppingBag, 1000);

    // 페이지 로딩 시 첫 실행
    window.onload = updateShoppingBag;

    // 모달 열기
    function openEmergencyModal() {
      const modal = document.getElementById("emergency-modal");
      modal.style.display = "flex"; // flex로 중앙 표시
      setTimeout(closeEmergencyModal, 10000); // 10초 후 자동 닫기
    }

    // 모달 닫기
    function closeEmergencyModal() {
      const modal = document.getElementById("emergency-modal");
      modal.style.display = "none";
    }

    // WebSocket 연결 설정 (서버 IP를 정확히 지정해야 함)
    // 만약 클라이언트와 서버가 동일 PC라면 127.0.0.1 가능
    // 다르면 서버 IP로 변경 필요 (예: ws://192.168.x.x:8989)
    const ws = new WebSocket("ws://127.0.0.1:8989");

    ws.onopen = function () {
      console.log("WebSocket 연결 성공");
    };

    ws.onmessage = function (event) {
      try {
        const data = JSON.parse(event.data);
        if (data.event === "emergency_stop" && data.status === "activated") {
          console.log("비상정지 알림 수신");
          openEmergencyModal();
        }
      } catch (e) {
        console.error("WebSocket 메시지 파싱 오류:", e);
      }
    };

    ws.onerror = function (error) {
      console.error("WebSocket 오류:", error);
    };

    ws.onclose = function () {
      console.log("WebSocket 연결이 닫혔습니다.");
    };
  </script>
</body>
</html>
