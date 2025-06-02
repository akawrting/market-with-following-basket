<?php
// start_process.php

// DB 연결 정보
$host = "127.0.0.1";
$db = "famarket";
$user = "famarket";
$pass = "qpalzm1029!";

// 사용자 입력
$input_phonenum = $_POST['phonenum'] ?? '';

if (empty($input_phonenum)) {
    echo "<script>alert('전화번호를 입력해주세요.'); window.history.back();</script>";
    exit;
}

try {
    // DB 연결
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 현재 시간 기준 5분 이내의 입장 기록 조회
    $stmt = $conn->prepare("SELECT phonenum FROM entertbl WHERE enter_time >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY enter_time DESC");
    $stmt->execute();
    $recent_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $found_match = false;
    foreach ($recent_entries as $entry) {
        if ($entry['phonenum'] === $input_phonenum) {
            $found_match = true;
            break;
        }
    }

    if ($found_match) {
        // 전화번호로 usertbl에서 userid 조회
        $stmt = $conn->prepare("SELECT userid FROM usertbl WHERE phonenum = :phonenum");
        $stmt->bindParam(':phonenum', $input_phonenum);
        $stmt->execute();
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            $userid = $user_data['userid'];
            
            // 세션 시작 및 전화번호 저장
            session_start();
            $_SESSION['phonenum'] = $input_phonenum;
            
            // shoppingbag.php로 이동
            header("Location: shoppingbag.php");
            exit;
        } else {
            echo "<script>alert('해당 전화번호의 사용자 정보를 찾을 수 없습니다.'); window.location.href = 'welcome.php';</script>";
            exit;
        }
    } else {
        // 전화번호 불일치
        echo "<script>alert('최근 5분 이내 입장 기록과 전화번호가 일치하지 않습니다.'); window.location.href = 'welcome.php';</script>";
        exit;
    }

} catch (PDOException $e) {
    echo "<script>alert('DB 오류: " . $e->getMessage() . "'); window.location.href = 'welcome.php';</script>";
    exit;
}
?>
