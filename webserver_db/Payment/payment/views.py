from django.shortcuts import render
import sys
sys.path.append(r"..")
import mysql.connector

def index(request):
    try:
        # famarket DB 연결
        conn = mysql.connector.connect(
            host="127.0.0.1",
            user="famarket",
            password="qpalzm1029!",
            database="famarket"
        )
        
        cursor = conn.cursor(dictionary=True)  # 딕셔너리 형태로 결과 반환
        
        # paytable에서 가장 최근 레코드의 payprice 가져오기
        cursor.execute("SELECT payprice FROM paytable ORDER BY id DESC LIMIT 1")
        result = cursor.fetchone()
        
        if result:
            pay_amount = result['payprice']
            print(f"가져온 결제 금액: {pay_amount}")
        else:
            # 결제 정보가 없을 경우 기본값 설정
            pay_amount = 0
            print("결제 정보를 찾을 수 없음")
        
        # sbtable에서 상품 정보 가져오기
        cursor.execute("SELECT itemname, itemnum, totalprice FROM sbtable")
        sbtable_items = cursor.fetchall()
            
        cursor.close()
        conn.close()
        
    except Exception as e:
        print(f"DB 연결 오류: {e}")
        pay_amount = 0
        sbtable_items = []
    
    return render(request, 'payment/index.html', {
        "pay_amount": pay_amount,
        "sbtable": sbtable_items
    })

def payment_success(request):
    paid_amount = request.GET.get("paid_amount", '0')
    merchant_uid = request.GET.get("merchant_uid", '0')
    return render(request, 'payment/payment_success.html', {"paid_amount": paid_amount, "merchant_uid": merchant_uid})

def payment_failed(request):
    return render(request, 'payment/payment_failed.html')
