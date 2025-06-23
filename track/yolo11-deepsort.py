import cv2
import torch
from ultralytics import YOLO
from deep_sort_realtime.deepsort_tracker import DeepSort

# YOLO 모델 로드
model = YOLO("yolo11n.pt")
model.conf = 0.4  # 신뢰도 임계값 설정

# DeepSORT 초기화
tracker = DeepSort(max_age=30, n_init=3, nn_budget=100)

# HTTP 스트림 URL
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
    detections = []
    for result in results:
        boxes = result.boxes.xyxy  # 바운딩 박스 좌표
        confidences = result.boxes.conf  # 신뢰도
        classes = result.boxes.cls  # 클래스 ID

        for box, conf, cls in zip(boxes, confidences, classes):
            if int(cls) == 0:  # 클래스 ID 0: 사람
                x1, y1, x2, y2 = box.tolist()  # 텐서를 리스트로 변환
                x, y, w, h = x1, y1, x2 - x1, y2 - y1
                detections.append([x, y, w, h, float(conf)])  # [x, y, w, h, confidence]

    # DeepSORT로 추적
    if detections:
        tracked_objects = tracker.update_tracks(detections, frame=frame)
    else:
        tracked_objects = []

    # 추적된 객체 시각화
    for track in tracked_objects:
        if not track.is_confirmed() or track.time_since_update > 1:
            continue
        x1, y1, x2, y2 = map(int, track.to_tlbr())  # 바운딩 박스 좌표
        track_id = track.track_id  # 객체 ID
        label = f"ID {track_id}"
        cv2.rectangle(frame, (x1, y1), (x2, y2), (255, 0, 0), 2)  # 파란색 경계 상자
        cv2.putText(frame, label, (x1, y1 - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 0, 0), 2)

    # 결과 화면 출력
    cv2.imshow("YOLO + DeepSORT Tracking", frame)

    # 'q' 키를 누르면 종료
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cap.release()
cv2.destroyAllWindows()
