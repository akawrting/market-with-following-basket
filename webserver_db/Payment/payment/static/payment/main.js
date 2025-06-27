IMP.init("imp54322068");

const button = document.querySelector("button");
const merchantUid = new Date().getTime();
const pay_amount = document.getElementById('pay-amount').dataset.amount;

const onClickPay = async () => {
    IMP.request_pay({
        pg: "kakaopay",
        pay_method: "card",
        amount: pay_amount,
        name: "무인매장",
        merchant_uid: merchantUid,
    }, 
    function(response){
        if(response.success){
            window.location.href = `http://127.0.0.1:8000/payment/payment_success?paid_amount=${response.paid_amount}&merchant_uid=${response.merchant_uid}`;
        }
        else {
            window.location.href = "http://127.0.0.1:8000/payment/payment_failed";
        }

    });

};

document.getElementById("payButton").addEventListener("click", onClickPay);