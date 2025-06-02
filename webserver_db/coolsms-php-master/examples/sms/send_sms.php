<?php
require_once(__DIR__ . "/../../lib/message.php");

$messages = array(
    array(
        "to" => "01089524095",
        "from" => "01089524095",
        "text" => "테스트 메시지입니다."
    )
);

$result = send_messages($messages);
print_r($result);
