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
		$appDir = '../app';
		$appList = scandir($appDir);

		$this->__applicationList = array();

		foreach ($appList as $app)
		{
			if (preg_match('/^\.{1,2}$/', $app))
				continue;

			$appPath = self::formatPath($appDir, $app);

			if (is_dir($appPath))
			{
				$path = self::formatPath($appPath . '/');

				$instance = new Application($app);

				if (is_dir(self::formatPath($path . '/etc/')) && file_exists(self::formatPath($path . '/etc/routes.php')))
				{
					ob_start();
					
					include(self::formatPath($path . '/etc/routes.php'));

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

	public static function formatPath()
	{
		$path = implode('/', func_get_args());
		$path = preg_replace('/\/\//', '/', $path);
		$path = preg_replace('/\\\\/',  '\\', $path);
		//$path = preg_replace('/\\//', '\\', $path);

		return $path;
	}

	public function route($url = NULL)
	{
		if (!isset($url))
		{
			$url = $_SERVER['REQUEST_URI'];
		}
		
		$this->__applicationParams = NULL;
		
		$url = str_replace($_SERVER['SCRIPT_NAME'], '', $url);

		$mediaPath = '../media/';

		if (preg_match('/.+\..+$/', $url) && file_exists(self::formatPath($mediaPath . $url)))
		{
			$file = self::formatPath($mediaPath, $url);

			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			finfo_file($finfo, $file);
			$f = fopen($file, 'r');

			if (headers_sent())
				return FALSE;

			header('Content-Type: ' . finfo_file($finfo, $file));

			echo stream_get_contents($f);

			fclose($f);
			finfo_close($finfo);

			return TRUE;
		}

		$pieces = preg_split('/\//', $url);
		$routingParams = array();

		if (empty($url))
			$url =  '/';

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
	
	/*
	 * Retrieves object URL
	 * 
	 * The $object argument determines which object should be mapped.
	 * 
	 * $object format:
	 * 
	 * * app:<application>
	 *     returns default <application>'s controller and action URL
	 * 
	 * * app:<application>/<controller>
	 *     returns default <application> <controller>'s URL
	 * 
	 * * app:<application>/<controller>/<action>
	 *     returns concrete <action> URL
	 * 
	 * * file:<filename>
	 *     returns file URL. File should be located within www/ directory or it 
	 *     would not be visible for client
	 * 
	 *     TODO:
	 * 			when file is located within media/ - route paths from index.php
	 * 			to that file first
	 * 
	 */
	 
	public static function getUrl($object)
	{
		$appRegex = '/^app:(.+)$/';
		$fileRegex = '/^file:(.+)$/';
		
		$pieces = array();
		$result = NULL;
		
		if (preg_match($appRegex, $object))
		{
		} else
		if (preg_match($fileRegex, $object, $pieces))
		{
			if (!is_array($pieces) || count($pieces) < 2)
				return NULL;
				
			$cwd = getcwd();
			chdir(dirname(__FILE__));
				
			$mediaPath = '../media/';
				
			// cwd points to current application's directory
			$files = scandir($mediaPath);

			foreach ($files as $f)
			{
				if (file_exists(Router::formatPath($mediaPath . $f)) && $f == $pieces[1])
				{
					$result = $f;
					break;
				}
			}
			
			chdir($cwd);
		}
		
		return Router::formatPath('index.php', $result);
		
		/*$appList = $this->getApplicationList();
		
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
		
		return "{$applicationName}/{$controllerName}/";*/
	}
}
