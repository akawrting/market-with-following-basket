<?php
// 세션 시작
session_start();

// 데이터베이스 연결
require_once 'db_connect.php';

// 테이블이 없으면 생성 (아주 심플하게)
$conn->query("
    CREATE TABLE IF NOT EXISTS runningtbl (
        running TINYINT DEFAULT 0
    )
");

// 데이터가 있는지 확인
$check_data = $conn->query("SELECT * FROM runningtbl");
if ($check_data->num_rows == 0) {
    // 데이터가 없으면 초기값 삽입
    $conn->query("INSERT INTO runningtbl (running) VALUES (0)");
}

// running 값을 1로 업데이트 (WHERE 조건 없이)
$stmt = $conn->prepare("UPDATE runningtbl SET running = 1");
if (!$stmt->execute()) {
    // 업데이트 실패 시 에러 메시지
    echo "오류가 발생했습니다: " . $stmt->error;
    exit;
}
$stmt->close();
$conn->close();

// 장바구니 페이지로 리다이렉트
header("Location: shoppingbag.php");
exit;
?>
