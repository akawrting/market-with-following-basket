# -*- coding: utf-8 -*-
import sys
import os
import cv2
import numpy as np
import mediapipe as mp
import pickle
import traceback
import hashlib
from PyQt5.QtWidgets import (QApplication, QMainWindow, QWidget, QPushButton, 
                            QVBoxLayout, QHBoxLayout, QLabel, QMessageBox,
                            QLineEdit, QDialog, QFormLayout, QDialogButtonBox,
                            QFrame, QSizePolicy, QSpacerItem)
from PyQt5.QtCore import Qt, QTimer, pyqtSlot, QUrl, QObject, QPropertyAnimation, QEasingCurve, QRect
from PyQt5.QtGui import QImage, QPixmap, QFont, QPalette, QColor, QLinearGradient, QPainter, QBrush
from PyQt5.QtWebEngineWidgets import QWebEngineView
from PyQt5.QtWebChannel import QWebChannel
import mysql.connector

class ModernButton(QPushButton):
    """현대적인 스타일의 버튼"""
    def __init__(self, text, primary=False):
        super().__init__(text)
        self.primary = primary
        self.setMinimumHeight(80)
        self.setMinimumWidth(300)
        self.setCursor(Qt.PointingHandCursor)
        
        if primary:
            self.setStyleSheet("""
                QPushButton {
                    background: qlineargradient(x1:0, y1:0, x2:0, y2:1,
                        stop:0 #4834d4, stop:1 #3742fa);
                    color: white;
                    border: none;
                    border-radius: 25px;
                    font-size: 22px;
                    font-weight: bold;
                    padding: 25px 40px;
                    text-align: center;
                }
                QPushButton:hover {
                    background: qlineargradient(x1:0, y1:0, x2:0, y2:1,
                        stop:0 #5742d4, stop:1 #4052fa);
                    transform: translateY(-3px);
                }
                QPushButton:pressed {
                    background: qlineargradient(x1:0, y1:0, x2:0, y2:1,
                        stop:0 #3424a4, stop:1 #2632da);
                    transform: translateY(-1px);
                }
            """)
        else:
            self.setStyleSheet("""
                QPushButton {
                    background: qlineargradient(x1:0, y1:0, x2:0, y2:1,
                        stop:0 #00d2d3, stop:1 #54a0ff);
                    color: white;
                    border: none;
                    border-radius: 25px;
                    font-size: 22px;
                    font-weight: bold;
                    padding: 25px 40px;
                    text-align: center;
                }
                QPushButton:hover {
                    background: qlineargradient(x1:0, y1:0, x2:0, y2:1,
                        stop:0 #10e2e3, stop:1 #64b0ff);
                    transform: translateY(-3px);
                }
                QPushButton:pressed {
                    background: qlineargradient(x1:0, y1:0, x2:0, y2:1,
                        stop:0 #00c2c3, stop:1 #4490ff);
                    transform: translateY(-1px);
                }
            """)

class SecondaryButton(QPushButton):
    """보조 버튼 스타일"""
    def __init__(self, text, color_theme="gray"):
        super().__init__(text)
        self.setMinimumHeight(60)
        self.setMinimumWidth(200)
        self.setCursor(Qt.PointingHandCursor)
        
        if color_theme == "success":
            bg_color = "#2ed573"
            hover_color = "#26c965"
        elif color_theme == "warning":
            bg_color = "#ffa502"
            hover_color = "#ff9500"
        elif color_theme == "danger":
            bg_color = "#ff3838"
            hover_color = "#ff2020"
        else:  # gray
            bg_color = "#7f8fa6"
            hover_color = "#6c7b8a"
        
        self.setStyleSheet(f"""
            QPushButton {{
                background-color: {bg_color};
                color: white;
                border: none;
                border-radius: 15px;
                font-size: 18px;
                font-weight: 600;
                padding: 18px 30px;
            }}
            QPushButton:hover {{
                background-color: {hover_color};
                transform: translateY(-2px);
            }}
            QPushButton:pressed {{
                transform: translateY(0px);
            }}
        """)

