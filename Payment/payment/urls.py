from django.urls import path

from . import views
app_name = "payment"

urlpatterns = [
    
    path("", views.index, name="index"),
    path("payment_success/", views.payment_success, name="payment_success"),
    path("payment_failed/", views.payment_failed, name="payment_failed"),
]