<?php
// 세션 시작
session_start();

// 데이터베이스 연결
require_once 'db_connect.php';

// 로그인 상태 확인 (userid가 세션에 저장되어 있는지)
if (!isset($_SESSION['userid'])) {
    // 로그인되지 않은 경우 로그인 페이지로 리다이렉트
    header("Location: login.php");
    exit;
}

$userid = $_SESSION['userid'];

// 사용자 정보 가져오기
$stmt = $conn->prepare("SELECT * FROM usertbl WHERE userid = ?");
$stmt->bind_param("s", $userid);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows == 0) {
    // 사용자 정보가 없는 경우
    echo "사용자 정보를 찾을 수 없습니다.";
    exit;
}

$user = $user_result->fetch_assoc();

// 구매 내역 가져오기
$stmt = $conn->prepare("
    SELECT ph.*, COUNT(pi.item_id) as item_count 
    FROM purchase_history ph
    LEFT JOIN purchase_items pi ON ph.purchase_id = pi.purchase_id
    WHERE ph.userid = ?
    GROUP BY ph.purchase_id
    ORDER BY ph.purchase_date DESC
");
$stmt->bind_param("s", $user['userid']);
$stmt->execute();
$purchases_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>마이페이지</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .logo {
            max-width: 120px;
        }
        
        h1, h2, h3 {
            color: #333;
            margin-top: 0;
        }
        
        .user-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .points {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
        }
        
        .purchase-list {
            margin-top: 30px;
        }
        
        .purchase-item {
            background-color: white;
            border: 1px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .purchase-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .purchase-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .purchase-date {
            color: #6c757d;
            font-size: 14px;
        }
        
        .purchase-amount {
            font-weight: bold;
            color: #495057;
            font-size: 18px;
        }
        
        .purchase-details {
            display: none;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .item-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .item-table th, .item-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .item-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .logout-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background-color: #5a6268;
        }
        
        .no-purchases {
            text-align: center;
            padding: 40px 0;
            color: #6c757d;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>마이페이지</h1>
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">로그아웃</button>
            </form>
        </header>
        
        <div class="user-info">
            <h2><?php echo htmlspecialchars($user['username']); ?>님 환영합니다!</h2>
            <p>전화번호: <?php echo htmlspecialchars($user['phonenum']); ?></p>
            <p>보유 포인트: <span class="points"><?php echo number_format($user['points']); ?> P</span></p>
        </div>
        
        <div class="purchase-list">
            <h2>구매 내역</h2>
            
            <?php if ($purchases_result->num_rows > 0): ?>
                <?php while ($purchase = $purchases_result->fetch_assoc()): ?>
                    <div class="purchase-item" onclick="toggleDetails(<?php echo $purchase['purchase_id']; ?>)">
                        <div class="purchase-header">
                            <div class="purchase-date"><?php echo date('Y년 m월 d일 H:i', strtotime($purchase['purchase_date'])); ?></div>
                            <div class="purchase-amount"><?php echo number_format($purchase['total_amount']); ?>원</div>
                        </div>
                        <div>구매 상품 <?php echo $purchase['item_count']; ?>개</div>
                        
                        <div id="details-<?php echo $purchase['purchase_id']; ?>" class="purchase-details">
                            <?php
                            // 구매 상세 내역 가져오기
                            $detail_stmt = $conn->prepare("
                                SELECT * FROM purchase_items 
                                WHERE purchase_id = ?
                                ORDER BY itemname
                            ");
                            $detail_stmt->bind_param("i", $purchase['purchase_id']);
                            $detail_stmt->execute();
                            $items_result = $detail_stmt->get_result();
                            ?>
                            
                            <table class="item-table">
                                <thead>
                                    <tr>
                                        <th>상품명</th>
                                        <th>수량</th>
                                        <th>가격</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $items_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['itemname']); ?></td>
                                        <td><?php echo $item['itemnum']; ?>개</td>
                                        <td><?php echo number_format($item['totalprice']); ?>원</td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-purchases">
                    <p>아직 구매 내역이 없습니다.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleDetails(purchaseId) {
            var detailsDiv = document.getElementById('details-' + purchaseId);
            if (detailsDiv.style.display === 'block') {
                detailsDiv.style.display = 'none';
            } else {
                detailsDiv.style.display = 'block';
            }
        }
    </script>
</body>
</html>
