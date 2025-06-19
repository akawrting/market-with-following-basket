from ultralytics import YOLO
import cv2
from picamera2 import Picamera2
import time
import pymysql
import RPi.GPIO as GPIO

SERVO_PIN = 17

GPIO.setmode(GPIO.BCM)
GPIO.setup(SERVO_PIN, GPIO.OUT)

pwm = GPIO.PWM(SERVO_PIN, 50)
pwm.start(0)

def set_angle(start_angle, end_angle, duration=1.0):
    steps = 20
    step_time = duration / steps
    angle_diff = end_angle - start_angle
    for i in range(steps + 1):
        current_angle = start_angle + (angle_diff * i / steps)
        duty = 2 + (current_angle / 18)
        pwm.ChangeDutyCycle(duty)
        time.sleep(step_time)
    pwm.ChangeDutyCycle(0)

def openning():
    set_angle(166, 100, 1.0)
    time.sleep(1)

def closing():
    set_angle(100, 166, 1.0)
    time.sleep(1)

def connect_to_database():
    try:
        conn = pymysql.connect(
            host="192.168.137.1",
            user="famarket",
            password="qpalzm1029!",
            database="famarket",
            charset="utf8mb4",
            autocommit=False
        )
        return conn
    except pymysql.MySQLError as e:
        print(f"Database connection error: {e}")
        return None

def get_item_stock(item_id):
    conn = connect_to_database()
    if conn is None:
        return None
    try:
        with conn.cursor() as cursor:
            cursor.execute("SELECT itemstock FROM itemtable WHERE itemid = %s", (item_id,))
            stock = cursor.fetchone()
            return stock[0] if stock else None
    finally:
        conn.close()

def get_item_info(item_id):
    conn = connect_to_database()
    if conn is None:
        return None, None
    try:
        with conn.cursor() as cursor:
            cursor.execute("SELECT itemname, itemprice FROM itemtable WHERE itemid = %s", (item_id,))
            result = cursor.fetchone()
            return result if result else (None, None)
    finally:
        conn.close()

def update_item_stock(item_id, new_stock):
    conn = connect_to_database()
    if conn is None:
        return
    try:
        with conn.cursor() as cursor:
            cursor.execute("UPDATE itemtable SET itemstock = %s WHERE itemid = %s", (new_stock, item_id))
        conn.commit()
        print(f"Item {item_id} stock updated to {new_stock}")
    finally:
        conn.close()

def update_sbtable(item_id, quantity, userid="default_user"):
    itemname, itemprice = get_item_info(item_id)
    if itemname is None or itemprice is None:
        print(f"Failed to load item {item_id} information")
        return

    total_price = itemprice * quantity
    conn = connect_to_database()
    if conn is None:
        return
    try:
        with conn.cursor() as cursor:
            cursor.execute("SELECT itemnum FROM sbtable WHERE itemid = %s", (item_id,))
            result = cursor.fetchone()
            if result:
                new_quantity = result[0] + quantity
                new_total = itemprice * new_quantity
                cursor.execute(
                    "UPDATE sbtable SET itemnum = %s, totalprice = %s WHERE itemid = %s",
                    (new_quantity, new_total, item_id)
                )
                print(f"Updated item {item_id} quantity to {new_quantity} in cart")
            else:
                cursor.execute(
                    "INSERT INTO sbtable (itemid, itemname, itemnum, totalprice) VALUES (%s, %s, %s, %s)",
                    (item_id, itemname, quantity, total_price)
                )
                print(f"Added new item {item_id} to cart")
        conn.commit()
    finally:
        conn.close()

picam2 = Picamera2()
config = picam2.create_preview_configuration(main={"size": (600, 700)})
picam2.configure(config)
picam2.start()
time.sleep(2)

model = YOLO('best.pt', verbose=False)
class_names = model.names
print(f"Available classes: {class_names}")

detection_times = {}
processed_items = {}
userid = "default_user"

closing()

while True:
    frame = picam2.capture_array()

    if frame.shape[-1] == 4:
        frame = cv2.cvtColor(frame, cv2.COLOR_RGBA2RGB)

    try:
        results = model(frame, verbose=False)
        current_time = time.time()
        current_detections = set()

        CLASS_NAME_TO_ITEMID = {
            'Caja roja': 2,
            'Creamy Chocobar': 4,
            'Goreabab': 9,
            'Mc Chocolaty chips cookies': 3,
            'Milka Ceralis': 10,
            'Milka White Chocolate': 5,
            'Nesquik': 6,
            'Nova choco bar': 8,
            'Oreo': 7,
            'Pocky': 1
        }

        if results and results[0].boxes is not None:
            for box in results[0].boxes:
                cls_id = int(box.cls.item())
                cls_name = class_names[cls_id]
                conf = box.conf.item()

                if conf > 0.5:
                    item_id = CLASS_NAME_TO_ITEMID.get(cls_name)
                    if item_id is None:
                        continue

                    current_detections.add(item_id)

                    if item_id not in detection_times:
                        detection_times[item_id] = current_time

                    elif (current_time - detection_times[item_id] >= 3.0 and
                          (item_id not in processed_items or current_time - processed_items[item_id] > 5.0)):

                        current_stock = get_item_stock(item_id)
                        if current_stock is not None and current_stock > 0:
                            update_item_stock(item_id, current_stock - 1)
                            update_sbtable(item_id, 1)
                            processed_items[item_id] = current_time
                            print(f"Item {item_id} added to cart")
                            openning()
                            closing()

        for item_id in list(detection_times.keys()):
            if item_id not in current_detections:
                del detection_times[item_id]

        annotated_frame = results[0].plot()
        cv2.imshow("Object Detection", annotated_frame)

    except Exception as e:
        print(f"Error: {e}")

    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cv2.destroyAllWindows()
picam2.stop()
