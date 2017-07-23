<?php
require_once './vendor/autoload.php';

use App\Http;

$url = 'http://ids.xaut.edu.cn/authserver/login?service=http://my.xaut.edu.cn/login.portal';
$username = '';  // 登陆账号
$password = '';  // 登陆密码

$http = new Http($url, $username, $password);
$http->get();
$url = $http->login();
//var_dump($url);
$result = $http->go();
echo $result;