class LoginDialog(QDialog):
    """모던한 로그인 다이얼로그"""
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setWindowTitle("로그인")
        self.setModal(True)
        self.setFixedSize(450, 300)
        self.setStyleSheet("""
            QDialog {
                background: qlineargradient(x1:0, y1:0, x2:1, y2:1,
                    stop:0 #667eea, stop:1 #764ba2);
                border-radius: 20px;
            }
            QLabel {
                color: white;
                font-size: 16px;
                font-weight: bold;
            }
            QLineEdit {
                background-color: rgba(255, 255, 255, 0.9);
                border: 2px solid transparent;
                border-radius: 10px;
                padding: 15px;
                font-size: 16px;
                color: #2c3e50;
            }
            QLineEdit:focus {
                border-color: #3498db;
                background-color: white;
            }
        """)
        
        layout = QVBoxLayout()
        layout.setSpacing(20)
        layout.setContentsMargins(40, 40, 40, 40)
        
        # 타이틀
        title = QLabel("로그인")
        title.setAlignment(Qt.AlignCenter)
        title.setStyleSheet("font-size: 28px; font-weight: bold; color: white; margin-bottom: 20px;")
        layout.addWidget(title)
        
        # 입력 필드들
        self.userid_input = QLineEdit()
        self.userid_input.setPlaceholderText("사용자 ID를 입력하세요")
        layout.addWidget(self.userid_input)
        
        self.password_input = QLineEdit()
        self.password_input.setEchoMode(QLineEdit.Password)
        self.password_input.setPlaceholderText("비밀번호를 입력하세요")
        layout.addWidget(self.password_input)
        
        # 버튼들
        button_layout = QHBoxLayout()
        
        ok_button = SecondaryButton("로그인", "success")
        ok_button.clicked.connect(self.accept)
        button_layout.addWidget(ok_button)
        
        cancel_button = SecondaryButton("취소", "gray")
        cancel_button.clicked.connect(self.reject)
        button_layout.addWidget(cancel_button)
        
        layout.addLayout(button_layout)
        self.setLayout(layout)
        
        # Enter 키로 로그인 가능하도록
        self.password_input.returnPressed.connect(self.accept)
        
    def get_credentials(self):
        return self.userid_input.text().strip(), self.password_input.text().strip()

class Bridge(QObject):
    def __init__(self, callback):
        super().__init__()
        self.callback = callback

    @pyqtSlot(str)
    def onFormSubmitted(self, user_id):
        print("받은 user_id:", user_id, type(user_id))
        self.callback(user_id)

