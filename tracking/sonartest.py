# ultrasonic_test.py
from gpiozero import DistanceSensor
from time import sleep

# === 초음파 센서 설정 ===
# 네 메인 코드에 사용된 핀 번호 그대로 가져왔어!
# DistanceSensor(echo_pin, trigger_pin) 순서야.
front_sensor = DistanceSensor(echo=10, trigger=9, max_distance=2.0)
left_sensor = DistanceSensor(echo=17, trigger=4, max_distance=2.0)
right_sensor = DistanceSensor(echo=8, trigger=7, max_distance=2.0)

print("--------------------------------------------------", flush=True)
print("초음파 센서 테스트를 시작합니다! 🤖", flush=True)
print("각 센서 앞에 손을 대거나 물체를 놓아보세요.", flush=True)
print("Ctrl+C를 누르면 테스트가 종료됩니다.", flush=True)
print("--------------------------------------------------", flush=True)

try:
    while True:
        # 각 센서의 거리를 측정하고 cm 단위로 변환
        # 센서가 응답하지 않으면 max_distance (200cm)를 반환할 수 있어.
        # 또는 gpiozero에서 DistanceSensorNoEcho 경고를 띄울 수 있어.
        front_distance = front_sensor.distance * 100
        left_distance = left_sensor.distance * 100
        right_distance = right_sensor.distance * 100

        # 측정된 거리를 출력
        print(f"정면: {front_distance:.1f} cm | 왼쪽: {left_distance:.1f} cm | 오른쪽: {right_distance:.1f} cm", flush=True)

        # 너무 빠르게 측정하지 않도록 잠시 기다려줘
        sleep(0.2) # 0.2초 간격으로 측정

except KeyboardInterrupt:
    # Ctrl+C를 누르면 프로그램 종료
    print("\n테스트를 종료합니다. 안녕! 👋", flush=True)
finally:
    # 센서 리소스 정리 (깔끔하게 마무리하는 게 좋아!)
    front_sensor.close()
    left_sensor.close()
    right_sensor.close()
    print("센서 리소스가 해제되었습니다.", flush=True)

