# app pc에 이 기능 추가
# 1. 추적 시작 시 맨 처음 사람 한 명을 기억-추적
# 2. 3초 이상 화면에서 사라지면 target_id 초기화 → 새로운 사람을 다시 추적
# 추가표시: !!, 자세한 설명 apppc참고.
# Raspberry Pi에서 MJPEG 스트리밍으로 전송되는 카메라 영상을 받아, YOLO 사람 탐지, 사람의 위치 따라 Pi로 제어 명령을 전송
# 재탐지 모드 x, 가까이가면 멈춤 구현x

import cv2
import numpy as np
import time
import requests
from ultralytics import YOLO
from deep_sort_realtime.deepsort_tracker import DeepSort

# === MJPEG 스트리밍 및 제어 URL 설정 ===
stream_url = "http://192.168.243.153:5000/video_feed"    #라즈베리파이 ip 쓰면됨.
control_url = "http://192.168.243.153:8000/control"

# === YOLO 모델 로드 ===
model = YOLO("yolo11n.pt")

# === DeepSORT 초기화 ===
tracker = DeepSort(max_age=15)

# === 스트리밍 연결 ===
stream = requests.get(stream_url, stream=True, timeout=5)
frame_width = 640
frame_center = frame_width // 2
bytes_buffer = b""

last_seen_time = 0
last_direction = "stop"
target_id = None  # 🎯 처음 추적한 객체의 ID !!

# === 명령 전송 함수 ===
def send_command(cmd):
    try:
        requests.post(control_url, json={"cmd": cmd}, timeout=0.3)
        print(f"→ 명령 전송: {cmd}")
    except:
        print(f"❌ 명령 전송 실패: {cmd}")

# === 스트리밍 루프 ===
for chunk in stream.iter_content(chunk_size=4096):
    bytes_buffer += chunk
    a = bytes_buffer.find(b'\xff\xd8')
    b = bytes_buffer.find(b'\xff\xd9')

    if a != -1 and b != -1 and a < b:
        jpg = bytes_buffer[a:b+2]
        bytes_buffer = bytes_buffer[b+2:]
        frame = cv2.imdecode(np.frombuffer(jpg, dtype=np.uint8), cv2.IMREAD_COLOR)

        if frame is None:
            continue

        results = model.predict(frame, conf=0.4)
        detections = results[0].boxes

        person_dets = []
        for box, conf, cls in zip(detections.xyxy, detections.conf, detections.cls):
            if int(cls.item()) == 0:  # 사람 클래스만
                x1, y1, x2, y2 = map(int, box)
                w, h = x2 - x1, y2 - y1
                person_dets.append(([x1, y1, w, h], conf.item(), "person"))

        tracks = tracker.update_tracks(person_dets, frame=frame)

        best_track = None
        min_offset = float("inf")
        now = time.time()

        for track in tracks:
            if not track.is_confirmed():
                continue

            if target_id is None:
                target_id = track.track_id  # 처음 본 객체 ID 설정 !!

            if track.track_id != target_id:
                continue  # 처음 본 객체 외에는 무시 !!

            l, t, r, b = track.to_ltrb()
            cx = int((l + r) / 2)
            offset = abs(cx - frame_center)
            if offset < min_offset:
                min_offset = offset
                best_track = (l, t, r, b, cx)

        if best_track:
            l, t, r, b, cx = best_track
            cv2.rectangle(frame, (int(l), int(t)), (int(r), int(b)), (0, 255, 0), 2)
            cv2.line(frame, (cx, 0), (cx, frame.shape[0]), (255, 0, 0), 2)

            if cx < frame_center - 120:
                send_command("left")
                last_direction = "left"
            elif cx > frame_center + 120:
                send_command("right")
                last_direction = "right"
            else:
                send_command("forward")
                last_direction = "forward"

            last_seen_time = now

        else:
            if now - last_seen_time < 0.5:
                send_command(last_direction)
            else:
                send_command("stop")
                last_direction = "stop"

            # 🎯 일정 시간 이상 놓치면 target_id 초기화 !!
            if target_id is not None and (now - last_seen_time > 3.0):
                print("⏱️ 추적 대상 놓침 → target_id 초기화")
                target_id = None

        cv2.imshow("Tracking", frame)
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

# === 종료 ===
cv2.destroyAllWindows()
stream.close()
