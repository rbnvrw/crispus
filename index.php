<?php

define('ROOT_PATH', realpath(dirname(__FILE__)) .'/');

require_once(ROOT_PATH.'vendor/autoload.php');

$oCrispus = new RubenVerweij\Crispus(ROOT_PATH.'config.ini');
