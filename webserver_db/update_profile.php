<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = $_SESSION['userid'];
    $gender = $_POST['gender'] ?? null;
    $yyyymmdd = $_POST['yyyymmdd'] ?? null;

    // 생년월일 유효성 검사 (예: 8자리 숫자인지)
    if (!preg_match('/^\d{8}$/', $yyyymmdd)) {
        // 유효하지 않은 형식일 경우 처리
        echo "<script>alert('생년월일 형식이 올바르지 않습니다. YYYYMMDD 형식으로 입력해주세요.'); history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("UPDATE usertbl SET gender = ?, yyyymmdd = ? WHERE userid = ?");
    $stmt->bind_param("sss", $gender, $yyyymmdd, $userid);

    if ($stmt->execute()) {
        echo "<script>alert('회원정보가 성공적으로 수정되었습니다.'); window.location.href='main.php';</script>";
    } else {
        echo "<script>alert('회원정보 수정에 실패했습니다: " . $stmt->error . "'); history.back();</script>";
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: main.php"); // POST 요청이 아니면 마이페이지로 리다이렉트
    exit;
}
?>
