<?php
	Dispatcher::subscribe('moo', 'foo');
	
	Config::set('dbLogQuery', TRUE);
	$conn = Database::connect('mysql:host=localhost;dbname=chess', 'root', 'abcABC123');
	$res = Database::query($conn, "select * from logs where message like :pawn limit 10;", array(':pawn' => '%pawn%'));
	
	$ext1Res = Router::ext('MooExtension/Mooer.php:moo', 'Joe', 'Hustav', 'Mark');
	$ext2Res = Router::ext('MooExtension/Hop.php:hop', 'brbrbr');
	$extSummary = Router::ext('MooExtension/Hop.php:quote', $ext1Res, $ext2Res);
	
	Renderer::render('view/template.phtml', array(
		'moo' => 'moo title', 
		'_content' => '<h1>Hello, World!</h1>' . $extSummary, 
		'rows' => $res)
	);
	
/*	class index
	{
		public function index()
		{
			Renderer::render('view/template.phtml', array('moo' => 'moo title', '_content' => '<h1>Hello, World!</h1>'));
		}
		
		public function hello($name)
		{
		}
		
		protected function moveSomeFile()
		{
		}
	}
*/
