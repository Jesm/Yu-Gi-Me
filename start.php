<?php

session_start();
include 'facebook-sdk-v5/autoload.php';
$config = include('config.php');

$fb = new Facebook\Facebook($config['facebook']);
$helper = $fb->getRedirectLoginHelper();

function get_cardmaker_upload_path($url){
	$options = [
		'ssl' => [
			'verify_peer' => false,
			'verify_peer_name' => false
		]
	];
	$tmp_path = tempnam(sys_get_temp_dir(), 'image.jpg');
	$img_data = file_get_contents($url, false, stream_context_create($options));
	file_put_contents($tmp_path, $img_data);

	$ch = curl_init('http://www.yugiohcardmaker.net/ycmaker/uploadimage.php');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, [
		'userfile' => curl_file_create($tmp_path, 'image/jpeg', 'pic.jpg')
	]);
	$html = curl_exec($ch);
	curl_close($ch);

	preg_match('/window\.opener\.document\.getElementById\("picture"\)\.value\="(.+)";/mi', $html, $matches);
	return $matches[1];
}

function get_card_params($user){
	$picture_url = 'http://graph.facebook.com/' . $user['id'] . '/picture?width=400';
	$path = get_cardmaker_upload_path($picture_url);

	return [
		'name' => $user['name'],
		'cardtype' => 'Monster',
		'subtype' => 'magic',
		'attribute' => 'Dark',
		'level' => 8,
		'rarity' => 'Common',
		'picture' => $path,
		'circulation' => '',
		'set1' => '',
		'set2' => '',
		'type' => 'bodibuilde',
		'carddescription' => 'lorem',
		'atk' => 1300,
		'def' => 4000,
		'creator' => '',
		'year' => '2016',
		'serial' => ''
	];
}

function generate_card($params){
	$page = 'http://www.yugiohcardmaker.net/ycmaker/createcard.php';
	$url = $page . '?' . http_build_query($params);

	return file_get_contents($url);
}