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

			// must never be null
			$baseApplication = $r->getBaseApplication();

			$this->assertNotNull($baseApplication);
			$this->assertIsA($baseApplication, 'Application');

			//  might be null
			$appControllers = $app->getControllers();

			// application's or core
			$controller = $app->getDefaultController();

			// might be null if application's one
			$controllerActions = $controller->getActions();

			// self or parent
			$action = $controller->getDefaultAction();

			// might not be null
			$url = $r->getUrl($controller->getName(), $action->getName());
		}
	}
