<?php

include 'start.php';

set_time_limit(0);
$cfg_db = $config['mongo']['database'];

// Verifies if cards collection exists
$list_databases = new MongoDB\Driver\Command(['listDatabases' => 1]);
$result = $mongo_manager->executeCommand('admin', $list_databases);
$databases = $result->toArray();

$found = false;
foreach($databases[0]->databases as $obj)
	$found = $found || $obj->name == $cfg_db;

// If exists, delete all documents; Otherwise, create collection
if($found){
	$bulk = new MongoDB\Driver\BulkWrite();
	$bulk->delete([]);
	$mongo_manager->executeBulkWrite($cfg_db . '.cards', $bulk);
}
else{
	$create_collection = new MongoDB\Driver\Command(['create' => 'cards']);
	$mongo_manager->executeCommand($cfg_db, $create_collection);
}

// Import cards informations and images
$card_names = json_decode(file_get_contents('card_names.json'));
$bulk = new MongoDB\Driver\BulkWrite();

foreach($card_names as $id => $card_name){
	echo 'Importing ' . $card_name . "...\n";

	$monster_data = get_monster_data($card_name);
	if($monster_data['card_type'] != 'monster')
		continue;

	$monster_data['img_name'] = preg_replace('/[^a-z0-9_]/i', '_', $card_name) . '.jpg';
	$bulk->insert($monster_data);

	$img_path = 'img/cards/' . $monster_data['img_name'];
	if(!file_exists($img_path)){
		$tmp_path = get_monster_image($card_name);
		rename($tmp_path, $img_path);
	}
}

$result = $mongo_manager->executeBulkWrite($cfg_db . '.cards', $bulk);