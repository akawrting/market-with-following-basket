from django.shortcuts import render
import sys
sys.path.append(r"..")
import mysql.connector

# famarket DB에서 가장 최근 결제 금액을 가져오는 방식으로 변경
def index(request):
    try:
        # famarket DB 연결
        conn = mysql.connector.connect(
            host="127.0.0.1",
            user="famarket",
            password="qpalzm1029!",
            database="famarket"
        )
        
        cursor = conn.cursor()
        
        # 디버깅: paytable 구조 확인
        cursor.execute("DESCRIBE paytable")
        table_structure = cursor.fetchall()
        print("테이블 구조:", table_structure)
        
        # 디버깅: 데이터 확인
        cursor.execute("SELECT * FROM paytable ORDER BY id DESC LIMIT 3")
        data = cursor.fetchall()
        print("최근 데이터:", data)
        
        # paytable에서 가장 최근 레코드의 payprice 가져오기
        # 'id' 대신 실제 테이블의 기본 키나 날짜 필드를 사용할 수 있음
        cursor.execute("SELECT payprice FROM paytable ORDER BY id DESC LIMIT 1")
        result = cursor.fetchone()
        
        if result:
            pay_amount = result[0]
            print(f"가져온 결제 금액: {pay_amount}")
        else:
            # 결제 정보가 없을 경우 기본값 설정
            pay_amount = 0
            print("결제 정보를 찾을 수 없음")
            
        cursor.close()
        conn.close()
        
    except Exception as e:
        print(f"DB 연결 오류: {e}")
        pay_amount = 0
    
    return render(request, 'payment/index.html', {"pay_amount": pay_amount})

def payment_success(request):
    paid_amount = request.GET.get("paid_amount", '0')
    merchant_uid = request.GET.get("merchant_uid", '0')
    return render(request, 'payment/payment_success.html', {"paid_amount": paid_amount, "merchant_uid": merchant_uid})

def payment_failed(request):
    return render(request, 'payment/payment_failed.html')
