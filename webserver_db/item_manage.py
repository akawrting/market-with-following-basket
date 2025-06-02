import mysql.connector
from mysql.connector import Error

def connect_to_database():
    try:
        conn = mysql.connector.connect(
            host="127.0.0.1",
            user="famarket",
            password="qpalzm1029!",
            database="famarket"
        )
        if conn.is_connected():
            return conn
    except Error as e:
        print(f"❌ 데이터베이스 연결 오류: {e}")
        return None

def get_item_stock(item_id):
    conn = connect_to_database()
    if conn is None:
        return None
    try:
        cursor = conn.cursor()
        cursor.execute("SELECT itemstock FROM itemtable WHERE itemid = %s", (item_id,))
        stock = cursor.fetchone()
        return stock[0] if stock else None
    except Error as e:
        print(f"❌ 재고 조회 오류: {e}")
        return None
    finally:
        cursor.close()
        conn.close()

def get_item_info(item_id):
    conn = connect_to_database()
    if conn is None:
        return None, None
    try:
        cursor = conn.cursor()
        cursor.execute("SELECT itemname, itemprice FROM itemtable WHERE itemid = %s", (item_id,))
        result = cursor.fetchone()
        return result if result else (None, None)
    except Error as e:
        print(f"❌ 아이템 정보 조회 오류: {e}")
        return None, None
    finally:
        cursor.close()
        conn.close()

def update_item_stock(item_id, new_stock):
    conn = connect_to_database()
    if conn is None:
        return
    try:
        cursor = conn.cursor()
        cursor.execute("UPDATE itemtable SET itemstock = %s WHERE itemid = %s", (new_stock, item_id))
        conn.commit()
        print(f"✅ 아이템 {item_id} 재고를 {new_stock}으로 업데이트했습니다.")
    except Error as e:
        print(f"❌ 재고 업데이트 오류: {e}")
    finally:
        cursor.close()
        conn.close()

def update_sbtable(item_id, quantity, userid):  # userid 매개변수 추가
    itemname, itemprice = get_item_info(item_id)
    if itemname is None or itemprice is None:
        print(f"❌ 아이템 {item_id} 정보 불러오기 실패")
        return

    total_price = itemprice * quantity

    conn = connect_to_database()
    if conn is None:
        return
    try:
        cursor = conn.cursor()
        # 이미 존재하는 항목인지 확인 (userid도 함께 확인)
        cursor.execute("SELECT itemnum FROM sbtable WHERE itemid = %s AND userid = %s", (item_id, userid))
        result = cursor.fetchone()
        if result:
            # 이미 있으면 수량 및 총 가격 업데이트
            new_quantity = result[0] + quantity
            new_total = itemprice * new_quantity
            cursor.execute(
                "UPDATE sbtable SET itemnum = %s, totalprice = %s WHERE itemid = %s AND userid = %s",
                (new_quantity, new_total, item_id, userid)
            )
            print(f"✅ sbtable의 아이템 {item_id} 수량을 {new_quantity}개로 업데이트. (사용자: {userid})")
        else:
            # 없으면 새로 삽입 (userid 포함)
            cursor.execute(
                "INSERT INTO sbtable (itemid, itemname, itemnum, totalprice, userid) VALUES (%s, %s, %s, %s, %s)",
                (item_id, itemname, quantity, total_price, userid)
            )
            print(f"✅ sbtable에 아이템 {item_id}을(를) 새로 추가했습니다. (사용자: {userid})")
        conn.commit()
    except Error as e:
        print(f"❌ sbtable 업데이트 오류: {e}")
    finally:
        cursor.close()
        conn.close()

def display_item_stock():
    item_ids = [1, 2, 3, 4]
    for item_id in item_ids:
        stock = get_item_stock(item_id)
        if stock is not None:
            print(f"🛒 아이템 {item_id} 재고: {stock}")

if __name__ == "__main__":
    # 사용자 ID 입력 받기
    userid = input("사용자 ID를 입력하세요: ")
    
    while True:
        display_item_stock()
        try:
            item_id = int(input("구매할 아이템 ID (1-4): "))
            current_stock = get_item_stock(item_id)

            if current_stock is None:
                print("❌ 해당 아이템이 존재하지 않습니다.")
                continue
            elif current_stock <= 0:
                print("⚠️ 재고가 없습니다.")
                continue

            # 재고 감소
            update_item_stock(item_id, current_stock - 1)

            # 장바구니 테이블 업데이트 (userid 전달)
            update_sbtable(item_id, 1, userid)

            print(f"🎉 아이템 {item_id} 1개 구매 완료! (사용자: {userid})")
        except ValueError:
            print("❌ 숫자만 입력하세요.")
