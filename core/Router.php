<?php
require_once('Log.php');
require_once('Application.php');

class Router
{
	private static $__applicationList = NULL;
	private static $__applicationParams = NULL;

	public static function getAppList()
	{
		$cwd = getcwd();
		chdir(dirname(__FILE__));

		$appDir = '../app';
		$appList = scandir($appDir);

		$__applicationList = array();

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

				$__applicationList[$app] = $instance;

				unset($instance);
			}
		}

		chdir($cwd);

		return $__applicationList;
	}

	public static function getApplicationList()
	{
		if (!isset(self::$__applicationList) || !is_array(self::$__applicationList))
			self::$__applicationList = self::getAppList();

		return self::$__applicationList;
	}

	public static function formatPath()
	{
		$path = implode('/', func_get_args());
		$path = preg_replace('/\/\//', '/', $path);
		$path = preg_replace('/\\\\/',  '\\', $path);
		//$path = preg_replace('/\\//', '\\', $path);

		return $path;
	}

	public static function route($url = NULL)
	{
		if (!isset($url))
		{
			$url = $_SERVER['REQUEST_URI'];
		}
		
		self::$__applicationParams = NULL;

		/*if (!preg_match(preg_quote($_SERVER['SCRIPT_NAME']), $url))
			$url = trim($url, '/');*/
		
		$url = str_replace($_SERVER['SCRIPT_NAME'], '', $url);

		$mediaPath = '../media/';

		if (preg_match('/.+\..+$/', $url) && file_exists(self::formatPath($mediaPath . $url)))
		{
			$file = self::formatPath($mediaPath, $url);

			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			finfo_file($finfo, $file);
			$f = fopen($file, 'r');

			if (headers_sent())
			{
				Log::message("Could not display '{$url}' file content: headers already sent, so the file would not be displayed properly anyway.");
				return FALSE;
			}

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

		// Bugfix:: when NetBeans starts XDebug session,
		// it goes to http://myhost/blah-blah-blah/?XDEBUG_SESSION_START=netbeans-xdebug
		// and router recognizes a controller named '?XDEBUG_SESSION_START'
		// which is actually a bug
		$getArgsListPattern = '/^\?.*/';

		// URL pattern: application/controller/action/arg0/arg1/...
		
		// application name
		if (isset($pieces[1]))
		{
			if (!preg_match($getArgsListPattern, $pieces[1]))
				$routingParams['application'] = $pieces[1];
		}
		
		// controller name
		if (isset($pieces[2]))
		{
			if (!preg_match($getArgsListPattern, $pieces[2]))
				$routingParams['controller'] = $pieces[2];
		}
		
		// action name
		if (isset($pieces[3]))
		{
			if (!preg_match($getArgsListPattern, $pieces[3]))
				$routingParams['action'] = $pieces[3];
		}
		
		if (count($pieces) > 4)
		{
			$routingParams['params'] = array_splice($pieces, 4, count($pieces) - 4);
			self::$__applicationParams = $routingParams['params'];
		}
		
		$appList = self::getApplicationList();
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

	protected static function getArrayValues($arr)
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

	public static function getUrlParams()
	{
		return self::getArrayValues(self::$__applicationParams, func_get_args());
	}

	public static function getPostParams()
	{
		return self::getArrayValues($_POST, func_get_args());
	}

	public static function getFileParams()
	{
		return self::getArrayValues($_FILES, func_get_args());
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
		
		if (preg_match($appRegex, $object, $pieces))
		{
			if (count($pieces) < 2)
			{
				Log::message("Could not get '{$object}' [application] URL: not enough data (Should be 'app:AppName/Controller/Action/Arguments').");
				return NULL;
			}

			$pieces = preg_split('/\//', $pieces[1]);

			$appList = Router::getAppList();

			if (!isset($appList[$pieces[0]]))
			{
				Log::message("Could not get '{$object}' [application] URL: there is no such application as '{$pieces[0]}'");
				return NULL;
			}

			$app = $appList[$pieces[0]];
			$controllers = $app->getControllers();

			if (isset($pieces[1]) && isset($controllers[$pieces[1]]))
			{
				if (isset($pieces[2]))
					$result = Router::formatPath($pieces[0], $pieces[1], $pieces[2]); else
						$result = Router::formatPath($pieces[0], $pieces[1]);
			} else
			{
				$result = Router::formatPath($pieces[0]);
			}
		} else
		if (preg_match($fileRegex, $object, $pieces))
		{
			if (!is_array($pieces) || count($pieces) < 2)
			{
				Log::message("Could not get '{$object}' [file] URL: not enough data (should be 'file:FilePath'; FilePath should be relative to the '/media/' folder)");
				return NULL;
			}
				
			$cwd = getcwd();
			chdir(dirname(__FILE__));
				
			$mediaPaths = array('../www/', '../media/');
				
			foreach ($mediaPaths as $dir)
			{
				if (isset($result))
					break;

				// cwd points to current application's directory
				$files = scandir($dir);

				foreach ($files as $f)
				{
					if (file_exists(Router::formatPath($dir . $f)) && $f == $pieces[1])
					{
						$result = $f;
						break;
					}
				}
			}
			
			chdir($cwd);
		}

		if (!isset($result))
		{
			Log::message("Could not find '{$object}' file. Please, make sure argument format is 'file:FilePath' and FilePath is relative to the '/media/' folder.");
		}
		
		return Router::formatPath('/index.php', $result);
	}
}
