<?php

include 'start.php';

set_time_limit(0);
$cfg_db = $config['mongo']['database'];

// If database exists, delete all documents in cards collection; Otherwise, create collections
if(!mongo_database_exists($mongo_manager, $cfg_db)){
	$create_collection = new MongoDB\Driver\Command(['create' => 'cards']);
	$mongo_manager->executeCommand($cfg_db, $create_collection);

	$create_collection = new MongoDB\Driver\Command(['create' => 'users']);
	$mongo_manager->executeCommand($cfg_db, $create_collection);
}

// Import cards informations and images
$card_names = json_decode(file_get_contents('card_names.json'));
$bulk = new MongoDB\Driver\BulkWrite();
$bulk->delete([]);

// $card_names = array_slice($card_names, 0, 20);

foreach(array_unique($card_names) as $id => $card_name){
	echo 'Importing ' . $card_name . "...\n";

	$monster_data = get_monster_data($card_name);
	if($monster_data['card_type'] != 'monster')
		continue;

	$monster_data['json_name'] = $card_name;
	$monster_data['img_name'] = preg_replace('/[^a-z0-9_]/i', '_', $card_name) . '.jpg';
	$bulk->insert($monster_data);

	$img_path = 'img/cards/' . $monster_data['img_name'];
	if(!file_exists($img_path)){
		$tmp_path = get_monster_image($card_name);
		rename($tmp_path, $img_path);
	}
}

echo "Writing to database...\n";
$result = $mongo_manager->executeBulkWrite($cfg_db . '.cards', $bulk);
echo "Import completed!\n";