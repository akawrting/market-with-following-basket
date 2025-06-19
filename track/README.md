# Following-shoes_Raspberrypi5-Car
YOLOv5n, Deepsort


설치해야될 라이브러리 목록

#YOLO, DEEPSORT

pip install opencv-python torch torchvision torchaudio deep_sort_realtime scipy numpy pandas requests seaborn


# GPIOZERO

sudo apt update
sudo apt install python3-gpiozero
(venv) pip install gpiozero
pip install lgpio --break-system-packages  # or env
pip3 install rpi-lgpio

# give the authorize to gpio group
sudo nano /etc/udev/rules.d/99-rpi)gpio.rules
#add that 1 line
SUBSYSTEM=="gpio", KERNEL=="gpiochip*", MODE="0666"
#close
sudo udevadm control --reload-rules
sudo udevadm trigger
sudo reboot
