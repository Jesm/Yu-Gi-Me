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

function get_cardmaker_params($user){
	$picture_url = 'http://graph.facebook.com/' . $user['id'] . '/picture?width=400';
	$path = get_cardmaker_upload_path($picture_url);
	// $path = 'tempimages/175108937.jpg';

	return [
		'name' => $user['name'],
		'picture' => $path,
		'circulation' => '',
		'set1' => '',
		'set2' => '',
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

function url_exists($url){
	$headers = get_headers($url);
	return !preg_match('/404 not found$/i', $headers[0]);
}

function get_monster_for($user){
	global $config;
	if(isset($config['predefined_user_cards'][$user['id']]))
		return get_monster_data($config['predefined_user_cards'][$user['id']]);
}

function get_monster_data($name){
	$name = str_replace('#', '', $name);
	$url = 'http://yugiohprices.com/api/card_data/' . $name;
	$json = file_get_contents($url);
	$response = json_decode($json, true);

	$url = 'http://yugioh.wikia.com/wiki/Special:ExportRDF/' . str_replace(' ', '_', $name);
	$xml = file_get_contents($url);
	preg_match('/<property:Portuguese_name rdf:datatype=\"(?:.+)\">(.+)<\/property:Portuguese_name>/mi', $xml, $matches);
	if(isset($matches[1]))
		$response['data']['portuguese_name'] = $matches[1];

	return $response['data'];
}