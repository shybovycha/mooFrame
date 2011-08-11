<?php
	$routes = array(
		'root' => array(
			'match' => '/:arg',
			'arg' => '[a-z]{1,}',
			'rewrite' => true
		),
	);

	$isDefault = true;
