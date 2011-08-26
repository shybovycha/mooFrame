<?php
	Renderer::render('view/template.phtml', array(
		'moo' => 'foo title', 
		'_content' => '<h1>Redirection in action!</h1>', 
		'url' => Router::getUrl('referer'),
	));
