<?php

include 'start.php';

$folder_path = 'img/posts';
$files = scandir($folder_path);
$curr_time = time();
foreach($files as $filename){
	preg_match('/[0-9]+_([0-9]+)\.jpg/i', $filename, $matches);
	if(count($matches) && $curr_time - (int) $matches[1] > $config['min_post_image_keep_time']){
		$img_path = $folder_path . '/' . $filename;
		echo "Deleting " . $img_path . "...\n";
		unlink($img_path);
	}
}