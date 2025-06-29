<?php
// 세션 시작은 한 번만
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 포인트 사용 폼이 제출되었는지 확인
if (isset($_POST['use_points']) && isset($_POST['phonenum']) && isset($_POST['points_to_use'])) {
    // 입력받은 전화번호에서 하이픈 제거
    $phonenum_input = str_replace('-', '', $_POST['phonenum']);
    $points_to_use = (int)$_POST['points_to_use'];
    
    $host = "127.0.0.1";
    $db = "famarket";
    $user = "famarket";
    $pass = "qpalzm1029!";
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 전화번호로 사용자 포인트 확인 (하이픈 제거된 전화번호 사용)
        $stmt = $conn->prepare("SELECT points FROM usertbl WHERE phonenum = :phonenum");
        $stmt->bindParam(':phonenum', $phonenum_input); // 수정된 부분
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $available_points = $result['points'];
            
            // 사용 가능한 포인트보다 많이 사용하려고 하는지 확인
            if ($points_to_use > $available_points) {
                echo "<script>alert('사용 가능한 포인트보다 많은 포인트를 사용할 수 없습니다.'); history.back();</script>";
                exit();
            }
            
            // 장바구니 총 결제 금액 계산
            $stmt = $conn->prepare("SELECT SUM(totalprice) as total FROM sbtable");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_price = $result['total'] ?? 0;
            
            // 총 금액이 0이면 결제 불가능
            if ($total_price <= 0) {
                echo "<script>alert('장바구니에 담긴 상품이 없습니다.'); history.back();</script>";
                exit();
            }

            // 포인트 사용 후 남은 금액 계산
            $final_price = $total_price - $points_to_use;
            
            if ($final_price < 0) {
                echo "<script>alert('결제 금액보다 많은 포인트를 사용할 수 없습니다.'); history.back();</script>";
                exit();
            }
            
            // 사용자 포인트 차감 (하이픈 제거된 전화번호 사용)
            $new_points = $available_points - $points_to_use;
            $stmt = $conn->prepare("UPDATE usertbl SET points = :new_points WHERE phonenum = :phonenum");
            $stmt->bindParam(':new_points', $new_points);
            $stmt->bindParam(':phonenum', $phonenum_input); // 수정된 부분
            $stmt->execute();
            
            // 세션에 사용한 포인트와 최종 결제 금액 저장
            $_SESSION['used_points'] = $points_to_use;
            $_SESSION['paid_amount'] = $final_price;
            
            // paytable에 최종 결제 금액 저장
            // paytable에 used_points 컬럼이 있다면 추가
            // $stmt = $conn->prepare("INSERT INTO paytable (payprice, used_points) VALUES (:payprice, :used_points)");
            // $stmt->bindParam(':payprice', $final_price);
            // $stmt->bindParam(':used_points', $points_to_use);
            // $stmt->execute();
            // paytable에 used_points 컬럼이 없다면 아래처럼
            $stmt = $conn->prepare("INSERT INTO paytable (payprice) VALUES (:payprice)");
            $stmt->bindParam(':payprice', $final_price);
            $stmt->execute();
            
            // running 값을 0으로 업데이트
            $stmt = $conn->prepare("UPDATE runningtbl SET running = 0");
            if (!$stmt->execute()) {
                echo "<script>alert('running 값 업데이트 중 오류가 발생했습니다.'); history.back();</script>";
                exit;
            }
            
            $conn = null; // 데이터베이스 연결 종료
            
            // 출력 버퍼 정리
            ob_clean();
            
            // 결제 완료 페이지로 이동
            header("Location: http://127.0.0.1:8000/payment/");
            exit();
        } else {
            echo "<script>alert('해당 전화번호로 등록된 사용자를 찾을 수 없습니다.'); history.back();</script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "<script>alert('오류 발생: {$e->getMessage()}'); history.back();</script>";
        exit();
    }
}

