<?php
// 세션 시작
session_start();

// 데이터베이스 연결
require_once 'db_connect.php';

// status 값을 1로 업데이트
$stmt = $conn->prepare("UPDATE doorstatus SET status = 1");
if (!$stmt->execute()) {
    // 업데이트 실패 시 에러 메시지 (실제 운영 시 제거 가능)
    echo "오류가 발생했습니다: " . $stmt->error;
    exit;
}
$stmt->close();
$conn->close();

// 성공 응답
echo "success";
?>
