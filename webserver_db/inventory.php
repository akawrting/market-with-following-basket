<?php
// 세션 시작
session_start();

// 관리자 로그인 상태 확인
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin.php");
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
    
    // 재고 업데이트 처리
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
        $itemname = $_POST['itemname'];
        $new_stock = $_POST['new_stock'];
        
        $update_stmt = $conn->prepare("UPDATE itemtable SET itemstock = ? WHERE itemname = ?");
        $update_stmt->execute([$new_stock, $itemname]);
        
        $message = "재고가 성공적으로 업데이트되었습니다.";
        $message_type = "success";
    }
    
    // 새 상품 추가 처리
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
        $new_itemname = $_POST['new_itemname'];
        $new_itemprice = $_POST['new_itemprice'];
        $new_itemstock = $_POST['new_itemstock'];
        
        // 중복 상품명 확인
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM itemtable WHERE itemname = ?");
        $check_stmt->execute([$new_itemname]);
        $exists = $check_stmt->fetchColumn();
        
        if ($exists) {
            $message = "이미 존재하는 상품명입니다.";
            $message_type = "error";
        } else {
            $add_stmt = $conn->prepare("INSERT INTO itemtable (itemname, itemprice, itemstock) VALUES (?, ?, ?)");
            $add_stmt->execute([$new_itemname, $new_itemprice, $new_itemstock]);
            
            $message = "새 상품이 성공적으로 추가되었습니다.";
            $message_type = "success";
        }
    }
    
    // 상품 목록 가져오기
    $items_stmt = $conn->query("SELECT * FROM itemtable ORDER BY itemname");
    $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("데이터베이스 연결 실패: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>재고 관리</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        h1, h2 {
            color: #333;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .back-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        
        .back-btn:hover {
            background-color: #5a6268;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        .update-form {
            display: flex;
            align-items: center;
        }
        
        .update-form input {
            width: 100px;
            margin-right: 10px;
        }
        
        .message {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .stock-low {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .stock-ok {
            color: #2ecc71;
        }
        
        .two-column {
            display: flex;
            gap: 30px;
        }
                .column {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="header">
            <h1>재고 관리</h1>
            <a href="admin.php" class="back-btn">관리자 페이지로 돌아가기</a>
        </div>
        
        <div class="two-column">
            <div class="column">
                <h2>현재 재고 현황</h2>
                <table>
                    <thead>
                        <tr>
                            <th>상품명</th>
                            <th>가격</th>
                            <th>재고</th>
                            <th>재고 수정</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($items) > 0): ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['itemname']); ?></td>
                                    <td><?php echo number_format($item['itemprice']); ?>원</td>
                                    <td class="<?php echo $item['itemstock'] < 10 ? 'stock-low' : 'stock-ok'; ?>">
                                        <?php echo $item['itemstock']; ?>개
                                    </td>
                                    <td>
                                        <form method="post" action="" class="update-form">
                                            <input type="hidden" name="itemname" value="<?php echo htmlspecialchars($item['itemname']); ?>">
                                            <input type="number" name="new_stock" value="<?php echo $item['itemstock']; ?>" min="0">
                                            <button type="submit" name="update_stock">수정</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">등록된 상품이 없습니다.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="column">
                <h2>새 상품 추가</h2>
                <div class="form-section">
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="new_itemname">상품명:</label>
                            <input type="text" id="new_itemname" name="new_itemname" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_itemprice">가격:</label>
                            <input type="number" id="new_itemprice" name="new_itemprice" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_itemstock">초기 재고:</label>
                            <input type="number" id="new_itemstock" name="new_itemstock" min="0" required>
                        </div>
                        
                        <button type="submit" name="add_item">상품 추가</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

