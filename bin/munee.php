<?php

define('WEBROOT', realpath(dirname(__FILE__) .'/../../../../') . '/');
define('MUNEE_CACHE', WEBROOT.'data/cache');

require_once(WEBROOT.'vendor/autoload.php');

echo \Munee\Dispatcher::run(new \Munee\Request());
