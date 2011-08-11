<?php
	$routes = array(
		'root' => array(
			'match' => '/:arg',
			'arg' => '[a-z]{1,}',
			'rewrite' => true,
			'controller' => 'index',
			'action' => 'index',
		),
	);

	$isDefault = true;
