<?php
// 세션 시작은 한 번만
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = "127.0.0.1";
$db = "famarket";
$user = "famarket";
$pass = "qpalzm1029!";

// 세션에서 userid 가져오기
$userid = $_SESSION['userid'] ?? null;


if (!$userid) {
  echo "<script>alert('세션이 만료되었습니다. 다시 로그인해주세요.'); location.href='login.php';</script>";
  exit();
}

try {
  $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // 장바구니 총 결제 금액 계산
  $stmt = $conn->prepare("SELECT SUM(totalprice) as total FROM sbtable WHERE userid = :userid");
  $stmt->bindParam(':userid', $userid);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $total_price = $result['total'] ?? 0;

  // 총 금액이 0이면 결제 불가능
  if ($total_price <= 0) {
    echo "<script>alert('장바구니에 담긴 상품이 없습니다.'); history.back();</script>";
    exit();
  }

  // 결제 정보 저장
  $stmt = $conn->prepare("INSERT INTO paytable (userid, payprice, date) VALUES (:userid, :payprice, NOW())");
  $stmt->bindParam(':userid', $userid);
  $stmt->bindParam(':payprice', $total_price);
  $stmt->execute();

  // 장바구니 비우기
  $stmt = $conn->prepare("DELETE FROM sbtable WHERE userid = :userid");
  $stmt->bindParam(':userid', $userid);
  $stmt->execute();

  // 출력 버퍼 정리 (중요!)
  ob_clean();
  
  // 결제 완료 페이지로 이동
  header("Location: http://127.0.0.1:8000/payment/");
  exit();

} catch (PDOException $e) {
  echo "<script>alert('오류 발생: {$e->getMessage()}'); history.back();</script>";
  exit();
}
?>
