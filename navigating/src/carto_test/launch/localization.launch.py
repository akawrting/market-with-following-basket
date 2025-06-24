import os
from launch import LaunchDescription
from launch.actions import DeclareLaunchArgument
from launch.substitutions import LaunchConfiguration
from launch_ros.actions import Node
from ament_index_python.packages import get_package_share_directory

def generate_launch_description():
    pkg_carto_test = get_package_share_directory('carto_test')

    # cartographer setting file 1
    cartographer_config_dir = LaunchConfiguration('cartographer_config_dir',
                                                default=os.path.join(pkg_carto_test , 'config'))
    # cartographer setting file 2
    configuration_basename = LaunchConfiguration('configuration_basename', default='cartographer.lua') ## cartographer.lua : mapping, localization.lua: localization
    resolution = LaunchConfiguration('resolution', default='0.05')
    publish_period_sec = LaunchConfiguration('publish_period_sec', default='0.5')
    pbstream_file = LaunchConfiguration('pbstream_dir',
                                                default=os.path.join(pkg_carto_test, 'map', 'my_room.pbstream')) # Path to the pbstream file

    sim_time_arg = DeclareLaunchArgument(
        'use_sim_time', default_value='False',
        description='Flag to enable use_sim_time'
    )

    cartographer = Node(
        package='cartographer_ros',
        executable='cartographer_node',
        name='cartographer_node',
        output='screen',
        parameters=[{'use_sim_time': LaunchConfiguration('use_sim_time')}],
        arguments=[
            '-configuration_directory', cartographer_config_dir,
            '-configuration_basename', configuration_basename,
            '-load_state_filename', pbstream_file]
    )
    # Executing Cartographer
    cartographer_grid = Node(
        package='cartographer_ros',
        executable='cartographer_occupancy_grid_node',
        name='occupancy_grid_node',
        output='screen',
        parameters=[{'use_sim_time': LaunchConfiguration('use_sim_time')}],
        arguments=['-resolution', resolution, '-publish_period_sec', publish_period_sec]
    )


    launchDescriptionObject = LaunchDescription()

    launchDescriptionObject.add_action(sim_time_arg)
    launchDescriptionObject.add_action(cartographer)
    launchDescriptionObject.add_action(cartographer_grid)

    return launchDescriptionObject