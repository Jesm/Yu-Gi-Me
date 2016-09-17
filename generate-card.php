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
		$response = $fb->get('/me?fields=name,locale');
		$user = $response->getDecodedBody();
	}
	catch(Facebook\Exceptions\FacebookResponseException $e){
		exit('Graph returned an error: ' . $e->getMessage());
	}
	catch(Facebook\Exceptions\FacebookSDKException $e){
		exit('Facebook SDK returned an error: ' . $e->getMessage());
	}

	$params = get_cardmaker_params($user);
	$monster_data = get_monster_for($user);

	$type = preg_split('/\//i', $monster_data->type);
	$params += [
		'cardtype' => $monster_data->card_type,
		'attribute' => $monster_data->family,
		'level' => $monster_data->level,
		'rarity' => 'Common',
		'type' => $type[0],
		'subtype' => $type[1],
		'carddescription' => $monster_data->text,
		'atk' => $monster_data->atk,
		'def' => $monster_data->def,
	];
	$user_img_path = generate_card($params);

	$monster_img_path = 'img/cards/' . $monster_data->img_name;
	$post_img_path = 'img/posts/' . $user['id'] . '_' . time() . '.jpg';
	generate_post_image($user_img_path, $monster_img_path, 'img/bg_cropped.jpg', $post_img_path);

	$post_data = [
		'link' => get_url_for('/index.php'),
		'picture' => get_url_for('/' . $monster_img_path)
		// 'picture' => 'http://placehold.it/600x315'
	];
	$post_data += get_translated_post_content($user, $monster_data);

	try {
		$response = $fb->post('/me/feed', $post_data);
		var_dump($response);
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		exit('Graph returned an error: ' . $e->getMessage());
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		exit('Facebook SDK returned an error: ' . $e->getMessage());
	}
}