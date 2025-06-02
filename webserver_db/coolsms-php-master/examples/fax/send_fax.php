<?php
/*
 * 한번의 요청으로 1만건까지 FAX 발송이 가능합니다.
 */
require_once("../../lib/message.php");

// 발송할 PDF 혹은 HTML 파일을 먼저 업로드합니다.
$imageId = create_image_type("./sample.html", "FAX");

$messages = array(
    // 1만건까지 추가 가능
    array(
        "to" => "0212345678", // 수신자 팩스번호
        "from" => "029302266", // 발신자 팩스번호
        "subject" => "팩스제목",
        "faxOptions" => array(
          "fileIds" => array($imageId)
        )
    )
);

// 발송 요청 및 결과 출력
print_r(send_messages($messages));
