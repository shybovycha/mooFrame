<?php
require_once('Application.php');

class Router
{
	private $__applicationList = NULL, $__defaultApplication = NULL, $__applicationParams;

	public function getApplicationList()
	{
		if (!isset($this->__applicationList) || !is_array($this->__applicationList))
			$this->loadApplicationList();

		return $this->__applicationList;
	}

	public function loadApplicationList()
	{
		$appList = scandir('../app/');

		$this->__applicationList = array();

		foreach ($appList as $app)
		{
			if (preg_match('/^\.{1,2}$/', $app))
				continue;

			if (is_dir('../app/' . $app))
			{
				$path = '../app/' . $app . '/';

				$instance = new Application($app);
				
				if (is_dir($path . '/etc/') && file_exists($path . '/etc/routes.php'))
				{
					ob_start();
					
					include($path . '/etc/routes.php');

					if (isset($routes))
						$instance->setRoutes($routes);

					unset($routes);

					if (isset($isDefault) && $isDefault === TRUE)
					{
						$instance->setIsDefault(TRUE);

						unset($isDefault);
					}
					
					$logMessage = ob_get_contents();
					ob_end_clean();
				}

				$this->__applicationList[$app] = $instance;

				unset($instance);
			}
		}
	}

	public function route($url = NULL)
	{
		if (!isset($url))
		{
			$url = $_SERVER['REQUEST_URI'];
		}
		
		$this->__applicationParams = NULL;
		
		$url = str_replace($_SERVER['SCRIPT_NAME'], '', $url);
		
		$pieces = preg_split('/\//', $url);
		$routingParams = array();

		// URL pattern: application/controller/action/arg0/arg1/...
		
		// application name
		if (isset($pieces[1]))
		{
			$routingParams['application'] = $pieces[1];
		}
		
		// controller name
		if (isset($pieces[2]))
		{
			$routingParams['controller'] = $pieces[2];
		}
		
		// action name
		if (isset($pieces[3]))
		{
			$routingParams['action'] = $pieces[3];
		}
		
		if (count($pieces) > 4)
		{
			$routingParams['params'] = array_splice($pieces, 4, count($pieces) - 4);
			$this->__applicationParams = $routingParams['params'];
		}

		$appList = $this->getApplicationList();
		$routeMatchingApps = array();
		
		foreach ($appList as $app)
		{
			if ($app->matchUrl($url) || $app->matchRoutingParams($routingParams))
			{
				$routeMatchingApps[] = $app;
				$app->dispatch($routingParams);
			}
		}
	}

	protected function getArrayValues($arr)
	{
		if (!isset($arr) || !is_array($arr))
			return NULL;

		$args = func_get_args();
		$args = array_slice($args, 1, count($args));
		
		if (isset($args) && is_array($args) && count($args) > 0)
		{
			$res = array();

			foreach ($args as $v)
			{
				if (array_key_exists($v, $arr))
				{
					if (isset($arr[$v]))
						$res[$v] = $arr[$v]; else
							$res[v] = TRUE;
				} else
				{
					$res[v] = NULL;
				}
			}

			return $res;
		} else
		{
			return $arr;
		}
	}

	public function getUrlParams()
	{
		return $this->getArrayValues($this->__applicationParams, func_get_args());
	}

	public function getPostParams()
	{
		return $this->getArrayValues($_POST, func_get_args());
	}

	public function getFileParams()
	{
		return $this->getArrayValues($_FILES, func_get_args());
	}
	
	public function getUrl($applicationName, $controllerName = NULL, $actionName = NULL)
	{
		$appList = $this->getApplicationList();
		
		if (!isset($applicationName) || !isset($appList[$applicationName]))
		{
			return NULL;
		}
		
		$application = $appList[$applicationName];
		
		$controllerList = $application->getControllers();
		
		if (!isset($controllerName))
		{
			$defaultAction = $application->getDefaultAction();
			$rootController = $application->getRootController();
			
			if (!isset($defaultAction))
			{
				if (!isset($rootController))
				{
					return NULL;
				}

				return $rootController;
			}
			
			return $defaultAction;
		}
		
		$controller = $application->getController($controllerName);
		
		$actionList = get_class_methods($controller);
		
		if (isset($actionName) && !isset($actionList[$actionName]))
		{
			return NULL;
		}
		
		if (!isset($actionName))
		{
			return "{$applicationName}/{$controllerName}/{$actionName}";
		}
		
		return "{$applicationName}/{$controllerName}/";
	}
}
