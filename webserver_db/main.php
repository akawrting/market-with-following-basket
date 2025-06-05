<?php
// 세션 시작
session_start();

// 로그인 상태 확인
if (!isset($_SESSION['userid'])) {
    // 로그인되지 않은 경우 로그인 페이지로 리다이렉트
    header("Location: login.php");
    exit;
}

// 데이터베이스 연결
require_once 'db_connect.php';

// 사용자 정보 가져오기
$userid = $_SESSION['userid'];
$stmt = $conn->prepare("SELECT username, points FROM usertbl WHERE userid = ?");
$stmt->bind_param("s", $userid);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $username = $row['username'];
    $points = $row['points'] ?? 0; // points 컬럼이 없으면 0으로 설정
} else {
    // 사용자 정보를 찾을 수 없는 경우
    $username = "알 수 없음";
    $points = 0;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>메인 페이지</title>
    <style>
        body {
            font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
            padding: 40px;
            text-align: center;
        }
        
        h1 {
            color: #333;
            margin-bottom: 30px;
        }
        
        .point-info {
            font-size: 24px;
            margin: 30px 0;
            padding: 20px;
            background-color: #f7f9fc;
            border-radius: 12px;
        }
        
        .point-value {
            color: #4a90e2;
            font-weight: bold;
            font-size: 32px;
        }
        
        .menu {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .menu-btn {
            background: linear-gradient(45deg, #4a90e2, #5ca9fb);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 15px 25px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
            min-width: 150px;
        }
        
        .menu-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(74, 144, 226, 0.4);
        }
        
        .logout-btn {
            background: #f0f0f0;
            color: #666;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>안녕하세요, <?php echo $username; ?>님!</h1>
        
        <div class="point-info">
            적립된 포인트: <span class="point-value"><?php echo number_format($points); ?>P</span>
        </div>
        
        <div class="menu">
            <a href="info.php"><button class="menu-btn">내 정보</button></a>
            <a href="logout.php"><button class="menu-btn logout-btn">로그아웃</button></a>
        </div>
    </div>
</body>
</html>
