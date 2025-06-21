from setuptools import setup

package_name = 'teleop_control'

setup(
    name=package_name,
    version='0.1.0',
    packages=[package_name],
    install_requires=['setuptools'],
    zip_safe=True,
    maintainer='your_name',
    maintainer_email='your_email@example.com',
    description='Teleop control using keyboard for ROS2',
    license='Apache License 2.0',
    tests_require=['pytest'],
    entry_points={
        'console_scripts': [
            'teleop_control = teleop_control.teleop_node:main',
            'motor_control = teleop_control.motor_control:main',
        ],
    },
)