import os

# 터미널에서 ls 명령어 실행
output = os.popen('ros2 action send_goal /navigate_to_pose nav2_msgs/action/NavigateToPose \"{pose: {header: {frame_id: \'map\'}, pose: {position: {x: 1.255, y: -1.7, z: 0.0}, orientation: {x: 0.0, y: 0.0, z: 0.26, w: 0.96}}}}\"').read()

# 결과 출력
print("ls 명령어 결과:")
print(output)