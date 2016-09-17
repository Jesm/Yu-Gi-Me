<?php

include 'start.php';

$return_url = get_url_for('/generate-card.php');
$permissions = ['public_profile', 'publish_actions'];
$login_url = $helper->getLoginUrl($return_url, $permissions);

header('Location: ' . $login_url);
exit;