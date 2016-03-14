<?php

session_start();
include 'facebook-sdk-v5/autoload.php';
$config = include('config.php');

$fb = new Facebook\Facebook($config['facebook']);
$helper = $fb->getRedirectLoginHelper();