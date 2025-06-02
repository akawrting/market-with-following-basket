    title = QLabel("얼굴 인식 출입 시스템")
        title.setStyleSheet("font-size: 24px; font-weight: bold;")
        title.setAlignment(Qt.AlignCenter)
        layout.addWidget(title)

        self.userid_input = QLineEdit()
        self.userid_input.setPlaceholderText("아이디")
        self.userid_input.setStyleSheet("font-size: 16px; padding: 10px;")
        layout.addWidget(self.userid_input)

        self.password_input = QLineEdit()
        self.password_input.setEchoMode(QLineEdit.Password)
        self.password_input.setPlaceholderText("비밀번호")
        self.password_input.setStyleSheet("font-size: 16px; padding: 10px;")
        layout.addWidget(self.password_input)

        login_button = QPushButton("로그인")
        login_button.setStyleSheet("font-size: 16px; padding: 10px;")
        login_button.clicked.connect(self.handle_login)
        layout.addWidget(login_button)

        signup_button = QPushButton("회원가입")
        signup_button.setStyleSheet("font-size: 14px; padding: 8px;")
        signup_button.clicked.connect(self.handle_signup)
        layout.addWidget(signup_button)

        login_widget.setLayout(layout)
        self.setCentralWidget(login_widget)