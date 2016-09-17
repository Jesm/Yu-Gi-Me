<?php

return [
	'facebook' => [
		'app_id' => '{app-id}',
		'app_secret' => '{app-secret}',
		'default_graph_version' => 'v2.5'
	],

	'mongo' => [
		'uri' => 'mongodb://localhost',
		'database' => 'yugime'
	],

	'predefined_user_cards' => [
		'{fb-id}' => 'Exodia the Forbidden One'
	],

	'min_post_image_keep_time' => 60 * 60 * 24 // 1 day
];