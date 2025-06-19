<?php
// 세션 시작
session_start();

// 관리자 로그인 상태 확인
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "접근 권한이 없습니다.";
    exit;
}

// 데이터베이스 연결
$host = "127.0.0.1";
$db = "famarket";
$user = "famarket";
$pass = "qpalzm1029!";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("데이터베이스 연결 실패: " . $e->getMessage());
}

// 구매 ID 확인
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "잘못된 요청입니다.";
    exit;
}

$purchase_id = $_GET['id'];

// 구매 정보 가져오기
$stmt = $conn->prepare("
    SELECT ph.*, u.username, u.phonenum 
    FROM purchase_history ph
    JOIN usertbl u ON ph.userid = u.userid
    WHERE ph.purchase_id = ?
");
$stmt->execute([$purchase_id]);
$purchase = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$purchase) {
    echo "해당 구매 내역을 찾을 수 없습니다.";
    exit;
}

// 구매 상세 내역 가져오기
$stmt = $conn->prepare("
    SELECT * FROM purchase_items 
    WHERE purchase_id = ?
    ORDER BY itemname
");
$stmt->execute([$purchase_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="purchase-info">
    <p><strong>구매 ID:</strong> <?php echo $purchase['purchase_id']; ?></p>
    <p><strong>회원 이름:</strong> <?php echo htmlspecialchars($purchase['username']); ?></p>
    <p><strong>전화번호:</strong> <?php echo htmlspecialchars($purchase['phonenum']); ?></p>
    <p><strong>구매 일시:</strong> <?php echo date('Y-m-d H:i:s', strtotime($purchase['purchase_date'])); ?></p>
    <p><strong>총 결제 금액:</strong> <?php echo number_format($purchase['total_amount']); ?>원</p>
</div>

<h3>구매 상품 목록</h3>
<table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
    <thead>
        <tr>
            <th style="padding: 10px; text-align: left; background-color: #f8f9fa; border-bottom: 1px solid #ddd;">상품명</th>
            <th style="padding: 10px; text-align: center; background-color: #f8f9fa; border-bottom: 1px solid #ddd;">수량</th>
            <th style="padding: 10px; text-align: right; background-color: #f8f9fa; border-bottom: 1px solid #ddd;">가격</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($item['itemname']); ?></td>
                    <td style="padding: 10px; text-align: center; border-bottom: 1px solid #ddd;"><?php echo $item['itemnum']; ?>개</td>
                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;"><?php echo number_format($item['totalprice']); ?>원</td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" style="padding: 20px; text-align: center;">구매 상품 정보가 없습니다.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
