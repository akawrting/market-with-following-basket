import subprocess
import time

# gedit 실행
process = subprocess.Popen(['gedit'])

# 5초 대기
time.sleep(5)

# gedit 종료
process.terminate()