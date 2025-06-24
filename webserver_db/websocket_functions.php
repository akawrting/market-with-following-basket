<?php
// websocket_functions.php - 웹소켓 통신을 위한 함수 모음

// 웹소켓으로 명령어 보내는 함수
function sendWebSocketCommand($command, $params = [], $host = 'localhost', $port = '8989') {
    // 웹소켓 서버 주소
    $address = "ws://$host:$port";
    
    // 명령어와 파라미터를 JSON으로 변환
    $data = json_encode([
        'command' => $command,
        'params' => $params,
        'timestamp' => time()
    ]);
    
    // cURL을 사용하여 웹소켓 서버에 데이터 전송
    $ch = curl_init();
    
    // 웹소켓 헤더 설정
    $headers = [
        'Connection: Upgrade',
        'Upgrade: websocket',
        'Sec-WebSocket-Version: 13',
        'Sec-WebSocket-Key: ' . base64_encode(openssl_random_pseudo_bytes(16))
    ];
    
    curl_setopt($ch, CURLOPT_URL, $address);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    return ['success' => true, 'response' => $response];
}

// 예제: 간단한 명령어 전송
function sendAlert($message) {
    return sendWebSocketCommand('alert', ['message' => $message]);
}

// 예제: 데이터 업데이트 명령어
function updateData($dataId, $newValue) {
    return sendWebSocketCommand('update', ['id' => $dataId, 'value' => $newValue]);
}

// 예제: 사용자 리다이렉트 명령어
function redirectUser($url) {
    return sendWebSocketCommand('redirect', ['url' => $url]);
}
?>
