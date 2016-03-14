<?php

include 'start.php';

try{
	$token = $helper->getAccessToken();
}
catch(Facebook\Exceptions\FacebookResponseException $e){
	exit('Graph returned an error: ' . $e->getMessage());
}
catch(Facebook\Exceptions\FacebookSDKException $e){
	exit('Facebook SDK returned an error: ' . $e->getMessage());
}

if(isset($token)){
	$token = (string) $token;
	$fb->setDefaultAccessToken($token);

	try {
		$response = $fb->get('/me');
		$user = $response->getGraphUser();
	}
	catch(Facebook\Exceptions\FacebookResponseException $e){
		exit('Graph returned an error: ' . $e->getMessage());
	}
	catch(Facebook\Exceptions\FacebookSDKException $e){
		exit('Facebook SDK returned an error: ' . $e->getMessage());
	}

	exit('Olá ' . $user->getName());
}