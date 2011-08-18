<?php
	Renderer::render('view/template.phtml', array('moo' => 'moo title', '_content' => '<h1>Hello, World!</h1>'));
	$conn = Database::connect('mysql:host=localhost;dbname=chess', 'root', 'abcABC123');
	$res = Database::query($conn, "select * from logs where message like '%pawn%' limit 10;");
	
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