// 포인트 사용 안 함 버튼을 클릭한 경우
if (isset($_POST['skip_points'])) {
    $host = "127.0.0.1";
    $db = "famarket";
    $user = "famarket";
    $pass = "qpalzm1029!";
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 장바구니 총 결제 금액 계산
        $stmt = $conn->prepare("SELECT SUM(totalprice) as total FROM sbtable");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_price = $result['total'] ?? 0;
        
        // 총 금액이 0이면 결제 불가능
        if ($total_price <= 0) {
            echo "<script>alert('장바구니에 담긴 상품이 없습니다.'); history.back();</script>";
            exit();
        }

        // 결제 정보를 세션에 저장
        $_SESSION['paid_amount'] = $total_price;
        $_SESSION['used_points'] = 0; // 포인트 사용 안 했으니 0으로 설정
        
        // paytable에 결제 금액 저장
        $stmt = $conn->prepare("INSERT INTO paytable (payprice) VALUES (:payprice)");
        $stmt->bindParam(':payprice', $total_price);
        $stmt->execute();
        
        // running 값을 0으로 업데이트
        $stmt = $conn->prepare("UPDATE runningtbl SET running = 0");
        if (!$stmt->execute()) {
            echo "<script>alert('running 값 업데이트 중 오류가 발생했습니다.'); history.back();</script>";
            exit;
        }
        
        $conn = null; // 데이터베이스 연결 종료
        
        // 출력 버퍼 정리
        ob_clean();
        
        // 결제 완료 페이지로 이동
        header("Location: http://127.0.0.1:8000/payment/");
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('오류 발생: {$e->getMessage()}'); history.back();</script>";
        exit();
    }
}

// 포인트 사용 폼이 제출되지 않았을 경우, 폼 표시
$host = "127.0.0.1";
$db = "famarket";
$user = "famarket";
$pass = "qpalzm1029!";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 장바구니 총 결제 금액 계산 - 모든 totalprice 합산
    $stmt = $conn->prepare("SELECT SUM(totalprice) as total FROM sbtable");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_price = $result['total'] ?? 0;
    
    // 총 금액이 0이면 결제 불가능
    if ($total_price <= 0) {
        echo "<script>alert('장바구니에 담긴 상품이 없습니다.'); history.back();</script>";
        exit();
    }
    
    // 포인트 사용 폼 표시
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>포인트 사용</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background-color: #f8f9fa;
            }
            .container {
                max-width: 500px;
                margin: 0 auto;
                background-color: white;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            h2 {
                text-align: center;
                color: #333;
            }
            .form-group {
                margin-bottom: 15px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            input[type="text"],
            input[type="number"] {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                box-sizing: border-box;
            }
            .price-info {
                background-color: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
            }
            .btn {
                display: inline-block;
                background-color: #007bff;
                color: white;
                padding: 10px 15px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-align: center;
                text-decoration: none;
                margin-right: 10px;
            }
            .btn-primary {
                background-color: #007bff;
            }
            .btn-secondary {
                background-color: #6c757d;
            }
            .btn-container {
                text-align: center;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>포인트 사용</h2>
            
            <div class="price-info">
                <p><strong>총 결제 금액:</strong> <?php echo number_format($total_price); ?>원</p>
            </div>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="phonenum">전화번호:</label>
                    <input type="text" id="phonenum" name="phonenum" placeholder="010-0000-0000" required>
                </div>
                
                <div class="form-group">
                    <label for="points_to_use">사용할 포인트:</label>
                    <input type="number" id="points_to_use" name="points_to_use" min="0" max="<?php echo $total_price; ?>" value="0" required>
                    <small>* 최대 <?php echo number_format($total_price); ?>원까지 사용 가능합니다.</small>
                </div>
                
                <div class="btn-container">
                    <button type="submit" name="use_points" class="btn btn-primary">포인트 적용</button>
                    <a href="javascript:history.back()" class="btn btn-secondary">뒤로 가기</a>
                    <button type="submit" name="skip_points" class="btn btn-secondary">포인트 사용 안함</button>
                </div>
            </form>
        </div>
        
        <script>
            // 전화번호 입력 시 자동으로 하이픈(-) 추가
            document.getElementById('phonenum').addEventListener('input', function(e) {
                var x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,4})(\d{0,4})/);
                e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '');
            });
            
            // 포인트 입력 값 검증
            document.getElementById('points_to_use').addEventListener('change', function(e) {
                var max = <?php echo $total_price; ?>;
                if (parseInt(e.target.value) > max) {
                    alert('최대 ' + max + '원까지 사용 가능합니다.');
                    e.target.value = max;
                }
                if (parseInt(e.target.value) < 0) {
                    e.target.value = 0;
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit();
    
} catch (PDOException $e) {
    echo "<script>alert('오류 발생: {$e->getMessage()}'); history.back();</script>";
    exit();
}
?>
