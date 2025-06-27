#include <SoftwareSerial.h>  // 소프트웨어 시리얼 라이브러리
#include <Servo.h>           // 서보 라이브러리

SoftwareSerial BTSerial(7, 8);  // 소프트웨어 시리얼 객체를 7(RX), 8(TX)번 핀으로 생성
Servo myServo;                  // 서보 객체 생성
int servoPin = 6;               // 서보모터가 연결된 핀 (6번)
const int magneticSensorPin = 13; // 마그네틱 센서가 연결된 핀 (13번)
const int buttonPin = 12;       // 버튼이 연결된 핀 (12번)

bool doorClosed = true;         // 초기 상태는 문이 닫혀있다고 가정
int buttonState = 0;            // 버튼 상태 변수
int lastButtonState = HIGH;     // 이전 버튼 상태 변수 (풀업 저항 사용 시 기본값은 HIGH)
unsigned long doorOpenTime = 0; // 문이 열린 시간을 저장
bool doorOpening = false;       // 문이 열리는 중인지 여부

void setup() {
  Serial.begin(9600);           // 시리얼 통신 시작
  BTSerial.begin(9600);         // 블루투스 시리얼 통신 시작
  myServo.attach(servoPin);     // 서보모터 핀 설정
  pinMode(magneticSensorPin, INPUT_PULLUP); // 마그네틱 센서 핀을 내부 풀업으로 설정
  pinMode(buttonPin, INPUT_PULLUP);  // 버튼 핀을 내부 풀업으로 설정
  
  // 초기 위치 설정 (90도, 잠긴 상태)
  myServo.write(90);
  
  Serial.println("블루투스 도어락 제어 시작!");
  BTSerial.println("블루투스 연결 완료! 도어락 시스템 준비됨");
}

void loop() {
  // 블루투스에서 데이터 수신
  if (BTSerial.available()) {
    char c = BTSerial.read();
    Serial.write(c);  // 시리얼 모니터에도 출력
    
    // 수신된 문자에 따라 서보모터 제어
    if (c == 'o' || c == 'O') {  // 'o'를 받으면 열기 (인증 성공)
      unlockDoor();
    }
  }
  
  // 버튼 상태 읽기
  buttonState = digitalRead(buttonPin);
  
  // 버튼이 눌렸을 때 (LOW 상태, 풀업 저항 사용 시)
  if (buttonState == LOW && lastButtonState == HIGH) {
    // 디바운싱을 위한 짧은 딜레이
    delay(50);
    
    // 버튼 상태 다시 확인 (디바운싱)
    if (digitalRead(buttonPin) == LOW) {
      Serial.println("버튼으로 문 열음");
      unlockDoor();
    }
  }
  
  // 현재 버튼 상태를 이전 상태로 저장
  lastButtonState = buttonState;
  
  // 문이 열린 후 일정 시간이 지났고, 마그네틱 센서가 문이 닫혔음을 감지하면 잠금
  if (doorOpening && (millis() - doorOpenTime > 5000)) {
    // 문이 열린 후 5초가 지났으면 마그네틱 센서 상태 확인
    int sensorState = digitalRead(magneticSensorPin);
    
    // 마그네틱 센서가 닫힘을 감지하면 (LOW)
    if (sensorState == LOW) {
      doorClosed = true;
      doorOpening = false;
      lockDoor();
    }
  }
  
  // 시리얼 모니터에서 입력받은 데이터를 블루투스로 전송 (디버깅용)
  if (Serial.available()) {
    char c = Serial.read();
    BTSerial.write(c);
  }
}

// 도어락 잠금 함수
void lockDoor() {
  myServo.write(90); // 서보모터를 90도로 이동 (잠금 위치)
  Serial.println("문 닫힘 감지! 도어락 잠금.");
  BTSerial.println("door_closed"); // 파이썬으로 문 닫힘 상태 전송
}

// 도어락 열기 함수
void unlockDoor() {
  myServo.write(180); // 서보모터를 180도로 이동 (열림 위치)
  Serial.println("인증 성공! 도어락 열림.");
  BTSerial.println("door_unlocked"); // 파이썬으로 도어락 열림 상태 전송
  doorClosed = false; // 도어락이 열렸으니 문 닫힘 상태 아님
  doorOpening = true; // 문이 열리는 중임을 표시
  doorOpenTime = millis(); // 현재 시간 저장
}