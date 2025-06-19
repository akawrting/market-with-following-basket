<?php
// 세션 시작은 한 번만
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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

  // 결제 정보를 세션에 저장 (다른 페이지에서 사용할 수 있도록)
  $_SESSION['paid_amount'] = $total_price;

  // paytable에 결제 금액 저장
  $stmt = $conn->prepare("INSERT INTO paytable (payprice) VALUES (:payprice)");
  $stmt->bindParam(':payprice', $total_price);
  $stmt->execute();
  

  // running 값을 0으로 업데이트
  $stmt = $conn->prepare("UPDATE runningtbl SET running = 0");
  if (!$stmt->execute()) {
    // 업데이트 실패 시 에러 메시지
    echo "<script>alert('running 값 업데이트 중 오류가 발생했습니다.'); history.back();</script>";
    exit;
  }

  // $stmt->close() 제거 - PDO에서는 필요 없음
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
?>
