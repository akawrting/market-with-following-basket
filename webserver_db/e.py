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
                            QLineEdit, QDialog, QFormLayout, QDialogButtonBox)
from PyQt5.QtCore import Qt, QTimer, pyqtSlot, QUrl, QObject
from PyQt5.QtGui import QImage, QPixmap, QFont
from PyQt5.QtWebEngineWidgets import QWebEngineView
from PyQt5.QtWebChannel import QWebChannel
import mysql.connector

class LoginDialog(QDialog):
    """ID/Password 로그인 다이얼로그"""
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setWindowTitle("로그인")
        self.setModal(True)
        self.setFixedSize(300, 150)
        
        layout = QFormLayout()
        
        self.userid_input = QLineEdit()
        self.userid_input.setPlaceholderText("사용자 ID를 입력하세요")
        layout.addRow("ID:", self.userid_input)
        
        self.password_input = QLineEdit()
        self.password_input.setEchoMode(QLineEdit.Password)
        self.password_input.setPlaceholderText("비밀번호를 입력하세요")
        layout.addRow("Password:", self.password_input)
        
        self.buttons = QDialogButtonBox(QDialogButtonBox.Ok | QDialogButtonBox.Cancel)
        self.buttons.accepted.connect(self.accept)
        self.buttons.rejected.connect(self.reject)
        layout.addWidget(self.buttons)
        
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
        self.setWindowTitle("얼굴인식 출입 시스템")
        self.setGeometry(100, 100, 800, 600)

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
        self.face_recognition_attempts = 0
        self.last_recognized_user = None

        # 타이틀
        title_label = QLabel("얼굴인식 출입 시스템")
        title_label.setAlignment(Qt.AlignCenter)
        title_label.setStyleSheet("font-size: 28px; font-weight: bold; margin: 30px; color: #2c3e50;")
        self.main_layout.addWidget(title_label)

        # 버튼 컨테이너
        button_container = QWidget()
        button_layout = QVBoxLayout(button_container)

        # 회원가입 버튼
        self.register_button = QPushButton("처음 오셨나요? (회원가입)")
        self.register_button.setMinimumHeight(70)
        self.register_button.setStyleSheet("""
            QPushButton {
                background-color: #27ae60;
                color: white;
                border-radius: 15px;
                font-size: 18px;
                font-weight: bold;
                padding: 15px;
                border: none;
            }
            QPushButton:hover {
                background-color: #219a52;
                transform: translateY(-2px);
            }
            QPushButton:pressed {
                background-color: #1e8449;
                transform: translateY(0px);
            }
        """)
        self.register_button.clicked.connect(self.show_signup_page)
        button_layout.addWidget(self.register_button)

        button_layout.addSpacing(30)

        # 입장하기 버튼
        self.enter_button = QPushButton("입장하기 (얼굴인식)")
        self.enter_button.setMinimumHeight(70)
        self.enter_button.setStyleSheet("""
            QPushButton {
                background-color: #3498db;
                color: white;
                border-radius: 15px;
                font-size: 18px;
                font-weight: bold;
                padding: 15px;
                border: none;
            }
            QPushButton:hover {
                background-color: #2980b9;
                transform: translateY(-2px);
            }
            QPushButton:pressed {
                background-color: #21618c;
                transform: translateY(0px);
            }
        """)
        self.enter_button.clicked.connect(self.start_face_recognition)
        button_layout.addWidget(self.enter_button)

        button_container.setContentsMargins(100, 50, 100, 50)
        self.main_layout.addWidget(button_container)

    def clear_layout(self):
        """레이아웃 정리"""
        for i in reversed(range(self.main_layout.count())):
            child = self.main_layout.itemAt(i).widget()
            if child:
                child.setParent(None)

    def show_signup_page(self):
        """회원가입 페이지 표시"""
        self.clear_layout()

        self.web_view = QWebEngineView()
        self.channel = QWebChannel()
        self.bridge = Bridge(self.on_signup_success)
        self.channel.registerObject("bridge", self.bridge)
        self.web_view.page().setWebChannel(self.channel)

        self.web_view.load(QUrl("http://localhost:8080/signup.php"))

        back_button = QPushButton("뒤로가기")
        back_button.clicked.connect(self.setup_main_ui)
        back_button.setStyleSheet("""
            QPushButton {
                background-color: #95a5a6;
                color: white;
                border-radius: 8px;
                padding: 10px 20px;
                font-size: 14px;
                border: none;
            }
            QPushButton:hover {
                background-color: #7f8c8d;
            }
        """)

        self.main_layout.addWidget(self.web_view)
        self.main_layout.addWidget(back_button)
        self.current_mode = "signup"

    def start_face_registration(self, user_id):
        """얼굴 등록 시작"""
        self.clear_layout()
        self.current_user_id = user_id
        self.current_mode = "face_registration"
        self.face_vectors = []

        # 안내문
        instruction_label = QLabel("얼굴 등록을 시작합니다.\n정면, 좌측, 우측 얼굴을 차례로 촬영합니다.")
        instruction_label.setAlignment(Qt.AlignCenter)
        instruction_label.setStyleSheet("font-size: 18px; margin: 20px; color: #2c3e50;")
        self.main_layout.addWidget(instruction_label)

        # 카메라 화면
        self.camera_label = QLabel()
        self.camera_label.setAlignment(Qt.AlignCenter)
        self.camera_label.setMinimumSize(640, 480)
        self.camera_label.setStyleSheet("border: 2px solid #bdc3c7; border-radius: 10px;")
        self.main_layout.addWidget(self.camera_label)

        # 상태 표시
        self.status_label = QLabel("정면을 바라봐주세요")
        self.status_label.setAlignment(Qt.AlignCenter)
        self.status_label.setStyleSheet("font-size: 20px; font-weight: bold; color: #3498db; margin: 10px;")
        self.main_layout.addWidget(self.status_label)

        # 버튼들
        button_container = QWidget()
        button_layout = QHBoxLayout(button_container)

        self.capture_button = QPushButton("촬영하기")
        self.capture_button.setStyleSheet("""
            QPushButton {
                background-color: #e74c3c;
                color: white;
                border-radius: 10px;
                padding: 12px 24px;
                font-size: 16px;
                font-weight: bold;
                border: none;
            }
            QPushButton:hover {
                background-color: #c0392b;
            }
        """)
        self.capture_button.clicked.connect(self.capture_face)
        button_layout.addWidget(self.capture_button)

        cancel_button = QPushButton("취소")
        cancel_button.setStyleSheet("""
            QPushButton {
                background-color: #95a5a6;
                color: white;
                border-radius: 10px;
                padding: 12px 24px;
                font-size: 16px;
                border: none;
            }
            QPushButton:hover {
                background-color: #7f8c8d;
            }
        """)
        cancel_button.clicked.connect(self.setup_main_ui)
        button_layout.addWidget(cancel_button)

        self.main_layout.addWidget(button_container)
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
        qt_frame = convert_to_qt_format.rgbSwapped().scaled(640, 480, Qt.KeepAspectRatio)
        
        if hasattr(self, 'camera_label'):
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
                self.status_label.setText("좌측을 바라봐주세요")
            elif len(self.face_vectors) == 2:
                self.status_label.setText("우측을 바라봐주세요")
            elif len(self.face_vectors) == 3:
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
            
            QMessageBox.information(self, "성공", "얼굴 등록이 완료되었습니다!")
        except Exception as e:
            print(traceback.format_exc())
            QMessageBox.critical(self, "오류", f"얼굴 데이터 저장 오류: {str(e)}")
        
        self.setup_main_ui()

    def start_face_recognition(self):
        """얼굴 인식 시작"""
        self.clear_layout()
        self.current_mode = "face_recognition"
        self.face_recognition_attempts = 0

        # 안내문
        instruction_label = QLabel("얼굴 인식을 시작합니다.\n카메라를 정면으로 바라봐주세요.")
        instruction_label.setAlignment(Qt.AlignCenter)
        instruction_label.setStyleSheet("font-size: 18px; margin: 20px; color: #2c3e50;")
        self.main_layout.addWidget(instruction_label)

        # 카메라 화면
        self.camera_label = QLabel()
        self.camera_label.setAlignment(Qt.AlignCenter)
        self.camera_label.setMinimumSize(640, 480)
        self.camera_label.setStyleSheet("border: 2px solid #bdc3c7; border-radius: 10px;")
        self.main_layout.addWidget(self.camera_label)

        # 상태 표시 (시도 횟수 포함)
        self.status_label = QLabel(f"얼굴을 인식하는 중... (시도: {self.face_recognition_attempts + 1}/{self.max_attempts})")
        self.status_label.setAlignment(Qt.AlignCenter)
        self.status_label.setStyleSheet("font-size: 18px; font-weight: bold; color: #3498db; margin: 10px;")
        self.main_layout.addWidget(self.status_label)

        # 버튼들
        button_container = QWidget()
        button_layout = QHBoxLayout(button_container)

        self.recognize_button = QPushButton("인식하기")
        self.recognize_button.setStyleSheet("""
            QPushButton {
                background-color: #e74c3c;
                color: white;
                border-radius: 10px;
                padding: 12px 24px;
                font-size: 16px;
                font-weight: bold;
                border: none;
            }
            QPushButton:hover {
                background-color: #c0392b;
            }
        """)
        self.recognize_button.clicked.connect(self.recognize_face)
        button_layout.addWidget(self.recognize_button)

        # ID/비밀번호 로그인 버튼 (처음엔 숨김)
        self.login_button = QPushButton("ID/비밀번호로 로그인")
        self.login_button.setStyleSheet("""
            QPushButton {
                background-color: #f39c12;
                color: white;
                border-radius: 10px;
                padding: 12px 24px;
                font-size: 16px;
                font-weight: bold;
                border: none;
            }
            QPushButton:hover {
                background-color: #e67e22;
            }
        """)
        self.login_button.clicked.connect(self.show_manual_login)
        self.login_button.setVisible(False)  # 처음엔 숨김
        button_layout.addWidget(self.login_button)

        cancel_button = QPushButton("취소")
        cancel_button.setStyleSheet("""
            QPushButton {
                background-color: #95a5a6;
                color: white;
                border-radius: 10px;
                padding: 12px 24px;
                font-size: 16px;
                border: none;
            }
            QPushButton:hover {
                background-color: #7f8c8d;
            }
        """)
        cancel_button.clicked.connect(self.setup_main_ui)
        button_layout.addWidget(cancel_button)

        self.main_layout.addWidget(button_container)
        self.start_camera()

    def recognize_face(self):
        """얼굴 인식 수행"""
        if not self.cap or not self.cap.isOpened():
            return

        try:
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
                
                # 데이터베이스에서 사용자 검색
                best_match = self.find_best_match(current_face_vector)
                
                if best_match:
                    userid, username = best_match
                    self.last_recognized_user = (userid, username)
                    
                    # 인식된 사용자 확인
                    reply = QMessageBox.question(
                        self, "사용자 확인", 
                        f"인식된 사용자: {username}님\n\n맞습니까?",
                        QMessageBox.Yes | QMessageBox.No,
                        QMessageBox.Yes
                    )
                    
                    if reply == QMessageBox.Yes:
                        self.process_successful_login(userid, username)
                    else:
                        # 다시 시도할지 묻기
                        retry_reply = QMessageBox.question(
                            self, "다시 시도", 
                            "다시 얼굴 인식을 시도하시겠습니까?",
                            QMessageBox.Yes | QMessageBox.No,
                            QMessageBox.Yes
                        )
                        
                        if retry_reply == QMessageBox.Yes:
                            self.status_label.setText(f"다시 시도해주세요... (시도: {self.face_recognition_attempts + 1}/{self.max_attempts})")
                        else:
                            self.setup_main_ui()
                else:
                    self.handle_recognition_failure()
            else:
                self.status_label.setText("얼굴을 감지할 수 없습니다. 카메라에 얼굴이 명확히 보이도록 해주세요.")
                QMessageBox.warning(self, "경고", "카메라에 얼굴이 명확히 보이도록 해주세요.")
                
        except Exception as e:
            print(f"얼굴 인식 오류: {str(e)}")
            self.status_label.setText(f"얼굴 인식 중 오류 발생: {str(e)}")

    def find_best_match(self, current_face_vector):
        """최적 매치 찾기"""
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
            
            return best_match
            
        except Exception as e:
            print(f"데이터베이스 오류: {str(e)}")
            return None
        finally:
            if conn and conn.is_connected():
                cursor.close()
                conn.close()

    def handle_recognition_failure(self):
        """인식 실패 처리"""
        self.face_recognition_attempts += 1
        
        if self.face_recognition_attempts >= self.max_attempts:
            # 3회 실패 시 ID/비밀번호 로그인 버튼 표시
            self.status_label.setText("얼굴 인식에 실패했습니다. ID/비밀번호로 로그인해주세요.")
            self.login_button.setVisible(True)
            self.recognize_button.setEnabled(False)
            QMessageBox.warning(
                self, "인식 실패", 
                f"{self.max_attempts}회 시도했지만 인식에 실패했습니다.\nID/비밀번호로 로그인해주세요."
            )
        else:
            remaining_attempts = self.max_attempts - self.face_recognition_attempts
            self.status_label.setText(
                f"인식 실패: 등록된 사용자를 찾을 수 없습니다. (남은 시도: {remaining_attempts}회)"
            )
            QMessageBox.warning(
                self, "실패", 
                f"등록된 얼굴과 일치하지 않습니다.\n남은 시도 횟수: {remaining_attempts}회"
            )

    def show_manual_login(self):
        """수동 로그인 다이얼로그 표시"""
        dialog = LoginDialog(self)
        
        if dialog.exec_() == QDialog.Accepted:
            userid, password = dialog.get_credentials()
            
            if not userid or not password:
                QMessageBox.warning(self, "입력 오류", "ID와 비밀번호를 모두 입력해주세요.")
                return
            
            if self.verify_manual_login(userid, password):
                # 로그인 성공
                username = self.get_username_by_id(userid)
                self.process_successful_login(userid, username)
            else:
                QMessageBox.warning(self, "로그인 실패", "ID 또는 비밀번호가 올바르지 않습니다.")

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
            
            # 해시된 비밀번호로 확인 (실제 구현에서는 회원가입 시 해시된 비밀번호를 저장해야 함)
            hashed_password = self.hash_password(password)
            cursor.execute("SELECT userid FROM usertbl WHERE userid = %s AND userpassword = %s", 
                         (userid, hashed_password))
            result = cursor.fetchone()
            
            # 해시된 비밀번호가 없는 경우 평문 비밀번호로도 시도 (기존 데이터 호환성)
            if not result:
                cursor.execute("SELECT userid FROM usertbl WHERE userid = %s AND userpassword = %s", 
                             (userid, password))
                result = cursor.fetchone()
            
            return result is not None
            
        except Exception as e:
            print(f"로그인 검증 오류: {str(e)}")
            return False
        finally:
            if conn and conn.is_connected():
                cursor.close()
                conn.close()

    def get_username_by_id(self, userid):
        """사용자 ID로 이름 가져오기"""
        try:
            conn = mysql.connector.connect(
                host="127.0.0.1",
                user="famarket",
                password="qpalzm1029!",
                database="famarket"
            )
            cursor = conn.cursor()
            cursor.execute("SELECT username FROM usertbl WHERE userid = %s", (userid,))
            result = cursor.fetchone()
            return result[0] if result else userid
            
        except Exception as e:
            print(f"사용자명 조회 오류: {str(e)}")
            return userid
        finally:
            if conn and conn.is_connected():
                cursor.close()
                conn.close()

    def process_successful_login(self, userid, username):
        """성공적인 로그인 처리"""
        try:
            # 입장 기록 저장
            conn = mysql.connector.connect(
                host="127.0.0.1",
                user="famarket",
                password="qpalzm1029!",
                database="famarket"
            )
            cursor = conn.cursor()
            
            # 전화번호 조회 및 입장 기록
            cursor.execute("SELECT phonenum FROM usertbl WHERE userid = %s", (userid,))
            result = cursor.fetchone()
            
            if result:
                phonenum = result[0]
                cursor.execute("INSERT INTO entertbl (phonenum, enter_time) VALUES (%s, NOW())", (phonenum,))
                conn.commit()
            
            # UI 업데이트
            self.status_label.setText(f"환영합니다, {username}님!")
            QMessageBox.information(self, "로그인 성공", f"{username}님 환영합니다!")
            
            # 카메라 정지 및 메인 화면으로
            self.stop_camera()
            self.setup_main_ui()
            
        except Exception as e:
            print(f"입장 기록 저장 오류: {str(e)}")
            QMessageBox.warning(self, "DB 오류", f"입장 기록 저장 중 오류 발생: {str(e)}")
        finally:
            if conn and conn.is_connected():
                cursor.close()
                conn.close()

    def calculate_similarity(self, vector1, vector2):
        """벡터 유사도 계산 (코사인 유사도)"""
        min_len = min(len(vector1), len(vector2))
        v1 = np.array(vector1[:min_len])
        v2 = np.array(vector2[:min_len])
        
        dot = np.dot(v1, v2)
        norm1, norm2 = np.linalg.norm(v1), np.linalg.norm(v2)
        
        if norm1 == 0 or norm2 == 0:
            return 0
        
        return dot / (norm1 * norm2)

    def on_signup_success(self, user_id):
        """회원가입 성공 콜백"""
        QMessageBox.information(self, "회원가입 완료", "이제 얼굴을 등록해주세요.")
        self.start_face_registration(user_id)

    def closeEvent(self, event):
        """애플리케이션 종료 시 리소스 정리"""
        self.stop_camera()
        event.accept()

if __name__ == "__main__":
    app = QApplication(sys.argv)
    
    # 애플리케이션 스타일 설정
    app.setStyleSheet("""
        QMainWindow {
            background-color: #ecf0f1;
        }
        QWidget {
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        QMessageBox {
            background-color: white;
        }
        QLabel {
            color: #2c3e50;
        }
    """)
    
    window = FaceRecognitionApp()
    window.show()
    sys.exit(app.exec_())