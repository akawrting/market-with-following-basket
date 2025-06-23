# flashcart에 초음파 추가

from flask import Flask, request
from gpiozero import PWMOutputDevice, DigitalOutputDevice, DistanceSensor
from time import sleep

app = Flask(__name__)

# === GPIO 핀 설정 ===
PWMA = PWMOutputDevice(18)
AIN1 = DigitalOutputDevice(22)
AIN2 = DigitalOutputDevice(27)
PWMB = PWMOutputDevice(23)
BIN1 = DigitalOutputDevice(25)
BIN2 = DigitalOutputDevice(24)

# === 초음파 센서 설정 ===
front_sensor = DistanceSensor(echo=10, trigger=9, max_distance=2.0)
left_sensor = DistanceSensor(echo=17, trigger=4, max_distance=2.0)
right_sensor = DistanceSensor(echo=8, trigger=7, max_distance=2.0)

# === 모터 제어 함수 ===
def stop_motors():
    PWMA.value = 0.0
    PWMB.value = 0.0

def move_forward(speed=0.6):
    AIN1.value, AIN2.value = 0, 1
    BIN1.value, BIN2.value = 0, 1
    PWMA.value = speed
    PWMB.value = speed

def steer_left(speed=0.4):
    AIN1.value, AIN2.value = 0, 1
    BIN1.value, BIN2.value = 0, 1
    PWMA.value = speed * 0.5
    PWMB.value = speed

def steer_right(speed=0.4):
    AIN1.value, AIN2.value = 0, 1
    BIN1.value, BIN2.value = 0, 1
    PWMA.value = speed
    PWMB.value = speed * 0.5

def turn_left(speed=0.6):
    AIN1.value, AIN2.value = 1, 0
    BIN1.value, BIN2.value = 0, 1
    PWMA.value = speed
    PWMB.value = speed

def turn_right(speed=0.6):
    AIN1.value, AIN2.value = 0, 1
    BIN1.value, BIN2.value = 1, 0
    PWMA.value = speed
    PWMB.value = speed

def soft_turn_left(speed=0.6):
    AIN1.value, AIN2.value = 0, 1
    BIN1.value, BIN2.value = 0, 0
    PWMA.value = speed
    PWMB.value = 0

def soft_turn_right(speed=0.6):
    AIN1.value, AIN2.value = 0, 0
    BIN1.value, BIN2.value = 0, 1
    PWMA.value = 0
    PWMB.value = speed

# === 장애물 회피 함수 ===
def obstacle_avoidance():
    global last_avoid  # <- 전역변수 사용 명시
    front = front_sensor.distance * 100
    left = left_sensor.distance * 100
    right = right_sensor.distance * 100

    print(f"거리 - 정면: {front:.1f}cm, 왼쪽: {left:.1f}cm, 오른쪽: {right:.1f}cm")

    if front < 15:
        stop_motors()
        print("❗ 정면 장애물 감지")
        if left > right:
            print("↩️ 왼쪽 회피")
            turn_left()
            last_avoid = "left"
        else:
            print("↪️ 오른쪽 회피")
            turn_right()
            last_avoid = "right"
        sleep(1.0)
        move_forward()
        sleep(1.0)
        stop_motors()

        # 원래 방향 복원
        if last_avoid == "left":
            print("↪️ 오른쪽 복귀")
            turn_right()
            sleep(1)
        elif last_avoid == "right":
            print("↩️ 왼쪽 복귀")
            turn_left()
            sleep(1)

        print("⬆️ 전진 복귀")
        move_forward()
        sleep(0.5)
        return True

    elif left < 20 and right >= 20:
        print("🐍 왼쪽에 장애물 → 오른쪽 휘어서 진행")
        soft_turn_right()
        sleep(0.3)
        return True

    elif right < 20 and left >= 20:
        print("🐍 오른쪽에 장애물 → 왼쪽 휘어서 진행")
        soft_turn_left()
        sleep(0.3)
        return True

    elif left < 20 and right < 20:
        print("❗ 양옆 장애물 → 정면 확인 후 정지 또는 회피")
        if front > 20:
            print("⬆️ 전진 가능")
            move_forward()
        else:
            print("🛑 양옆 + 정면 장애물 → 멈춤")
            stop_motors()
        sleep(0.3)
        return True

    return False  # 회피 필요 없음

            
    return False  # 회피 필요 없음

@app.route('/control', methods=['POST'])
def control():
    data = request.get_json()
    cmd = data.get("cmd", "")

    if cmd == "forward":
        if not obstacle_avoidance():
            move_forward()
    elif cmd == "left":
        if not obstacle_avoidance():
            steer_left()
    elif cmd == "right":
        if not obstacle_avoidance():
            steer_right()
    elif cmd == "stop":
        stop_motors()

    return {"status": "ok", "executed": cmd}

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=8000)
