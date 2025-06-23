# market-with-following-basket

## At server(Windows)
1. download Composer-Setup.exe and install    https://getcomposer.org/download/
2. `cd your/php/project/path/` ex)c:/xampp/htdocs
3. `composer --version` check composer installed well
4. `composer init`
5. `composer require textalk/websocket`
6. `conda(pip) install websockets`
7. edit ex_return.php    ex) $websocket_server = "ws://<server ip>:8989";
8. `python ws_init.py`
9. http://localhost/ex_return.php

## At client(Raspberry Pi)
1. `pip3 install websockets`
2. edit ws_client.py    ex) SERVER_IP = "<server ip>"
3. python3 ws_client.py

