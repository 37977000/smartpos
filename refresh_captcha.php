<?php
session_start();
$captcha = substr(md5(rand()), 0, 6);
$_SESSION['captcha'] = $captcha;
echo $captcha;
?>