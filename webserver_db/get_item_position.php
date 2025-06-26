<?php
// 세션 시작
session_start();

// DB 연결 정보
$host = "127.0.0.1";
$db = "famarket";
$user = "famarket";
$pass = "qpalzm1029!";

// 아이템 ID 가져오기
$itemId = isset($_GET['itemid']) ? intval($_GET['itemid']) : 0;

if ($itemId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => '유효하지 않은 아이템 ID']);
    exit;
}

try {
    // DB 연결
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 아이템 위치 정보 조회
    // pos와 orientation 열을 그대로 가져옴
    $stmt = $conn->prepare("SELECT itemid, itemname, pos, orientation FROM itemtable WHERE itemid = :itemid");
    $stmt->bindParam(':itemid', $itemId);
    $stmt->execute();
    
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        header('Content-Type: application/json');
        echo json_encode(['error' => '아이템을 찾을 수 없습니다']);
        exit;
    }
    
    // JSON 응답
    header('Content-Type: application/json');
    echo json_encode($item);
    
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => '데이터베이스 오류: ' . $e->getMessage()]);
}
?>
