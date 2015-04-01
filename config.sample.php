<?php
define('ENVIRONMENT', 'development'); // 'testing', 'production'
define('TOKEN_LIFETIME', 60 * 60 * 1000); // auth tokens expire after one hour
define('RESPONSE_LIMIT', 20);
define('RESPONSE_OFFSET', 0);

define('DB_HOST', 'host');
define('DB_USER', 'user');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'name');
define('TIME_ZONE', 'UTC');

ini_set('error_reporting', (ENVIRONMENT === 'production') ? 'E_NONE' : 'E_ALL');
ini_set('display_errors', (ENVIRONMENT === 'production') ? 0 : 1);
?>