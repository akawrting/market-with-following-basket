<?php
$servername = "127.0.0.1"; // 보통 웹호스팅에서는 localhost
$username = "famarket";
$password = "qpalzm1029!";
$dbname = "famarket"; // 데이터베이스 이름, 보통 계정명과 같을 수 있음

// 데이터베이스 연결 생성
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
    die("데이터베이스 연결 실패: " . $conn->connect_error);
}

// 한글 깨짐 방지
$conn->set_charset("utf8");
?>
