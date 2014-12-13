<?php

define('ROOT_PATH', realpath(dirname(__FILE__)));

require_once(ROOT_PATH.'/vendor/autoload.php');

Crispus\Config::getInstance();

$oCrispus = new Crispus\Crispus();
