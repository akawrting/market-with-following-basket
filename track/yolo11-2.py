import torch
import cv2
from ultralytics import YOLO

# YOLO 모델 로드
model = YOLO("yolo11n.pt")
model.conf = 0.4  # 신뢰도 임계값 설정

# HTTP 스트림 URL (웹캠 대신 HTTP 스트림에서 입력받음)
stream_url = "http://192.168.0.4:5000/video_feed"
cap = cv2.VideoCapture(stream_url)

if not cap.isOpened():
    print("HTTP 스트림에 연결할 수 없습니다.")
    exit()

while True:
    ret, frame = cap.read()
    if not ret:
        print("프레임을 읽을 수 없습니다.")
        break

    # YOLO 추론 실행
    results = model(frame)

    # 사람만 필터링 (Class ID: 0)
    for result in results:
        boxes = result.boxes.xyxy  # 바운딩 박스 좌표
        confidences = result.boxes.conf  # 신뢰도
        classes = result.boxes.cls  # 클래스 ID

        for box, conf, cls in zip(boxes, confidences, classes):
            if int(cls) == 0:  # 클래스 ID 0: 사람
                x1, y1, x2, y2 = map(int, box)  # 바운딩 박스 좌표 정수 변환
                label = f"Person {conf:.2f}"  # 라벨에 신뢰도 추가
                cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)  # 초록색 경계 상자
                cv2.putText(frame, label, (x1, y1 - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 255, 0), 2)

    # 결과 화면 출력
    cv2.imshow("Person Detection", frame)

    # 'q' 키를 누르면 종료
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cap.release()
cv2.destroyAllWindows()
