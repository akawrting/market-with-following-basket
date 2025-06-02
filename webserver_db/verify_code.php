<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["phone"]) && isset($_POST["code"])) {
    $phone = $_POST["phone"];
    $code = $_POST["code"];

    if (isset($_SESSION['verify_codes'][$phone]) && $_SESSION['verify_codes'][$phone] == $code) {
        // 인증 성공
        unset($_SESSION['verify_codes'][$phone]);  // 한 번 사용된 인증번호는 삭제
        echo "success";
    } else {
        echo "fail";
    }
} else {
    echo "잘못된 요청입니다.";
}
?>
