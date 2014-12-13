<?php

define('WEBROOT', realpath(dirname(__FILE__)) .'/../');

require_once(WEBROOT.'vendor/autoload.php');

echo \Munee\Dispatcher::run(new \Munee\Request());