class FaceRecognitionApp(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("FaceAuth Pro - 얼굴인식 출입 시스템")
        self.showMaximized()  # 전체화면으로 시작
        
        # 앱 전체 스타일 설정
        self.setStyleSheet("""
            QMainWindow {
                background: qlineargradient(x1:0, y1:0, x2:1, y2:1,
                    stop:0 #a8edea, stop:1 #fed6e3);
            }
            QLabel {
                color: #2c3e50;
            }
        """)

        # MediaPipe 초기화
        self.mp_face_mesh = mp.solutions.face_mesh
        self.face_mesh = self.mp_face_mesh.FaceMesh(
            static_image_mode=False,
            max_num_faces=1,
            min_detection_confidence=0.5,
            min_tracking_confidence=0.5
        )

        # UI 초기화
        self.central_widget = QWidget()
        self.setCentralWidget(self.central_widget)
        self.main_layout = QVBoxLayout(self.central_widget)
        self.main_layout.setContentsMargins(50, 50, 50, 50)
        self.main_layout.setSpacing(30)

        # 카메라 및 타이머
        self.cap = None
        self.timer = QTimer()
        self.timer.timeout.connect(self.update_frame)

        # 상태 변수들
        self.current_mode = "main"
        self.face_vectors = []
        self.current_user_id = None
        self.face_recognition_attempts = 0  # 얼굴 인식 시도 횟수
        self.max_attempts = 3  # 최대 시도 횟수
        self.last_recognized_user = None  # 마지막 인식된 사용자 정보

        self.setup_main_ui()

    def hash_password(self, password):
        """비밀번호 해싱"""
        return hashlib.sha256(password.encode()).hexdigest()

    def setup_main_ui(self):
        """메인 UI 설정"""
        self.clear_layout()
        self.current_mode = "main"
        self.face_recognition_attempts = 0  # 리셋
        self.last_recognized_user = None

        # 메인 컨테이너
        main_container = QWidget()
        main_container.setMaximumWidth(1200)
        main_container.setMinimumHeight(800)
        container_layout = QVBoxLayout(main_container)
        container_layout.setSpacing(50)
        container_layout.setContentsMargins(60, 60, 60, 60)

        # 타이틀 섹션
        title_frame = QFrame()
        title_frame.setStyleSheet("""
            QFrame {
                background: rgba(255, 255, 255, 0.1);
                border-radius: 30px;
                border: 2px solid rgba(255, 255, 255, 0.2);
            }
        """)
        title_layout = QVBoxLayout(title_frame)
        title_layout.setContentsMargins(40, 40, 40, 40)

        # 메인 타이틀
        title_label = QLabel("🔐 FaceAuth Pro")
        title_label.setAlignment(Qt.AlignCenter)
        title_label.setStyleSheet("""
            font-size: 48px; 
            font-weight: bold; 
            color: #2c3e50;
            margin: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        """)
        title_layout.addWidget(title_label)

        # 서브 타이틀
        subtitle_label = QLabel("첨단 얼굴인식 기술로 안전하고 편리한 출입관리")
        subtitle_label.setAlignment(Qt.AlignCenter)
        subtitle_label.setStyleSheet("""
            font-size: 24px; 
            color: #7f8c8d;
            margin-bottom: 20px;
            font-weight: 300;
        """)
        title_layout.addWidget(subtitle_label)

        container_layout.addWidget(title_frame)

        # 버튼 섹션
        button_frame = QFrame()
        button_frame.setStyleSheet("""
            QFrame {
                background: rgba(255, 255, 255, 0.15);
                border-radius: 30px;
                border: 2px solid rgba(255, 255, 255, 0.3);
            }
        """)
        button_layout = QVBoxLayout(button_frame)
        button_layout.setContentsMargins(60, 60, 60, 60)
        button_layout.setSpacing(40)

        # 회원가입 버튼
        self.register_button = ModernButton("🆕 처음 오셨나요? (회원가입)", False)
        self.register_button.clicked.connect(self.show_signup_page)
        button_layout.addWidget(self.register_button, alignment=Qt.AlignCenter)

        # 입장하기 버튼
        self.enter_button = ModernButton("🚪 입장하기 (얼굴인식)", True)
        self.enter_button.clicked.connect(self.start_face_recognition)
        button_layout.addWidget(self.enter_button, alignment=Qt.AlignCenter)

        container_layout.addWidget(button_frame)

        # 중앙 정렬
        main_layout_wrapper = QHBoxLayout()
        main_layout_wrapper.addStretch()
        main_layout_wrapper.addWidget(main_container)
        main_layout_wrapper.addStretch()

        self.main_layout.addStretch()
        self.main_layout.addLayout(main_layout_wrapper)
        self.main_layout.addStretch()

    def clear_layout(self):
        """레이아웃 정리"""
        for i in reversed(range(self.main_layout.count())):
            child = self.main_layout.itemAt(i).widget()
            if child:
                child.setParent(None)

    def show_signup_page(self):
        """회원가입 페이지 표시"""
        self.clear_layout()

        # 뒤로가기 버튼 먼저 추가
        back_button = SecondaryButton("← 뒤로가기", "gray")
        back_button.clicked.connect(self.setup_main_ui)
        
        button_container = QHBoxLayout()
        button_container.addWidget(back_button)
        button_container.addStretch()
        self.main_layout.addLayout(button_container)

        # 웹뷰
        self.web_view = QWebEngineView()
        self.web_view.setStyleSheet("""
            QWebEngineView {
                border: 3px solid rgba(255, 255, 255, 0.3);
                border-radius: 15px;
                background: white;
            }
        """)
        
        self.channel = QWebChannel()
        self.bridge = Bridge(self.on_signup_success)
        self.channel.registerObject("bridge", self.bridge)
        self.web_view.page().setWebChannel(self.channel)
        self.web_view.load(QUrl("http://localhost:8080/signup.php"))

        self.main_layout.addWidget(self.web_view)
        self.current_mode = "signup"

    def start_face_registration(self, user_id):
        """얼굴 등록 시작"""
        self.clear_layout()
        self.current_user_id = user_id
        self.current_mode = "face_registration"
        self.face_vectors = []

        # 컨테이너
        container = QWidget()
        container.setMaximumWidth(1000)
        layout = QVBoxLayout(container)
        layout.setSpacing(30)
        layout.setContentsMargins(40, 40, 40, 40)

        # 헤더
        header_frame = QFrame()
        header_frame.setStyleSheet("""
            QFrame {
                background: rgba(255, 255, 255, 0.2);
                border-radius: 20px;
                border: 2px solid rgba(255, 255, 255, 0.3);
            }
        """)
        header_layout = QVBoxLayout(header_frame)
        header_layout.setContentsMargins(30, 30, 30, 30)

        title_label = QLabel("👤 얼굴 등록")
        title_label.setAlignment(Qt.AlignCenter)
        title_label.setStyleSheet("font-size: 36px; font-weight: bold; color: #2c3e50; margin-bottom: 10px;")
        header_layout.addWidget(title_label)

        instruction_label = QLabel("정면, 좌측, 우측 얼굴을 차례로 촬영합니다.\n각 방향마다 명확하게 얼굴이 보이도록 해주세요.")
        instruction_label.setAlignment(Qt.AlignCenter)
        instruction_label.setStyleSheet("font-size: 20px; color: #7f8c8d; line-height: 1.5;")
        header_layout.addWidget(instruction_label)

        layout.addWidget(header_frame)

        # 카메라 영역
        camera_frame = QFrame()
        camera_frame.setStyleSheet("""
            QFrame {
                background: rgba(0, 0, 0, 0.1);
                border: 3px solid #3498db;
                border-radius: 20px;
            }
        """)
        camera_layout = QVBoxLayout(camera_frame)
        camera_layout.setContentsMargins(20, 20, 20, 20)

        self.camera_label = QLabel()
        self.camera_label.setAlignment(Qt.AlignCenter)
        self.camera_label.setMinimumSize(800, 600)
        self.camera_label.setStyleSheet("background: black; border-radius: 15px;")
        camera_layout.addWidget(self.camera_label)

        layout.addWidget(camera_frame)

        # 상태 표시
        self.status_label = QLabel("📷 정면을 바라봐주세요")
        self.status_label.setAlignment(Qt.AlignCenter)
        self.status_label.setStyleSheet("""
            font-size: 24px; 
            font-weight: bold; 
            color: #3498db; 
            margin: 20px;
            padding: 15px;
            background: rgba(52, 152, 219, 0.1);
            border-radius: 15px;
        """)
        layout.addWidget(self.status_label)

        # 버튼들
        button_layout = QHBoxLayout()
        button_layout.setSpacing(20)

        self.capture_button = SecondaryButton("📸 촬영하기", "danger")
        self.capture_button.clicked.connect(self.capture_face)
        button_layout.addWidget(self.capture_button)

        cancel_button = SecondaryButton("❌ 취소", "gray")
        cancel_button.clicked.connect(self.setup_main_ui)
        button_layout.addWidget(cancel_button)

        layout.addLayout(button_layout)

        # 중앙 정렬
        main_wrapper = QHBoxLayout()
        main_wrapper.addStretch()
        main_wrapper.addWidget(container)
        main_wrapper.addStretch()

        self.main_layout.addLayout(main_wrapper)
        self.start_camera()

    def start_camera(self):
        """카메라 시작"""
        if self.cap is None:
            self.cap = cv2.VideoCapture(0)

        if not self.cap.isOpened():
            QMessageBox.critical(self, "오류", "카메라를 열 수 없습니다.")
            self.setup_main_ui()
            return

        self.timer.start(30)

    def stop_camera(self):
        """카메라 정지"""
        if self.timer.isActive():
            self.timer.stop()
        if self.cap and self.cap.isOpened():
            self.cap.release()
            self.cap = None

    def update_frame(self):
        """프레임 업데이트"""
        if not self.cap or not self.cap.isOpened():
            return
            
        ret, frame = self.cap.read()
        if not ret:
            return

        frame = cv2.flip(frame, 1)
        
        # 얼굴 인식 모드일 때 얼굴 메시 그리기
        if self.current_mode in ["face_registration", "face_recognition"]:
            frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
            results = self.face_mesh.process(frame_rgb)
            if results.multi_face_landmarks:
                for face_landmarks in results.multi_face_landmarks:
                    mp.solutions.drawing_utils.draw_landmarks(
                        frame, face_landmarks, self.mp_face_mesh.FACEMESH_CONTOURS,
                        landmark_drawing_spec=mp.solutions.drawing_utils.DrawingSpec(
                            color=(0, 255, 0), thickness=1, circle_radius=1))

        # Qt 이미지로 변환
        h, w, ch = frame.shape
        bytes_per_line = ch * w
        convert_to_qt_format = QImage(frame.data, w, h, bytes_per_line, QImage.Format_RGB888)
        
        # 카메라 레이블 크기에 맞게 조정
        if hasattr(self, 'camera_label'):
            label_size = self.camera_label.size()
            qt_frame = convert_to_qt_format.rgbSwapped().scaled(
                label_size, Qt.KeepAspectRatio, Qt.SmoothTransformation)
            self.camera_label.setPixmap(QPixmap.fromImage(qt_frame))

    def capture_face(self):
        """얼굴 캡처"""
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
                self.status_label.setText("👈 좌측을 바라봐주세요")
            elif len(self.face_vectors) == 2:
                self.status_label.setText("👉 우측을 바라봐주세요")
            elif len(self.face_vectors) == 3:
                self.status_label.setText("✅ 등록 중...")
                self.save_face_vectors()
        else:
            QMessageBox.warning(self, "경고", "얼굴을 감지할 수 없습니다. 다시 시도해주세요.")

    def save_face_vectors(self):
        """얼굴 벡터 저장"""
        self.stop_camera()
        
        try:
            face_data = pickle.dumps(self.face_vectors)
            conn = mysql.connector.connect(
                host="127.0.0.1",
                user="famarket",
                password="qpalzm1029!",
                database="famarket"
            )
            cursor = conn.cursor()
            cursor.execute("UPDATE datatbl SET uservector = %s WHERE userid = %s", 
                         (face_data, self.current_user_id))
            conn.commit()
            cursor.close()
            conn.close()
            
            QMessageBox.information(self, "성공", "✅ 얼굴 등록이 완료되었습니다!")
        except Exception as e:
            print(traceback.format_exc())
            QMessageBox.critical(self, "오류", f"얼굴 데이터 저장 오류: {str(e)}")
        
        self.setup_main_ui()

    def start_face_recognition(self):
        """얼굴 인식 시작"""
        self.clear_layout()
        self.current_mode = "face_recognition"
        self.face_recognition_attempts = 0  # 시작할 때 리셋

        # 컨테이너
        container = QWidget()
        container.setMaximumWidth(1000)
        layout = QVBoxLayout(container)
        layout.setSpacing(30)
        layout.setContentsMargins(40, 40, 40, 40)

        # 헤더
        header_frame = QFrame()
        header_frame.setStyleSheet("""
            QFrame {
                background: rgba(255, 255, 255, 0.2);
                border-radius: 20px;
                border: 2px solid rgba(255, 255, 255, 0.3);
            }
        """)
        header_layout = QVBoxLayout(header_frame)
        header_layout.setContentsMargins(30, 30, 30, 30)

        title_label = QLabel("🔍 얼굴 인식")
        title_label.setAlignment(Qt.AlignCenter)
        title_label.setStyleSheet("font-size: 36px; font-weight: bold; color: #2c3e50; margin-bottom: 10px;")
        header_layout.addWidget(title_label)

        instruction_label = QLabel("카메라를 정면으로 바라보고 '인식하기' 버튼을 눌러주세요.\n밝은 곳에서 얼굴이 명확히 보이도록 해주세요.")
        instruction_label.setAlignment(Qt.AlignCenter)
        instruction_label.setStyleSheet("font-size: 20px; color: #7f8c8d; line-height: 1.5;")
        header_layout.addWidget(instruction_label)

        layout.addWidget(header_frame)

        # 카메라 영역
        camera_frame = QFrame()
        camera_frame.setStyleSheet("""
            QFrame {
                background: rgba(0, 0, 0, 0.1);
                border: 3px solid #e74c3c;
                border-radius: 20px;
            }
        """)
        camera_layout = QVBoxLayout(camera_frame)
        camera_layout.setContentsMargins(20, 20, 20, 20)

        self.camera_label = QLabel()
        self.camera_label.setAlignment(Qt.AlignCenter)
        self.camera_label.setMinimumSize(800, 600)
        self.camera_label.setStyleSheet("background: black; border-radius: 15px;")
        camera_layout.addWidget(self.camera_label)

        layout.addWidget(camera_frame)

        # 상태 표시 (시도 횟수 포함)
        self.status_label = QLabel(f"👁️ 얼굴을 인식하는 중... (시도: {self.face_recognition_attempts + 1}/{self.max_attempts})")
        self.status_label.setAlignment(Qt.AlignCenter)
        self.status_label.setStyleSheet("""
            font-size: 24px; 
            font-weight: bold; 
            color: #e74c3c; 
            margin: 20px;
            padding: 15px;
            background: rgba(231, 76, 60, 0.1);
            border-radius: 15px;
        """)
        layout.addWidget(self.status_label)

        # 버튼들
        button_layout = QHBoxLayout()
        button_layout.setSpacing(20)

        self.recognize_button = SecondaryButton("🔍 인식하기", "danger")
        self.recognize_button.clicked.connect(self.recognize_face)
        button_layout.addWidget(self.recognize_button)

        # ID/비밀번호 로그인 버튼 (처음엔 숨김)
        self.login_button = SecondaryButton("🔑 ID/비밀번호로 로그인", "warning")
        self.login_button.clicked.connect(self.show_manual_login)
        self.login_button.setVisible(False)  # 처음엔 숨김
        button_layout.addWidget(self.login_button)

        cancel_button = SecondaryButton("❌ 취소", "gray")
        cancel_button.clicked.connect(self.setup_main_ui)
        button_layout.addWidget(cancel_button)

        layout.addLayout(button_layout)

        # 중앙 정렬
        main_wrapper = QHBoxLayout()
        main_wrapper.addStretch()
        main_wrapper.addWidget(container)
        main_wrapper.addStretch()

        self.main_layout.addLayout(main_wrapper)
        self.start_camera()
    def recognize_face(self):
        """얼굴 인식 수행"""
        if not self.cap or not self.cap.isOpened():
            return
            
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
            
            # 데이터베이스에서 등록된 얼굴들과 비교
            try:
                conn = mysql.connector.connect(
                    host="127.0.0.1",
                    user="famarket",
                    password="qpalzm1029!",
                    database="famarket"
                )
                cursor = conn.cursor()
                cursor.execute("SELECT userid, uservector FROM datatbl WHERE uservector IS NOT NULL")
                users = cursor.fetchall()
                cursor.close()
                conn.close()
                
                best_match = None
                best_similarity = 0
                
                for userid, face_data in users:
                    try:
                        stored_vectors = pickle.loads(face_data)
                        for stored_vector in stored_vectors:
                            similarity = self.calculate_similarity(current_face_vector, stored_vector)
                            if similarity > best_similarity:
                                best_similarity = similarity
                                best_match = userid
                    except Exception as e:
                        continue
                
                # 임계값 설정 (85% 이상 유사도)
                if best_similarity > 0.85:
                    self.last_recognized_user = best_match
                    self.show_success_message(best_match)
                    return
                else:
                    self.face_recognition_attempts += 1
                    if self.face_recognition_attempts >= self.max_attempts:
                        self.status_label.setText("❌ 인식 실패! ID/비밀번호로 로그인하세요.")
                        self.status_label.setStyleSheet("""
                            font-size: 24px; 
                            font-weight: bold; 
                            color: #e74c3c; 
                            margin: 20px;
                            padding: 15px;
                            background: rgba(231, 76, 60, 0.2);
                            border-radius: 15px;
                        """)
                        self.login_button.setVisible(True)  # 로그인 버튼 표시
                        self.recognize_button.setEnabled(False)  # 인식 버튼 비활성화
                    else:
                        self.status_label.setText(f"❌ 인식 실패! 다시 시도하세요. (시도: {self.face_recognition_attempts}/{self.max_attempts})")
                        
            except Exception as e:
                print(traceback.format_exc())
                QMessageBox.critical(self, "오류", f"얼굴 인식 오류: {str(e)}")
                
        else:
            self.face_recognition_attempts += 1
            if self.face_recognition_attempts >= self.max_attempts:
                self.status_label.setText("❌ 얼굴을 감지할 수 없습니다! ID/비밀번호로 로그인하세요.")
                self.login_button.setVisible(True)
                self.recognize_button.setEnabled(False)
            else:
                self.status_label.setText(f"⚠️ 얼굴을 감지할 수 없습니다. 다시 시도하세요. (시도: {self.face_recognition_attempts}/{self.max_attempts})")

    def show_manual_login(self):
        """수동 로그인 다이얼로그 표시"""
        dialog = LoginDialog(self)
        if dialog.exec_() == QDialog.Accepted:
            userid, password = dialog.get_credentials()
            if userid and password:
                self.verify_manual_login(userid, password)

    def verify_manual_login(self, userid, password):
        """수동 로그인 검증"""
        try:
            conn = mysql.connector.connect(
                host="127.0.0.1",
                user="famarket",
                password="qpalzm1029!",
                database="famarket"
            )
            cursor = conn.cursor()
            hashed_password = self.hash_password(password)
            cursor.execute("SELECT userid FROM datatbl WHERE userid = %s AND userpasswd = %s", 
                         (userid, hashed_password))
            result = cursor.fetchone()
            cursor.close()
            conn.close()
            
            if result:
                self.last_recognized_user = userid
                self.show_success_message(userid)
            else:
                QMessageBox.warning(self, "로그인 실패", "아이디 또는 비밀번호가 잘못되었습니다.")
                
        except Exception as e:
            print(traceback.format_exc())
            QMessageBox.critical(self, "오류", f"로그인 오류: {str(e)}")

    def calculate_similarity(self, vector1, vector2):
        """두 벡터 간의 유사도 계산 (코사인 유사도)"""
        try:
            vector1 = np.array(vector1)
            vector2 = np.array(vector2)
            
            if len(vector1) != len(vector2):
                return 0
            
            dot_product = np.dot(vector1, vector2)
            norm1 = np.linalg.norm(vector1)
            norm2 = np.linalg.norm(vector2)
            
            if norm1 == 0 or norm2 == 0:
                return 0
                
            return dot_product / (norm1 * norm2)
        except:
            return 0

    def show_success_message(self, userid):
        """성공 메시지 표시"""
        self.stop_camera()
        self.clear_layout()
        
        # 성공 페이지 컨테이너
        container = QWidget()
        container.setMaximumWidth(800)
        layout = QVBoxLayout(container)
        layout.setSpacing(40)
        layout.setContentsMargins(60, 60, 60, 60)

        # 성공 아이콘과 메시지
        success_frame = QFrame()
        success_frame.setStyleSheet("""
            QFrame {
                background: qlineargradient(x1:0, y1:0, x2:0, y2:1,
                    stop:0 #2ed573, stop:1 #26c965);
                border-radius: 30px;
                border: 3px solid #1dd65a;
            }
        """)
        success_layout = QVBoxLayout(success_frame)
        success_layout.setContentsMargins(50, 50, 50, 50)
        success_layout.setSpacing(30)

        # 성공 아이콘
        success_icon = QLabel("✅")
        success_icon.setAlignment(Qt.AlignCenter)
        success_icon.setStyleSheet("font-size: 120px; color: white;")
        success_layout.addWidget(success_icon)

        # 성공 메시지
        success_title = QLabel("인증 성공!")
        success_title.setAlignment(Qt.AlignCenter)
        success_title.setStyleSheet("""
            font-size: 42px; 
            font-weight: bold; 
            color: white;
            margin-bottom: 10px;
        """)
        success_layout.addWidget(success_title)

        # 사용자 정보
        user_info = QLabel(f"환영합니다, {userid}님!")
        user_info.setAlignment(Qt.AlignCenter)
        user_info.setStyleSheet("""
            font-size: 28px; 
            color: white;
            font-weight: 600;
            margin-bottom: 20px;
        """)
        success_layout.addWidget(user_info)

        # 입장 시간
        import datetime
        current_time = datetime.datetime.now().strftime("%Y년 %m월 %d일 %H:%M:%S")
        time_label = QLabel(f"입장 시간: {current_time}")
        time_label.setAlignment(Qt.AlignCenter)
        time_label.setStyleSheet("""
            font-size: 20px; 
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        """)
        success_layout.addWidget(time_label)

        layout.addWidget(success_frame)

        # 확인 버튼
        confirm_button = ModernButton("확인", True)
        confirm_button.clicked.connect(self.setup_main_ui)
        layout.addWidget(confirm_button, alignment=Qt.AlignCenter)

        # 중앙 정렬
        main_wrapper = QHBoxLayout()
        main_wrapper.addStretch()
        main_wrapper.addWidget(container)
        main_wrapper.addStretch()

        self.main_layout.addStretch()
        self.main_layout.addLayout(main_wrapper)
        self.main_layout.addStretch()

        # 입장 기록을 데이터베이스에 저장
        self.save_entry_log(userid)

    def save_entry_log(self, userid):
        """입장 기록 저장"""
        try:
            conn = mysql.connector.connect(
                host="127.0.0.1",
                user="famarket",
                password="qpalzm1029!",
                database="famarket"
            )
            cursor = conn.cursor()
            
            # 입장 기록 테이블이 없다면 생성
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS entry_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    userid VARCHAR(50) NOT NULL,
                    entry_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    method VARCHAR(20) DEFAULT 'face_recognition'
                )
            """)
            
            # 입장 기록 저장
            cursor.execute("INSERT INTO entry_logs (userid, method) VALUES (%s, %s)", 
                         (userid, 'face_recognition' if self.last_recognized_user else 'manual'))
            
            conn.commit()
            cursor.close()
            conn.close()
        except Exception as e:
            print(f"입장 기록 저장 오류: {e}")

    def on_signup_success(self, user_id):
        """회원가입 성공 후 처리"""
        print(f"회원가입 성공: {user_id}")
        
        # 성공 메시지 표시
        reply = QMessageBox.question(self, "회원가입 성공", 
                                   f"{user_id}님의 회원가입이 완료되었습니다!\n\n지금 얼굴을 등록하시겠습니까?",
                                   QMessageBox.Yes | QMessageBox.No)
        
        if reply == QMessageBox.Yes:
            self.start_face_registration(user_id)
        else:
            self.setup_main_ui()

    def closeEvent(self, event):
        """애플리케이션 종료 시 정리"""
        self.stop_camera()
        event.accept()

def main():
    app = QApplication(sys.argv)
    
    # 애플리케이션 스타일 설정
    app.setStyle('Fusion')
    palette = QPalette()
    palette.setColor(QPalette.Window, QColor(240, 240, 240))
    palette.setColor(QPalette.WindowText, QColor(44, 62, 80))
    app.setPalette(palette)
    
    # 폰트 설정
    font = QFont("맑은 고딕", 12)
    app.setFont(font)
    
    window = FaceRecognitionApp()
    window.show()
    
    sys.exit(app.exec_())

if __name__ == "__main__":
    main()
