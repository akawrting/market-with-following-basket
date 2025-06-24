import cv2
from picamera2 import Picamera2
from libcamera import Transform
from gpiozero import PWMOutputDevice, DigitalOutputDevice, DistanceSensor
from time import sleep

# === GPIO 핀 설정 ===
PWMA = PWMOutputDevice(18)
AIN1 = DigitalOutputDevice(22) #왼 바퀴 후진
AIN2 = DigitalOutputDevice(27) #왼 바퀴 전진
PWMB = PWMOutputDevice(23)
BIN1 = DigitalOutputDevice(25) #오른 바퀴 후진
BIN2 = DigitalOutputDevice(24) #오른 바퀴 전진

# === 초음파 센서 설정 ===
front_sensor = DistanceSensor(echo=10, trigger=9, max_distance=2.0)
left_sensor = DistanceSensor(echo=17, trigger=4, max_distance=2.0)
right_sensor = DistanceSensor(echo=8, trigger=7, max_distance=2.0)



# === 모터 제어 함수 ===
def stop_motors():
    PWMA.value = 0.0
    PWMB.value = 0.0

def move_forward():
    AIN1.value, AIN2.value = 0, 1
    BIN1.value, BIN2.value = 0, 1
    PWMA.value = 0.6
    PWMB.value = 0.6

def turn_left():
    AIN1.value, AIN2.value = 1, 0
    BIN1.value, BIN2.value = 0, 1
    PWMA.value = 0.3
    PWMB.value = 0.3

def turn_right():
    AIN1.value, AIN2.value = 0, 1
    BIN1.value, BIN2.value = 1, 0
    PWMA.value = 0.3
    PWMB.value = 0.3

def soft_turn_left():
    AIN1.value, AIN2.value = 0, 0
    BIN1.value, BIN2.value = 0, 1
    PWMA.value = 0.6
    PWMB.value = 0.6

def soft_turn_right():
    AIN1.value, AIN2.value = 0, 1
    BIN1.value, BIN2.value = 0, 0
    PWMA.value = 0.6
    PWMB.value = 0.6

# === 장애물 회피 함수 ===
def obstacle_avoidance():
    global last_avoid  # <- 전역변수 사용 명시
    front = front_sensor.distance * 100
    left = left_sensor.distance * 100
    right = right_sensor.distance * 100

    print(f"거리 - 정면: {front:.1f}cm, 왼쪽: {left:.1f}cm, 오른쪽: {right:.1f}cm")

    if front < 20:
        if left < 10:
            print("↩️ 왼쪽 회피")
            turn_left()
            last_avoid = "left"
        elif right < 10:
            print("↪️ 오른쪽 회피")
            turn_right()
            last_avoid = "right"
        elif left >= 10 and right >= 10:
            print("대상과 적정거리 유지")
            stop_motors()
            sleep(5)

        

        # 원래 방향 복원
        # if last_avoid == "left":
        #     print("↪️ 오른쪽 복귀")
        #     turn_right()
        #     sleep(1)
        # elif last_avoid == "right":
        #     print("↩️ 왼쪽 복귀")
        #     turn_left()
        #     sleep(1)

        # print("⬆️ 전진 복귀")
        # move_forward()
        # sleep(0.5)
        # return True

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

classNames = {0: 'background',
              1: 'person', 2: 'bicycle', 3: 'car', 4: 'motorcycle', 5: 'airplane', 6: 'bus',
              7: 'train', 8: 'truck', 9: 'boat', 10: 'traffic light', 11: 'fire hydrant',
              13: 'stop sign', 14: 'parking meter', 15: 'bench', 16: 'bird', 17: 'cat',
              18: 'dog', 19: 'horse', 20: 'sheep', 21: 'cow', 22: 'elephant', 23: 'bear',
              24: 'zebra', 25: 'giraffe', 27: 'backpack', 28: 'umbrella', 31: 'handbag',
              32: 'tie', 33: 'suitcase', 34: 'frisbee', 35: 'skis', 36: 'snowboard',
              37: 'sports ball', 38: 'kite', 39: 'baseball bat', 40: 'baseball glove',
              41: 'skateboard', 42: 'surfboard', 43: 'tennis racket', 44: 'bottle',
              46: 'wine glass', 47: 'cup', 48: 'fork', 49: 'knife', 50: 'spoon',
              51: 'bowl', 52: 'banana', 53: 'apple', 54: 'sandwich', 55: 'orange',
              56: 'broccoli', 57: 'carrot', 58: 'hot dog', 59: 'pizza', 60: 'donut',
              61: 'cake', 62: 'chair', 63: 'couch', 64: 'potted plant', 65: 'bed',
              67: 'dining table', 70: 'toilet', 72: 'tv', 73: 'laptop', 74: 'mouse',
              75: 'remote', 76: 'keyboard', 77: 'cell phone', 78: 'microwave', 79: 'oven',
              80: 'toaster', 81: 'sink', 82: 'refrigerator', 84: 'book', 85: 'clock',
              86: 'vase', 87: 'scissors', 88: 'teddy bear', 89: 'hair drier', 90: 'toothbrush'}

def id_class_name(class_id, classes):
    return classes.get(class_id, "Unknown")

