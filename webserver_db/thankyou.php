<?php
session_start();

// DB 연결 정보
$host = "127.0.0.1";
$db = "famarket";
$user = "famarket";
$pass = "qpalzm1029!";

// 기본값
$username = "고객";

$input_phonenum = $_SESSION['phonenum'] ?? '';

if (!empty($input_phonenum)) {
    try {
        // DB 연결
        $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 사용자 이름 조회
        $stmt = $conn->prepare("SELECT username FROM usertbl WHERE phonenum = :phonenum");
        $stmt->bindParam(':phonenum', $input_phonenum);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && isset($user['username'])) {
            $username = $user['username'];
        }
    } catch (PDOException $e) {
        // 오류 무시하고 기본값 유지
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>결제 완료</title>
  <style>
    body {
      font-family: 'Noto Sans KR', sans-serif;
      background-color: #f3f4f6;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .thankyou-box {
      background-color: white;
      padding: 40px 60px;
      border-radius: 20px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      text-align: center;
    }
    .thankyou-box h1 {
      color: #4caf50;
      font-size: 32px;
      margin-bottom: 20px;
    }
    .thankyou-box p {
      font-size: 18px;
      color: #333;
      margin-top: 10px;
    }
    .home-button {
      margin-top: 30px;
      padding: 12px 25px;
      font-size: 16px;
      background-color: #4caf50;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="thankyou-box">
    <h1>🎉 결제가 완료되었습니다!</h1>
    <p><strong><?php echo htmlspecialchars($username); ?>님</strong>, 이용해주셔서 감사합니다.</p>
    <p>즐거운 쇼핑 되세요!</p>
    <a href="index.php" class="home-button">홈으로 돌아가기</a>
  </div>
</body>
</html>
