<?php
	require_once(dirname(__FILE__) . '/simpletest/autorun.php');
	require_once('../core/Router.php');

	class MooFrame extends UnitTestCase
	{
		public function testRouter()
		{
			$r = new Router();
			
			$this->assertNotNull($r);
			$this->assertIsA($r, 'Router');

			$a = $r->getApplicationList();

			$this->assertNotNull($a);
			$this->assertTrue(is_array($a));
			$this->assertTrue(count($a) > 0);

			$app = $r->getDefaultApplication();

			$this->assertNotNull($app);
			$this->assertIsA($app, 'Application');

			$appName = $app->getName();

			$this->assertNotNull($appName);
			$this->assertTrue(is_string($appName));
			$this->assertNotNull($a[$app->getName()]);

			$appRoutes = $app->getRoutes();

			$this->assertNotNull($appRoutes);
			$this->assertTrue(is_array($appRoutes));
			$this->assertTrue(count($appRoutes) > 0);

			$appRootRoute = $app->getRootRoute();

			$this->assertNotNull($appRootRoute);
			$this->assertTrue(is_array($appRootRoute));
			$this->assertTrue(array_key_exists('match', $appRootRoute));

			/*
			 * // must never be null
			$baseApplication = $r->getBaseApplication();

			$this->assertNotNull($baseApplication);
			$this->assertIsA($baseApplication, 'Application');
			* 
			*/

			//  might be null
			$appControllers = $app->getControllers();
			
			$this->assertTrue(is_array($appControllers));

			// application's or core
			$controller = $app->getRootController();
			
			$this->assertTrue(is_object($controller));

			// might be null if application's one
			$controllerActions = get_class_methods($controller);
			
			$this->assertTrue(is_array($controllerActions));
			$this->assertTrue(count($controllerActions) > 0);

			// self or parent
			$action = $app->getDefaultAction();
			
			$this->assertTrue(is_string($action));
			$this->assertTrue(method_exists($controller, $action));

			// might not be null
			$url = $r->getUrl($appName, get_class($controller), $action);
			
			var_dump($appName, get_class($controller), $action);
			
			$this->assertTrue(is_string($url));
			
			var_dump($r->route('/Foo/index/index'));
		}
		
		public function testController()
		{
		}
	}
