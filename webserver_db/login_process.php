<?php
// 세션 시작
session_start();

// 데이터베이스 연결 파일 포함
require_once 'db_connect.php';

// POST 데이터 확인
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 폼에서 전송된 데이터 가져오기
    $userid = $_POST['userid'];
    $userpw = $_POST['userpw'];
    
    // SQL 인젝션 방지를 위한 데이터 정리
    $userid = $conn->real_escape_string($userid);
    
    // 사용자 정보 조회
    $sql = "SELECT * FROM usertbl WHERE userid = '$userid'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 비밀번호 확인
        if (password_verify($userpw, $row['password'])) {
            // 로그인 성공
            $_SESSION['userid'] = $row['userid'];
            $_SESSION['username'] = $row['username'];
            
            echo "<script>
                    alert('로그인 성공!');
                    location.href = 'main.php'; // 로그인 후 이동할 페이지
                  </script>";
        } else {
            // 비밀번호 불일치
            echo "<script>
                    alert('아이디 또는 비밀번호가 일치하지 않습니다.');
                    history.back();
                  </script>";
        }
    } else {
        // 아이디 없음
        echo "<script>
                alert('아이디 또는 비밀번호가 일치하지 않습니다.');
                history.back();
              </script>";
    }
    
    // 데이터베이스 연결 종료
    $conn->close();
}
?>
