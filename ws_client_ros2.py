import asyncio
import websockets
import json
import os # os 모듈 추가

# 웹소켓 서버 주소 (노트북의 IP 주소와 포트)
SERVER_IP = "192.168.137.1"  # 노트북의 실제 IP 주소로 변경
SERVER_PORT = 9090
WEBSOCKET_SERVER_URL = f"ws://{SERVER_IP}:{SERVER_PORT}"

async def connect_to_websocket():
    print(f"웹소켓 서버에 연결 중: {WEBSOCKET_SERVER_URL}")
    while True:
        try:
            async with websockets.connect(WEBSOCKET_SERVER_URL) as websocket:
                print("웹소켓 서버에 연결되었습니다.")
                
                # 메시지 수신 대기
                while True:
                    message = await websocket.recv()
                    print(f"서버로부터 메시지 수신: {message}")
                    
                    try:
                        data = json.loads(message)
                        
                        # 'command' 타입의 메시지를 받으면 ROS2 명령어 실행
                        if data.get("type") == "ros2_command":
                            command_to_execute = data.get("command")
                            if command_to_execute:
                                print(f"ROS2 명령어 실행 요청 수신: {command_to_execute}")
                                
                                # os.popen을 사용하여 명령어 실행
                                try:
                                    result = os.popen(command_to_execute).read()
                                    print(f"명령어 실행 결과:\n{result}")
                                except Exception as cmd_err:
                                    print(f"ROS2 명령어 실행 중 오류 발생: {cmd_err}")
                            else:
                                print("실행할 ROS2 명령어가 없습니다.")
                        
                        # 웹 클라이언트에서 'item_select' 타입으로 ROS2 명령어를 보냄
                        elif data.get("type") == "item_select":
                            command_to_execute = data.get("command")
                            item_id = data.get("item_id")
                            
                            if command_to_execute:
                                print(f"아이템 선택 및 ROS2 명령어 실행 요청 수신 (item_id: {item_id}): {command_to_execute}")
                                
                                # os.popen을 사용하여 명령어 실행
                                try:
                                    result = os.popen(command_to_execute).read()
                                    print(f"명령어 실행 결과:\n{result}")
                                except Exception as cmd_err:
                                    print(f"ROS2 명령어 실행 중 오류 발생: {cmd_err}")
                            else:
                                print("실행할 ROS2 명령어가 없습니다.")
                        
                        else:
                            print(f"알 수 없는 메시지 타입 또는 형식: {data}")
                            
                    except json.JSONDecodeError:
                        print(f"JSON 형식이 아님: {message}")
                    except Exception as e:
                        print(f"메시지 처리 중 오류 발생: {e}")

        except websockets.exceptions.ConnectionClosedOK:
            print("웹소켓 연결이 정상적으로 종료되었습니다. 재연결 시도...")
        except websockets.exceptions.ConnectionClosedError as e:
            print(f"웹소켓 연결 오류 발생: {e}. 재연결 시도...")
        except ConnectionRefusedError:
            print("웹소켓 서버에 연결할 수 없습니다. 서버가 실행 중인지 확인하세요. 5초 후 재시도...")
        except Exception as e:
            print(f"알 수 없는 오류 발생: {e}. 5초 후 재시도...")
        
        await asyncio.sleep(5) # 연결 실패 시 5초 후 재시도

if __name__ == "__main__":
    asyncio.run(connect_to_websocket())
