<?php
	class indexController
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
