import rclpy
from rclpy.node import Node
from geometry_msgs.msg import Twist
import sys
import termios
import tty

class TeleopControl(Node):
    def __init__(self):
        super().__init__('teleop_control')
        self.publisher = self.create_publisher(Twist, '/cmd_vel', 10)
        self.linear_speed = 0.3
        self.angular_speed = 0.1
        self.get_logger().info("Teleop Control Node Initialized. Use W/A/S/D to control. Press Q to quit.")

    def get_key(self):
        tty.setraw(sys.stdin.fileno())
        key = sys.stdin.read(1)
        termios.tcsetattr(sys.stdin, termios.TCSADRAIN, termios.tcgetattr(sys.stdin))
        return key

    def run(self):
        try:
            while True:
                key = self.get_key()
                twist = Twist()

                if key == 'w':
                    twist.linear.x = self.linear_speed
                elif key == 's':
                    twist.linear.x = -self.linear_speed
                elif key == 'a':
                    twist.angular.z = self.angular_speed
                elif key == 'd':
                    twist.angular.z = -self.angular_speed
                elif key == 'q':
                    self.get_logger().info("Quitting Teleop Control")
                    break
                else:
                    twist.linear.x = 0.0
                    twist.angular.z = 0.0

                self.publisher.publish(twist)
        except Exception as e:
            self.get_logger().error(f"An error occurred: {e}")
        finally:
            twist = Twist()
            self.publisher.publish(twist)

def main(args=None):
    rclpy.init(args=args)
    teleop_node = TeleopControl()
    teleop_node.run()
    teleop_node.destroy_node()
    rclpy.shutdown()

if __name__ == '__main__':
    main()