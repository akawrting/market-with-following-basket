from django.shortcuts import render
import sys
sys.path.append(r"..")

#basket.py(임의의 장바구니 코드)의 값을 불러오게 함
from basket import total_price
def index(request):
    #여기에 가격 들어가게 하면 됨
    pay_amount = total_price
    return render(request, 'payment/index.html' , {"pay_amount": pay_amount})

def payment_success(request):
    paid_amount = request.GET.get("paid_amount", '0')
    merchant_uid = request.GET.get("merchant_uid", '0')
    return render(request, 'payment/payment_success.html', {"paid_amount": paid_amount, "merchant_uid": merchant_uid})

def payment_failed(request):
    return render(request, 'payment/payment_failed.html')