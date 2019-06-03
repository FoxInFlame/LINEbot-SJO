<?php

$config = parse_ini_file(__DIR__ . '/../config.ini');

define('APP_ROOT', $config['app_root']);

define('SJO_LOGIN', $config['SJO_login']);
define('SJO_PASSWORD', $config['SJO_password']);
define('SJO_POST_PASSWORD', $config['SJO_post_password']);

define('ACCESS_TOKEN', $config['access_token']);

define('LINE_CHANNEL', $config['channel']);