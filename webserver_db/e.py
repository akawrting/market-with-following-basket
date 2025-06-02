# -*- coding: utf-8 -*-
import sys
import os
import cv2
import numpy as np
import mediapipe as mp
import pickle
import traceback
from PyQt5.QtWidgets import (QApplication, QMainWindow, QWidget, QPushButton, 
                            QVBoxLayout, QHBoxLayout, QLabel, QMessageBox)
from PyQt5.QtCore import Qt, QTimer, pyqtSlot, QUrl, QObject
from PyQt5.QtGui import QImage, QPixmap
from PyQt5.QtWebEngineWidgets import QWebEngineView
from PyQt5.QtWebChannel import QWebChannel
import mysql.connector

class Bridge(QObject):
    def __init__(self, callback):
        super().__init__()
        self.callback = callback

    @pyqtSlot(str)  # ✅ int → str 로 바꿔야 문자열 ID 전달 가능
    def onFormSubmitted(self, user_id):
        print("받은 user_id:", user_id, type(user_id))  # 확인용
        self.callback(user_id)



class FaceRecognitionApp(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("얼굴인식 출입 시스템")
        self.setGeometry(100, 100, 800, 600)

        self.mp_face_mesh = mp.solutions.face_mesh
        self.face_mesh = self.mp_face_mesh.FaceMesh(
            static_image_mode=False,
            max_num_faces=1,
            min_detection_confidence=0.5,
            min_tracking_confidence=0.5
        )

        self.central_widget = QWidget()
        self.setCentralWidget(self.central_widget)
        self.main_layout = QVBoxLayout(self.central_widget)

        self.cap = None
        self.timer = QTimer()
        self.timer.timeout.connect(self.update_frame)

        self.current_mode = "main"
        self.face_vectors = []
        self.current_user_id = None

        self.setup_main_ui()

    def setup_main_ui(self):
        for i in reversed(range(self.main_layout.count())):
            self.main_layout.itemAt(i).widget().setParent(None)

        title_label = QLabel("얼굴인식 출입 시스템")
        title_label.setAlignment(Qt.AlignCenter)
        title_label.setStyleSheet("font-size: 24px; font-weight: bold; margin: 20px;")
        self.main_layout.addWidget(title_label)

        button_container = QWidget()
        button_layout = QVBoxLayout(button_container)

        self.register_button = QPushButton("처음 오셨나요? (회원가입)")
        self.register_button.setMinimumHeight(60)
        self.register_button.setStyleSheet("""
            QPushButton {
                background-color: #4CAF50;
                color: white;
                border-radius: 10px;
                font-size: 18px;
                padding: 10px;
            }
            QPushButton:hover {
                background-color: #45a049;
            }
        """)
        self.register_button.clicked.connect(self.show_signup_page)
        button_layout.addWidget(self.register_button)

        button_layout.addSpacing(20)

        self.enter_button = QPushButton("입장하기 (얼굴인식)")
        self.enter_button.setMinimumHeight(60)
        self.enter_button.setStyleSheet("""
            QPushButton {
                background-color: #2196F3;
                color: white;
                border-radius: 10px;
                font-size: 18px;
                padding: 10px;
            }
            QPushButton:hover {
                background-color: #0b7dda;
            }
        """)
        self.enter_button.clicked.connect(self.start_face_recognition)
        button_layout.addWidget(self.enter_button)

        button_container.setContentsMargins(100, 50, 100, 50)
        self.main_layout.addWidget(button_container)

    def show_signup_page(self):
        for i in reversed(range(self.main_layout.count())):
            self.main_layout.itemAt(i).widget().setParent(None)

        self.web_view = QWebEngineView()
        self.channel = QWebChannel()
        self.bridge = Bridge(self.on_signup_success)
        self.channel.registerObject("bridge", self.bridge)
        self.web_view.page().setWebChannel(self.channel)

        self.web_view.load(QUrl("http://localhost:8080/signup.php"))

        back_button = QPushButton("뒤로가기")
        back_button.clicked.connect(self.setup_main_ui)
        back_button.setStyleSheet("padding: 10px;")

        self.main_layout.addWidget(self.web_view)
        self.main_layout.addWidget(back_button)
        self.current_mode = "signup"

    def start_face_registration(self, user_id):
        for i in reversed(range(self.main_layout.count())):
            self.main_layout.itemAt(i).widget().setParent(None)

        self.current_user_id = user_id
        self.current_mode = "face_registration"
        self.face_vectors = []

        instruction_label = QLabel("얼굴 등록을 시작합니다.\n정면, 좌측, 우측 얼굴을 차례로 촬영합니다.")
        instruction_label.setAlignment(Qt.AlignCenter)
        instruction_label.setStyleSheet("font-size: 18px; margin: 10px;")
        self.main_layout.addWidget(instruction_label)

        self.camera_label = QLabel()
        self.camera_label.setAlignment(Qt.AlignCenter)
        self.main_layout.addWidget(self.camera_label)

        self.status_label = QLabel("정면을 바라봐주세요")
        self.status_label.setAlignment(Qt.AlignCenter)
        self.status_label.setStyleSheet("font-size: 20px; font-weight: bold; color: #2196F3;")
        self.main_layout.addWidget(self.status_label)

        button_container = QWidget()
        button_layout = QHBoxLayout(button_container)

        self.capture_button = QPushButton("촬영하기")
        self.capture_button.clicked.connect(self.capture_face)
        button_layout.addWidget(self.capture_button)

        cancel_button = QPushButton("취소")
        cancel_button.clicked.connect(self.setup_main_ui)
        button_layout.addWidget(cancel_button)

        self.main_layout.addWidget(button_container)
        self.start_camera()

    def start_camera(self):
        if self.cap is None:
            self.cap = cv2.VideoCapture(0)

        if not self.cap.isOpened():
            QMessageBox.critical(self, "오류", "카메라를 열 수 없습니다.")
            self.setup_main_ui()
            return

        self.timer.start(30)

    def update_frame(self):
        ret, frame = self.cap.read()
        if not ret:
            return

        frame = cv2.flip(frame, 1)
        if self.current_mode in ["face_registration", "face_recognition"]:
            frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
            results = self.face_mesh.process(frame_rgb)
            if results.multi_face_landmarks:
                for face_landmarks in results.multi_face_landmarks:
                    mp.solutions.drawing_utils.draw_landmarks(
                        frame, face_landmarks, self.mp_face_mesh.FACEMESH_CONTOURS)

        h, w, ch = frame.shape
        bytes_per_line = ch * w
        convert_to_qt_format = QImage(frame.data, w, h, bytes_per_line, QImage.Format_RGB888)
        qt_frame = convert_to_qt_format.scaled(640, 480, Qt.KeepAspectRatio)
        self.camera_label.setPixmap(QPixmap.fromImage(qt_frame))

    def capture_face(self):
        if not self.cap or not self.cap.isOpened():
            return
        ret, frame = self.cap.read()
        if not ret:
            return
        frame = cv2.flip(frame, 1)
        frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        results = self.face_mesh.process(frame_rgb)

        if results.multi_face_landmarks:
            face_vector = []
            for face_landmarks in results.multi_face_landmarks:
                for landmark in face_landmarks.landmark:
                    face_vector.extend([landmark.x, landmark.y, landmark.z])
            self.face_vectors.append(face_vector)
            if len(self.face_vectors) == 1:
                self.status_label.setText("좌측을 바라봐주세요")
            elif len(self.face_vectors) == 2:
                self.status_label.setText("우측을 바라봐주세요")
            elif len(self.face_vectors) == 3:
                self.save_face_vectors()
        else:
            QMessageBox.warning(self, "경고", "얼굴을 감지할 수 없습니다. 다시 시도해주세요.")

    def save_face_vectors(self):
        self.timer.stop()
        if self.cap:
            self.cap.release()
            self.cap = None
        try:
            face_data = pickle.dumps(self.face_vectors)
            conn = mysql.connector.connect(
                host="127.0.0.1",
                user="famarket",
                password="qpalzm1029!",
                database="famarket"
            )
            cursor = conn.cursor()
            cursor.execute("UPDATE datatbl SET uservector = %s WHERE userid = %s", (face_data, self.current_user_id))
            conn.commit()
            cursor.close()
            conn.close()
            QMessageBox.information(self, "성공", "얼굴 등록이 완료되었습니다!")
        except Exception as e:
            print(traceback.format_exc())
            QMessageBox.critical(self, "오류", f"얼굴 데이터 저장 오류: {str(e)}")
        self.setup_main_ui()

    def start_face_recognition(self):
        for i in reversed(range(self.main_layout.count())):
            self.main_layout.itemAt(i).widget().setParent(None)
        self.current_mode = "face_recognition"

        instruction_label = QLabel("얼굴 인식을 시작합니다.\n카메라를 정면으로 바라봐주세요.")
        instruction_label.setAlignment(Qt.AlignCenter)
        instruction_label.setStyleSheet("font-size: 18px; margin: 10px;")
        self.main_layout.addWidget(instruction_label)

        self.camera_label = QLabel()
        self.camera_label.setAlignment(Qt.AlignCenter)
        self.main_layout.addWidget(self.camera_label)

        self.status_label = QLabel("얼굴을 인식하는 중...")
        self.status_label.setAlignment(Qt.AlignCenter)
        self.status_label.setStyleSheet("font-size: 20px; font-weight: bold; color: #2196F3;")
        self.main_layout.addWidget(self.status_label)

        button_container = QWidget()
        button_layout = QHBoxLayout(button_container)

        self.recognize_button = QPushButton("인식하기")
        self.recognize_button.clicked.connect(self.recognize_face)
        button_layout.addWidget(self.recognize_button)

        cancel_button = QPushButton("취소")
        cancel_button.clicked.connect(self.setup_main_ui)
        button_layout.addWidget(cancel_button)

        self.main_layout.addWidget(button_container)
        self.start_camera()

    def recognize_face(self):
        if not self.cap or not self.cap.isOpened():
            return
    
        try:  # 전체 함수를 try 블록으로 감싸기
            ret, frame = self.cap.read()
            if not ret:
                return
            frame = cv2.flip(frame, 1)
            frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
            results = self.face_mesh.process(frame_rgb)

            if results.multi_face_landmarks:
                current_face_vector = []
                for face_landmarks in results.multi_face_landmarks:
                    for landmark in face_landmarks.landmark:
                        current_face_vector.extend([landmark.x, landmark.y, landmark.z])
                
                # 데이터베이스 연결 부분
                conn = None
                try:
                    conn = mysql.connector.connect(
                        host="127.0.0.1",
                        user="famarket",
                        password="qpalzm1029!",
                        database="famarket"
                    )
                    cursor = conn.cursor()
                    cursor.execute("SELECT userid, username, uservector FROM datatbl WHERE uservector IS NOT NULL")
                    users = cursor.fetchall()
                    best_match, best_similarity = None, -1
                    
                    for userid, username, uservector_blob in users:
                        try:
                            stored_vectors = pickle.loads(uservector_blob)
                            for stored_vector in stored_vectors:
                                similarity = self.calculate_similarity(current_face_vector, stored_vector)
                                if similarity > best_similarity and similarity > 0.85:
                                    best_similarity = similarity
                                    best_match = (userid, username)
                        except Exception as e:
                            print(f"벡터 처리 오류: {str(e)}")
                            continue
                    
                    if best_match:
                        userid, username = best_match
                        self.status_label.setText(f"환영합니다, {username}님!")
                        QMessageBox.information(self, "성공", f"{username}님 환영합니다!")

                        # 입장 기록 저장
                        try:
                            if conn.is_connected():
                                cursor.execute("SELECT phonenum FROM usertbl WHERE userid = %s", (userid,))
                                result = cursor.fetchone()
                                if result:
                                    phonenum = result[0]
                                    cursor.execute("INSERT INTO entertbl (phonenum, enter_time) VALUES (%s,NOW())", (phonenum,))
                                    conn.commit()
                            self.timer.stop()
                            if self.cap:
                                self.cap.release()
                                self.cap = None
                            self.setup_main_ui()
                        except Exception as e:
                            print(f"입장 기록 저장 오류: {str(e)}")
                            QMessageBox.warning(self, "DB 오류", f"입장 기록 저장 중 오류 발생: {str(e)}")
                    else:
                        self.status_label.setText("인식 실패: 등록된 사용자를 찾을 수 없습니다.")
                        QMessageBox.warning(self, "실패", "등록된 얼굴과 일치하지 않습니다.")
                except Exception as e:
                    print(f"데이터베이스 오류: {str(e)}")
                    self.status_label.setText(f"데이터베이스 오류: {str(e)}")
                finally:
                    if cursor:
                        cursor.close()
                    if conn and conn.is_connected():
                        conn.close()
            else:
                self.status_label.setText("얼굴을 감지할 수 없습니다.")
                QMessageBox.warning(self, "경고", "카메라에 얼굴이 명확히 보이도록 해주세요.")
        except Exception as e:
            print(f"얼굴 인식 오류: {str(e)}")
            self.status_label.setText(f"얼굴 인식 중 오류 발생: {str(e)}")


    def calculate_similarity(self, vector1, vector2):
        min_len = min(len(vector1), len(vector2))
        v1 = np.array(vector1[:min_len])
        v2 = np.array(vector2[:min_len])
        dot = np.dot(v1, v2)
        norm1, norm2 = np.linalg.norm(v1), np.linalg.norm(v2)
        if norm1 == 0 or norm2 == 0:
            return 0
        return dot / (norm1 * norm2)

    def on_signup_success(self, user_id):
        QMessageBox.information(self, "회원가입 완료", "이제 얼굴을 등록해주세요.")
        self.start_face_registration(user_id)

if __name__ == "__main__":
    app = QApplication(sys.argv)
    window = FaceRecognitionApp()
    window.show()
    sys.exit(app.exec_())
