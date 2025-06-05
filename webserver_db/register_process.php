<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userid    = trim($_POST['username']);   // 사용자 ID
    $username  = trim($_POST['name']);       // 실제 이름
    // 이메일 필드 처리 (빈 값 허용)
    $useremail = isset($_POST['email']) && !empty($_POST['email']) ? trim($_POST['email']) : null;
    $userpw    = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // 비밀번호 해싱
    session_start();
    $phonenum = $_SESSION['verified_phone'] ?? '';

    // 아이디 중복 확인
    $check_stmt = $conn->prepare("SELECT userid FROM usertbl WHERE userid = ?");
    $check_stmt->bind_param("s", $userid);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>
                alert('이미 사용 중인 아이디입니다.');
                history.back();
              </script>";
    } else {
        // 회원 정보 삽입 부분 수정
        $insert_stmt = $conn->prepare("INSERT INTO usertbl (userid, username, email, password, phonenum) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssss", $userid, $username, $useremail, $userpw, $phonenum);

        // 쿼리 실행 전에 변수 값 확인
        echo "phonenum 값: " . $phonenum . "<br>";
        
        if ($insert_stmt->execute()) {
            // datatbl에도 userid, username 추가
            $insert_data_stmt = $conn->prepare("INSERT INTO datatbl (userid, username) VALUES (?, ?)");
            $insert_data_stmt->bind_param("ss", $userid, $username);
            $insert_data_stmt->execute();
            $insert_data_stmt->close();

            // 세션에 사용자 정보 저장
            session_start();
            $_SESSION['userid'] = $userid;
            $_SESSION['username'] = $username;
            
            // 회원가입 성공 메시지 표시 후 info.php로 리다이렉트
            echo "<script>
                    alert('회원가입이 완료되었습니다!');
                    location.href = 'info.php';
                  </script>";
        } else {
            echo "<script>
            alert('오류가 발생했습니다: " . $insert_stmt->error . "');
            history.back();
            </script>";
            exit;
        }
        $insert_stmt->close();
    }

    $check_stmt->close();
    $conn->close();
}
?>
