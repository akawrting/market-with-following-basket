import rclpy
from rclpy.node import Node
from geometry_msgs.msg import Twist
import gpiod

# 모터 핀 설정 (BCM 번호 기준)
IN1 = 27
IN2 = 22
ENA = 18
IN3 = 24
IN4 = 25
ENB = 23

PWM_FREQUENCY = 100  # PWM 주파수 (Hz) 

class MotorControl(Node):
    def __init__(self):
        super().__init__('motor_control')
        self.subscription = self.create_subscription(
            Twist,
            'cmd_vel',
            self.listener_callback,
            10
        )

        # GPIO 칩 초기화
        self.chip = gpiod.Chip('gpiochip4')

        # GPIO 라인 설정
        self.in1 = self.chip.get_line(IN1)
        self.in2 = self.chip.get_line(IN2)
        self.ena = self.chip.get_line(ENA)
        self.in3 = self.chip.get_line(IN3)
        self.in4 = self.chip.get_line(IN4)
        self.enb = self.chip.get_line(ENB)

        # 라인 요청
        self.in1.request(consumer="motor_control", type=gpiod.LINE_REQ_DIR_OUT)
        self.in2.request(consumer="motor_control", type=gpiod.LINE_REQ_DIR_OUT)
        self.ena.request(consumer="motor_control", type=gpiod.LINE_REQ_DIR_OUT)
        self.in3.request(consumer="motor_control", type=gpiod.LINE_REQ_DIR_OUT)
        self.in4.request(consumer="motor_control", type=gpiod.LINE_REQ_DIR_OUT)
        self.enb.request(consumer="motor_control", type=gpiod.LINE_REQ_DIR_OUT)

        self.get_logger().info("Motor Control Node Initialized.")

    def listener_callback(self, msg):
        linear = msg.linear.x    # 전후진
        angular = max(min(msg.angular.z, 0.5), -0.5)  # 좌우회전

        # 기본 속도 계산
        left_speed = linear - angular
        right_speed = linear + angular

        # 왼쪽 모터 제어
        self.set_motor(self.in1, self.in2, self.ena, left_speed)

        # 오른쪽 모터 제어
        self.set_motor(self.in3, self.in4, self.enb, right_speed)

    def set_motor(self, pin1, pin2, pwm_pin, speed):
        if speed > 0:
            pin1.set_value(1)
            pin2.set_value(0)
        elif speed < 0:
            pin1.set_value(0)
            pin2.set_value(1)
        else:
            pin1.set_value(0)
            pin2.set_value(0)
        
        # PWM 신호를 Duty Cycle로 변환하여 설정
        duty_cycle = min(abs(int(speed * 100)), 100)
        pwm_pin.set_value(duty_cycle)

    def cleanup(self):
        # GPIO 해제
        self.in1.release()
        self.in2.release()
        self.ena.release()
        self.in3.release()
        self.in4.release()
        self.enb.release()
        self.chip.close()

def main(args=None):
    rclpy.init(args=args)
    node = MotorControl()
    try:
        rclpy.spin(node)
    finally:
        node.cleanup()
        node.destroy_node()
        rclpy.shutdown()
