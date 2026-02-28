<?php

$scope = "create";

$test = (object) [
	'name' => '',
	'variations' =>  [
		(object) [
			'id' => null,
			'name' => '',
			'percentage' => ''
		],
		(object) [
			'id' => null,
			'name' => '',
			'percentage' => ''
		]
	]
];

$postsForTest = null;

include (__DIR__."/form.view.php");