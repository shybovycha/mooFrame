<?php
	Dispatcher::subscribe('hop', 'ext:MooExtension/Hop.php:compareArgs');
	
	Config::set('dbLogQuery', TRUE);
	$conn = Database::connect('mysql:host=localhost;dbname=chess', 'root', 'abcABC123');
	$res = Database::query($conn, "select * from logs where message like :pawn limit 10;", array(':pawn' => '%pawn%'));
	
	$ext1Res = Router::ext('MooExtension/Mooer.php:moo', 'Joe', 'Hustav', 'Mark');
	$ext2Res = Router::ext('MooExtension/Hop.php:hop', 'brbrbr');
	$extSummary = Router::ext('MooExtension/Hop.php:quote', $ext1Res, $ext2Res);
	
	Log::message("Hop event firing #1:", Dispatcher::fire('hop', 'a', 'a', 1, '1', '0', FALSE, 0, NULL, 0, FALSE));
	Log::message("Hop event firing #2:", Dispatcher::fire('hop', 'a', 'a', 1, '1', '0', FALSE, 0, FALSE));
	
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
