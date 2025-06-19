<?php
// 세션 시작
session_start();

// 데이터베이스 연결
require_once 'db_connect.php';

try {
    // sbtable 비우기
    $stmt = $conn->prepare("DELETE FROM sbtable");
    $stmt->execute();
    
    echo "장바구니가 성공적으로 비워졌습니다.";
} catch (Exception $e) {
    http_response_code(500);
    echo "오류가 발생했습니다: " . $e->getMessage();
}
?>
