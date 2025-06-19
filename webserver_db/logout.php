<?php
// 세션 시작
session_start();

// 세션 변수 초기화
$_SESSION = array();

// 세션 쿠키 삭제
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// 세션 파괴
session_destroy();

// 로그인 페이지로 리다이렉트
header("Location: login.php");
exit;
?>