def main():
    camera = Picamera2()
    camera.configure(camera.create_preview_configuration(
        main={"format": 'XRGB8888', "size": (640, 480)},
        transform=Transform(hflip=1, vflip=1) # 수평, 수직 뒤집기 동시에 적용 (180도 회전 효과)
    ))
    
    try:
        camera.start()
        print("✅ Picamera2가 성공적으로 시작되었습니다.")
    except Exception as e:
        print(f"🚨🚨🚨 에러: Picamera2를 시작할 수 없습니다! 🚨🚨🚨")
        print(f"  - 에러 메시지: {e}")
        print("  1. 카메라 모듈이 제대로 연결되어 있나요?")
        print("  2. 'sudo raspi-config'에서 카메라 옵션이 활성화되어 있나요?")
        print("  3. 다른 프로그램이 카메라를 사용하고 있지는 않나요?")
        print("  4. 라즈베리 파이를 재부팅해보셨나요?")
        return

    try:
        model = cv2.dnn.readNetFromTensorflow('/home/robot/market-with-following-basket/tracking/models/frozen_inference_graph.pb',
                                      '/home/robot/market-with-following-basket/tracking/models/ssd_mobilenet_v2_coco_2018_03_29.pbtxt')
        
        if model.empty():
            print("🚨 에러: DNN 모델을 로드할 수 없습니다. 파일 경로와 파일 손상 여부를 확인하세요.")
            return
        last_state = "stop"
        while True:
            keyValue = cv2.waitKey(1)
        
            if keyValue == ord('q') :
                break
            
            image = camera.capture_array()
            image = cv2.cvtColor(image, cv2.COLOR_BGRA2BGR)
            
            image_height, image_width, _ = image.shape

            model.setInput(cv2.dnn.blobFromImage(image, size=(300, 300), swapRB=True))
            output = model.forward()

            person_detected = False
            
            front = front_sensor.distance * 100
            left = left_sensor.distance * 100
            right = right_sensor.distance * 100
            print(f"거리 - 정면: {front:.1f}cm, 왼쪽: {left:.1f}cm, 오른쪽: {right:.1f}cm")

            for detection in output[0, 0, :, :]:
                confidence = detection[2]
                class_id = int(detection[1])
                
                if confidence > .5 and class_id == 1: # 신뢰도가 0.5 이상이고, 클래스 ID가 1 (사람)인 경우
                    class_name=id_class_name(class_id,classNames)

                    person_detected = True

                    box_x_min = int(detection[3] * image_width)
                    box_y_min = int(detection[4] * image_height)
                    box_x_max = int(detection[5] * image_width)
                    box_y_max = int(detection[6] * image_height)
                    box_center_x = int((box_x_min + box_x_max) / 2)

                    cv2.rectangle(image, (box_x_min, box_y_min), (box_x_max, box_y_max), (0, 0, 200), thickness=2)
                    cv2.line(image, (box_center_x, box_y_min), (box_center_x, box_y_max), (200, 0, 0), thickness=2)
                    print(f"box_y_min: {box_y_min}, box_y_max: {box_y_max}")
                          
                    if box_y_min < 50:
                        stop_motors()
                        print("대상과 적정거리 유지")
                    
                    elif left < 20:
                        soft_turn_right()
                        print("왼쪽에 장애물 → 오른쪽 휘어서 진행")
                    
                    elif right < 20:
                        soft_turn_left()
                        print("오른쪽에 장애물 → 왼쪽 휘어서 진행")

                    elif box_center_x < image_width // 2 - 40:
                        #if not obstacle_avoidance():
                            soft_turn_left()
                            print("목표가 왼쪽에 있음")
                            last_state = "left"
                    elif box_center_x > image_width // 2 + 40:
                        #if not obstacle_avoidance():
                            soft_turn_right()
                            last_state = "right"
                            print("목표가 오른쪽에 있음")
                    else :
                        #if not obstacle_avoidance():
                            move_forward()
                            print("목표가 정면에 있음")
                    
                    
                    # if front < 30:
                    #     stop_motors()
                    #     print("대상과 적정거리 유지")

                    text_x = box_x_min
                    text_y = box_y_min - 10 if box_y_min - 10 > 10 else box_y_min + 20 
                    text = f"{class_name}: {confidence:.2f}"
                    font_scale = 0.7
                    font_thickness = 2
                    cv2.putText(image, text, (text_x, text_y), cv2.FONT_HERSHEY_SIMPLEX, font_scale, (0, 0, 255), font_thickness)
                
            if not person_detected:
                if last_state == "left":
                    turn_left()
                    print("마지막 위치 왼쪽")
                elif last_state == "right":
                    turn_right()
                    print("마지막 위치 오른쪽")
                # stop_motors()
                # print("사람이 감지되지 않음, 정지")

            cv2.imshow('Object Detection Result', image)
                        
    except KeyboardInterrupt:
        print("\n프로그램을 종료합니다. (Ctrl+C 감지)")
    except Exception as e:
        print(f"🚨 예상치 못한 에러 발생: {e}")
    finally:
        try:
            if 'camera' in locals() and camera.started:
                camera.stop()
                print("✅ Picamera2가 성공적으로 중지되었습니다.")
        except Exception as e:
            print(f"🚨 경고: Picamera2 중지 중 에러 발생: {e}")
        cv2.destroyAllWindows()


if __name__ == '__main__':
    main()
