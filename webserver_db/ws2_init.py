import asyncio
import websockets
import json

# 연결된 클라이언트들
connected_clients = set()

async def handle_client(websocket):
    connected_clients.add(websocket)
    print(f"클라이언트 연결됨: 현재 {len(connected_clients)}개 연결")
    
    try:
        async for message in websocket:
            print(f"메시지 수신: {message}")
            try:
                data = json.loads(message)
                
                # 여기가 중요! 모든 클라이언트에게 메시지 전달
                for client in connected_clients:
                    if client != websocket:  # 메시지를 보낸 클라이언트 제외
                        await client.send(message)  # 원본 메시지 그대로 전달
                
            except json.JSONDecodeError:
                print("JSON 형식이 아님")
            except Exception as e:
                print(f"메시지 처리 중 오류: {e}")
    except Exception as e:
        print(f"오류: {e}")
    finally:
        connected_clients.remove(websocket)
        print(f"클라이언트 연결 해제: 현재 {len(connected_clients)}개 연결")


async def main():
    # 최신 버전 websockets에서는 serve 함수에 직접 핸들러 전달
    server = await websockets.serve(handle_client, "0.0.0.0", 8989)
    print("웹소켓 서버 시작: 0.0.0.0:8989")
    await server.wait_closed()

if __name__ == "__main__":
    asyncio.run(main())
