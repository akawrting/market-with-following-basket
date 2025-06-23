<?php
session_start();

// CoolSMS PHP SDK message.php include
require_once __DIR__ . '/coolsms-php-master/lib/message.php';

// API key & secret
$api_key = 'ENTER_YOUR_API_KEY';
$api_secret = 'ENTER_YOUR_API_SECRET';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["phone"])) {
    $phone = $_POST["phone"];

    // 인증번호 생성
    $code = rand(100000, 999999);

    // 세션에 저장
    $_SESSION['verify_codes'][$phone] = $code;

    // 메시지 데이터 구성
    $messages = array(
        array(
            "to" => $phone,
            "from" => "01089524095", // 반드시 사전 등록된 발신번호
            "text" => "[본인확인] 인증번호는 {$code} 입니다."
        )
    );

    // 메시지 전송
    $result = send_messages($messages, $api_key, $api_secret);

    // 결과 확인
    if (isset($result->groupId)) {
        echo "입력하신 번호로 인증번호가 발송되었습니다.";
    } else {
        error_log("문자 전송 오류: " . json_encode($result));
        echo "인증번호 전송 중 오류가 발생했습니다.";
    }
} else {
    echo "잘못된 요청입니다.";
}
?>
