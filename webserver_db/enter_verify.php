<?php
session_start();
date_default_timezone_set("Asia/Seoul");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["phone"]) && isset($_POST["code"])) {
    $phone = $_POST["phone"];
    $code = $_POST["code"];

    // 인증번호 확인
    if (isset($_SESSION['verify_codes'][$phone]) && $_SESSION['verify_codes'][$phone] == $code) {
        // 1. 인증번호는 한번만 사용되도록 삭제
        unset($_SESSION['verify_codes'][$phone]);

        // 2. DB에 전화번호와 입장시간 저장
        $servername = "localhost";
        $username = "famarket";      // ← 본인 DB 계정으로 변경
        $password = "qpalzm1029!";      // ← 본인 DB 비밀번호로 변경
        $dbname = "famarket";   // ← DB 이름으로 변경

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 트랜잭션 시작
            $conn->beginTransaction();

            // 입장 기록 저장
            $stmt = $conn->prepare("INSERT INTO entertbl (phonenum, enter_time) VALUES (:phone, NOW())");
            $stmt->bindParam(":phone", $phone);
            $stmt->execute();

            // gatetbl의 status를 현재 값의 NOT 연산으로 업데이트 (토글)
            // MySQL에서 NOT은 ! 또는 NOT 키워드를 사용
            $stmt = $conn->prepare("UPDATE gatetbl SET status = NOT status");
            $stmt->execute();

            // 트랜잭션 커밋
            $conn->commit();

            echo "success";
        } catch (PDOException $e) {
            // 오류 발생 시 트랜잭션 롤백
            $conn->rollBack();
            http_response_code(500);
            echo "DB 오류: " . $e->getMessage();
        }

        $conn = null;
    } else {
        echo "fail"; // 인증번호 불일치
    }
} else {
    echo "invalid"; // 필드 누락
}
?>