<?php
	$routes = array(
		'root' => array(
			'match' => '/',
			//'args' => array('arg' => '[a-z]{1,}'),
			//'arg' => '[a-z]{1,}',
			//'rewrite' => true,
			'controller' => 'index',
			'action' => 'index',
		),
	);

	$isDefault = true;
