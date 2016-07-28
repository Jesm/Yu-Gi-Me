<?php

session_start();
include 'facebook-sdk-v5/autoload.php';
$config = include('config.php');

$fb = new Facebook\Facebook($config['facebook']);
$helper = $fb->getRedirectLoginHelper();

function save_remote_image($url){
	// $headers = getallheaders();
	$options = [
		// 'http' => [
		// 	'method' => 'GET',
		// 	'header' => "User-Agent: " . $headers['User-Agent'] . " \r\n"
		// ],
		'ssl' => [
			'verify_peer' => false,
			'verify_peer_name' => false
		]
	];
	// echo $url . '<br>';
	$img_data = file_get_contents($url, false, stream_context_create($options));
	$path = tempnam(sys_get_temp_dir(), 'tmp_img');
	file_put_contents($path, $img_data);
	return $path;
}

function get_cardmaker_upload_path($url){
	$tmp_path = save_remote_image($url);

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
	// $path = get_cardmaker_upload_path($picture_url);
	$path = 'tempimages/175108937.jpg';

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

	$path = save_remote_image($url);
	return $path;
}

function url_exists($url){
	$headers = get_headers($url);
	return !preg_match('/404 not found$/i', $headers[0]);
}

function get_monster_for($user){
	global $config;
	if(isset($config['predefined_user_cards'][$user['id']]))
		return $config['predefined_user_cards'][$user['id']];
}

function normalize_monster_name($str){
	return str_replace('#', '', $str);
}

function get_monster_data($name){
	$name = normalize_monster_name($name);
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

function get_monster_image($name){
	$name = normalize_monster_name($name);
	$url = 'http://yugiohprices.com/api/card_image/' . urlencode($name);
	return save_remote_image($url);
}

function generate_post_image($user_img_path, $monster_img_path, $background_img_path, $path){
	$img = imagecreatefromjpeg($background_img_path);
	$user_img = imagecreatefromjpeg($user_img_path);
	$monster_img = imagecreatefromjpeg($monster_img_path);

	$padd = 35;
	$fiw = imagesx($img);
	$fih = imagesy($img);

	$uiw = imagesx($user_img);
	$uih = imagesy($user_img);
	$uihd = $fih - 2 * $padd;
	$uiwd = $uiw * $uihd / $uih;
	imagecopyresampled($img, $user_img, $padd * 1.8, $padd, 0, 0, $uiwd, $uihd, $uiw, $uih);

	$miw = imagesx($monster_img);
	$mih = imagesy($monster_img);
	$mihd = $fih - 2 * $padd;
	$miwd = $miw * $mihd / $mih;
	imagecopyresampled($img, $monster_img, $fiw - $padd * 1.8 - $miwd, $padd, 0, 0, $miwd, $mihd, $miw, $mih);

	imagejpeg($img, $path, 100);

	imagedestroy($user_img);
	imagedestroy($monster_img);
	imagedestroy($img);
}