import serial
import time
import mysql.connector

# 블루투스 모듈이 연결된 COM 포트와 통신 속도 설정
ser = serial.Serial('COM8', 9600, timeout=1)  # 본인의 COM 포트와 속도로 변경

# MySQL 데이터베이스 연결 정보
db_config = {
    'host': 'localhost',
    'user': 'famarket',
    'password': 'qpalzm1029!',
    'database': 'famarket'
}

try:
    print("블루투스 시리얼 포트 연결됨.")
    print("DB 연결 및 도어락 상태 모니터링 시작...")
    
    # 이전 DB status를 저장
    prev_db_status = None 
    
    while True:
        try:
            # DB 연결
            conn = mysql.connector.connect(**db_config)
            cursor = conn.cursor()
            
            # DB에서 gatetbl의 status 값 읽기
            cursor.execute("SELECT status FROM gatetbl")
            db_result = cursor.fetchone()
            
            if db_result:
                # DB에서 읽은 status 값
                current_db_status = db_result[0]
                
                # 이전 상태와 현재 상태가 다를 때만 'o' 명령어 전송
                if prev_db_status is not None and current_db_status != prev_db_status:
                    ser.write('o'.encode())
                    print(f"보냄: o (DB status 변경 감지: {prev_db_status} -> {current_db_status})")
                    time.sleep(0.5) # 명령어 전송 후 잠시 대기
                
                prev_db_status = current_db_status # 현재 DB 상태 저장
            
            # 아두이노로부터 메시지 받기
            if ser.in_waiting > 0:
                received_data = ser.readline().decode().strip()
                print(f"아두이노로부터 받음: {received_data}")
            # DB 연결 종료
            cursor.close()
            conn.close()
            
        except mysql.connector.Error as db_err:
            print(f"데이터베이스 오류: {db_err}")
            time.sleep(5)  # 오류 발생 시 5초 대기 후 재시도
            
        # 0.5초마다 DB 상태 확인 및 아두이노 통신
        time.sleep(0.5)

except serial.SerialException as e:
    print(f"시리얼 포트 오류: {e}")
except KeyboardInterrupt:
    print("사용자에 의해 종료됨.")
finally:
    if 'ser' in locals() and ser.is_open:
        ser.close()
        print("시리얼 포트 닫힘.")
    if 'conn' in locals() and conn.is_connected():
        cursor.close()
        conn.close()