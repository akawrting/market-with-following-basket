<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["phone"]) && isset($_POST["code"])) {
    $phone = $_POST["phone"];
    $code = $_POST["code"];

    if (isset($_SESSION['verify_codes'][$phone]) && $_SESSION['verify_codes'][$phone] == $code) {
        unset($_SESSION['verify_codes'][$phone]);

        // ✅ 인증 완료된 전화번호를 세션에 저장
        $_SESSION['verified_phone'] = $phone;

        echo "success";
    } else {
        echo "fail";
    }
} else {
    echo "잘못된 요청입니다.";
}
