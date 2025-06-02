import cv2
import mediapipe as mp
import pickle
import mysql.connector
import numpy as np

# 얼굴 벡터를 저장할 리스트
face_vectors = []

# MediaPipe 설정
mp_face_mesh = mp.solutions.face_mesh
face_mesh = mp_face_mesh.FaceMesh(
    static_image_mode=False,
    max_num_faces=1,
    min_detection_confidence=0.5,
    min_tracking_confidence=0.5
)

# 카메라 열기
cap = cv2.VideoCapture(0)
if not cap.isOpened():
    print("❌ 카메라를 열 수 없습니다.")
    exit()

directions = ["정면", "좌측", "우측"]
idx = 0

print("📷 얼굴 등록을 시작합니다.")

while idx < 3:
    ret, frame = cap.read()
    if not ret:
        continue

    frame = cv2.flip(frame, 1)
    frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
    results = face_mesh.process(frame_rgb)

    cv2.putText(frame, f"{directions[idx]}을 바라보고 's'를 누르세요", (50, 50),
                cv2.FONT_HERSHEY_SIMPLEX, 0.9, (0, 255, 255), 2)

    cv2.imshow("Face Registration", frame)

    key = cv2.waitKey(1)
    if key == ord('s') and results.multi_face_landmarks:
        face_vector = []
        for landmark in results.multi_face_landmarks[0].landmark:
            face_vector.extend([landmark.x, landmark.y, landmark.z])
        face_vectors.append(face_vector)
        print(f"✅ {directions[idx]} 얼굴 저장됨")
        idx += 1
    elif key == 27:  # ESC
        break

cap.release()
cv2.destroyAllWindows()

if len(face_vectors) != 3:
    print("❌ 얼굴 데이터를 모두 저장하지 못했습니다.")
    exit()

# DB에 저장
try:
    face_data = pickle.dumps(face_vectors)
    conn = mysql.connector.connect(
        host="127.0.0.1",
        user="famarket",
        password="qpalzm1029!",
        database="famarket"
    )
    cursor = conn.cursor()
    print(face_data)
    cursor.execute("UPDATE datatbl SET uservector = %s WHERE userid = %s", (face_data, 'bjb4095'))
    conn.commit()
    cursor.close()
    conn.close()
    print("🎉 얼굴 데이터가 성공적으로 저장되었습니다.")
except Exception as e:
    print(f"❌ DB 저장 중 오류 발생: {e}")
