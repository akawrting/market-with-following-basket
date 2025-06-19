<?php

// URL 파라미터에서 결제 금액 가져오기
$payment_amount = isset($_GET['amount']) ? $_GET['amount'] : 0;
$point_amount = round($payment_amount * 0.05); // 결제 금액의 5%

$host = "127.0.0.1";
$db = "famarket";
$user = "famarket";
$pass = "qpalzm1029!";


// 전화번호 형식 변환 함수
function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone); // 숫자만 남기기
    
    if (strlen($phone) === 11) {
        return substr($phone, 0, 3) . '-' . substr($phone, 3, 4) . '-' . substr($phone, 7);
    } elseif (strlen($phone) === 10) {
        return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6);
    }
    
    return $phone;
}

// 포인트 업데이트 함수
function updatePoints($phone, $amount) {
    global $host, $db, $user, $pass;

    // 전화번호에서 하이픈 제거 (DB와 형식 일치)
    $phone_clean = preg_replace('/[^0-9]/', '', $phone);

    try {
        $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 사용자 조회
        $stmt = $conn->prepare("SELECT * FROM usertbl WHERE phonenum = :phone");
        $stmt->bindParam(':phone', $phone_clean);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_points = $user_data['points'] ?? 0;
            $new_points = $current_points + $amount;

            // 포인트 업데이트
            $update = $conn->prepare("UPDATE usertbl SET points = :points WHERE phonenum = :phone_clean");
            $update->bindParam(':points', $new_points);
            $update->bindParam(':phone_clean', $phone_clean);  // ✅ 이 줄을 고쳤어요
            $update->execute();

            return [
                'success' => true,
                'message' => '포인트가 성공적으로 적립되었습니다.',
                'current_points' => $current_points,
                'added_points' => $amount,
                'new_points' => $new_points
            ];
        } else {
            return [
                'success' => false,
                'message' => '등록된 전화번호가 없습니다. 회원가입을 해주세요.'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => '오류가 발생했습니다: ' . $e->getMessage()
        ];
    }
}

//구매 내역 저장 함수
function saveOrderHistory($userid, $payment_amount, $conn) {
    try {
        // 트랜잭션 시작
        $conn->beginTransaction();
        
        // 구매 내역 저장
        $stmt = $conn->prepare("INSERT INTO purchase_history (userid, total_amount) VALUES (:userid, :amount)");
        $stmt->bindParam(':userid', $userid);
        $stmt->bindParam(':amount', $payment_amount);
        $stmt->execute();
        
        $purchase_id = $conn->lastInsertId(); // 방금 생성된 구매 ID
        
        // sbtable에서 상품 정보 가져오기
        $items_stmt = $conn->query("SELECT * FROM sbtable");
        
        // 각 상품을 purchase_items에 저장
        while ($item = $items_stmt->fetch(PDO::FETCH_ASSOC)) {
            $detail_stmt = $conn->prepare("INSERT INTO purchase_items (purchase_id, itemname, itemnum, totalprice) VALUES (:purchase_id, :itemname, :itemnum, :totalprice)");
            $detail_stmt->bindParam(':purchase_id', $purchase_id);
            $detail_stmt->bindParam(':itemname', $item['itemname']);
            $detail_stmt->bindParam(':itemnum', $item['itemnum']);
            $detail_stmt->bindParam(':totalprice', $item['totalprice']);
            $detail_stmt->execute();
        }
        
        // 트랜잭션 커밋
        $conn->commit();
        
        return [
            'success' => true,
            'message' => '구매 내역이 성공적으로 저장되었습니다.',
            'purchase_id' => $purchase_id
        ];
        
    } catch (PDOException $e) {
        // 오류 발생 시 롤백
        $conn->rollBack();
        
        return [
            'success' => false,
            'message' => '구매 내역 저장 중 오류가 발생했습니다: ' . $e->getMessage()
        ];
    }
}


$message = '';
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone'])) {
    $phone = formatPhoneNumber($_POST['phone']);
    $result = updatePoints($phone, $point_amount);
    
    if ($result['success']) {
        $message = $result['message'];
    } 
    else {
        $message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>포인트 적립</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
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
            width: 100%;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .result-box {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9f7ef;
            border-radius: 5px;
        }
        .home-button {
            display: block;
            text-align: center;
            margin-top: 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>포인트 적립</h1>
        <p>결제 금액: <strong><?php echo number_format($payment_amount); ?>원</strong></p>
        <p>적립 예정 포인트: <strong><?php echo number_format($point_amount); ?>P</strong> (결제 금액의 5%)</p>
        
        <?php if (!$result): ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="phone">전화번호를 입력해주세요:</label>
                <input type="text" id="phone" name="phone" placeholder="010-1234-5678" required>
            </div>
            <button type="submit">포인트 적립하기</button>
        </form>
        <?php else: ?>
            <div class="message <?php echo $result['success'] ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
            
            <?php if ($result['success']): ?>
            <div class="result-box">
                <p>기존 포인트: <?php echo number_format($result['current_points']); ?>P</p>
                <p>적립 포인트: <?php echo number_format($result['added_points']); ?>P</p>
                <p>최종 포인트: <?php echo number_format($result['new_points']); ?>P</p>
                
                <?php
                // 구매 내역 저장 코드 추가
                try {
                    // 기존 PDO 연결 재사용 (getpoint.php 상단에 있는 연결)
                    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // 사용자 ID 가져오기
                    $phone_clean = preg_replace('/[^0-9]/', '', $_POST['phone']);
                    $user_stmt = $conn->prepare("SELECT userid FROM usertbl WHERE phonenum = :phone");
                    $user_stmt->bindParam(':phone', $phone_clean);
                    $user_stmt->execute();
                    
                    if ($user_stmt->rowCount() > 0) {
                        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
                        $userid = $user['userid'];
                        
                        // 구매 내역 저장
                        $stmt = $conn->prepare("INSERT INTO purchase_history (userid, total_amount) VALUES (:userid, :amount)");
                        $stmt->bindParam(':userid', $userid);
                        $stmt->bindParam(':amount', $payment_amount);
                        $stmt->execute();
                        
                        $purchase_id = $conn->lastInsertId(); // 방금 생성된 구매 ID
                        
                        // sbtable에서 상품 정보 가져오기
                        $items_stmt = $conn->query("SELECT * FROM sbtable");
                        
                        // 각 상품을 purchase_items에 저장
                        while ($item = $items_stmt->fetch(PDO::FETCH_ASSOC)) {
                            $detail_stmt = $conn->prepare("INSERT INTO purchase_items (purchase_id, itemname, itemnum, totalprice) VALUES (:purchase_id, :itemname, :itemnum, :totalprice)");
                            $detail_stmt->bindParam(':purchase_id', $purchase_id);
                            $detail_stmt->bindParam(':itemname', $item['itemname']);
                            $detail_stmt->bindParam(':itemnum', $item['itemnum']);
                            $detail_stmt->bindParam(':totalprice', $item['totalprice']);
                            $detail_stmt->execute();
                        }
                        
                        echo "<p class='success-message'>구매 내역이 저장되었습니다!</p>";
                    }
                } catch (PDOException $e) {
                    echo "<p class='error-message'>오류 발생: " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
            <?php endif; ?>
            
            <a href="http://127.0.0.1:8080/welcome.php" class="home-button">홈으로 돌아가기</a>
        <?php endif; ?>
    </div>
</body>

</html>
