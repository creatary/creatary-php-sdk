<?php

define("SSL_VERIFIER", false); // set this to true for production use

define("APP_HOST", "http://yoursite.com");
define("APP_URL", APP_HOST . "/creatary-php-sdk/example/sms-hello-world");
define("APP_CALLBACK_URL", APP_URL . "/callback.php");

define("CREATARY_CONSUMER_KEY", "your_consumer_key"); // this is your application consumer key
define("CREATARY_CONSUMER_SECRET", "your_consumer_secret"); // this is your application consumer secret

define('OAUTH_TMP_DIR', function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : realpath($_ENV["TMP"]));

?>