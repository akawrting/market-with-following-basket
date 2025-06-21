import os

from ament_index_python.packages import get_package_share_directory

pkg_carto_test = get_package_share_directory('carto_test')

default=os.path.join(pkg_carto_test , 'map', 'my_room.pbstream')

print(default)