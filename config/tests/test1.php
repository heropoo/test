<?php
/**
 * Created by PhpStorm.
 * User: ttt
 * Date: 2019/3/15
 * Time: 17:21
 */

require_once '../Config.php';
require_once '../Exception.php';

$config = new \Moon\Config\Config('config');

$app_name = $config->get('app.app_name');
$app_name1 = $config->get('app.app_name1');
$baby_name = $config->get('app.baby.name');

var_dump($app_name, $app_name1, $baby_name);

$app_name2 = $config->get('app.app_name2', true);


