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
		$response = $fb->get('/me?fields=name');
		$user = $response->getDecodedBody();
	}
	catch(Facebook\Exceptions\FacebookResponseException $e){
		exit('Graph returned an error: ' . $e->getMessage());
	}
	catch(Facebook\Exceptions\FacebookSDKException $e){
		exit('Facebook SDK returned an error: ' . $e->getMessage());
	}

	$params = get_card_params($user);
	$img_data = generate_card($params);
}