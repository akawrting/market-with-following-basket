<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userid    = trim($_POST['username']);   // 사용자 ID
    $username  = trim($_POST['name']);       // 실제 이름
    $useremail = trim($_POST['email']);      // 이메일
    $userpw    = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // 비밀번호 해싱
    $phonenum  = trim($_POST['phone']);      // 전화번호

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
        // 회원 정보 삽입
        $insert_stmt = $conn->prepare("INSERT INTO usertbl (userid, username, email, password, phonenum) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssss", $userid, $username, $useremail, $userpw, $phonenum);

        if ($insert_stmt->execute()) {
            // datatbl에도 userid, username 추가
            $insert_data_stmt = $conn->prepare("INSERT INTO datatbl (userid, username) VALUES (?, ?)");
            $insert_data_stmt->bind_param("ss", $userid, $username);
            $insert_data_stmt->execute();
            $insert_data_stmt->close();

            // 얼굴 등록용 페이지로 이동 + PyQt에게 user_id 전달
            $user_id_for_face = $userid; 

            echo "
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <script type='text/javascript' src='qrc:///qtwebchannel/qwebchannel.js'></script>
                </head>
                <body>
                    <h3>얼굴 등록 중입니다. 잠시만 기다려주세요...</h3>
                    <script>
                        new QWebChannel(qt.webChannelTransport, function(channel) {
                            var bridge = channel.objects.bridge;
                            var user_id = " . json_encode($user_id_for_face) . ";
                            console.log('전달할 user_id:', user_id);
                            bridge.onFormSubmitted(user_id);  // PyQt 슬롯 호출

                            setTimeout(function() {
                                window.location.href = 'face_register.php?user_id=' + user_id;
                            }, 1000);
                        });
                    </script>
                </body>
                </html>
            ";
        } else {
            echo "<script>
                    alert('오류가 발생했습니다: " . $conn->error . "');
                    history.back();
                  </script>";
        }
        $insert_stmt->close();
    }

    $check_stmt->close();
    $conn->close();
}
?>
