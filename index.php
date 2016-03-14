<?php

include 'start.php';

$return_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/generate-card.php'; // TODO: refatorar para gerar url correta em caso de subdiretorios
$permissions = ['email', 'user_likes']; // optional
$login_url = $helper->getLoginUrl($return_url, $permissions);

header('Location: ' . $login_url);
exit;