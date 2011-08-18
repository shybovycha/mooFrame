<?php
	require_once('MooMain.php');

	class Application
	{
		private $__data;

		function __construct($name)
		{
			$this->__data = array();
			$this->__data['name'] = $name;
			$this->__data['isDefault'] = FALSE;
			$this->__data['routes'] = array();

			return $this;
		}

		function __call($func, $args)
		{
			$matches = array();

			if (preg_match('/^set(\w+)/', $func, $matches))
			{
				if (is_array($args) && count($args) == 1 && count($matches) == 2)
				{
					$key = $matches[1];
					$key[0] = strtolower($key[0]);

					$this->__data[$key] = $args[0];
				}
			} else
			if (preg_match('/^get(\w+)/', $func, $matches))
			{
				if (count($matches) == 2)
				{
					$key = $matches[1];
					$key[0] = strtolower($key[0]);

					if (array_key_exists($key, $this->__data))
					{
						return $this->__data[$key];
					} else
					{
						return NULL;
					}
				}
			} else
			if (method_exists($this, $func))
			{
				$this->$func($args);
			}

			return $this;
		}

		function getControllers()
		{
			if (!isset($this->__data['controllers']))
			{
				$path = '../app/' . $this->__data['name'] . '/controller/';

				if (!file_exists($path) || !is_dir($path))
					return NULL;

				$controllers = scandir($path);
				$regex = '/^(\w+)\.(php)$/';

				// Remove '.', '..' and non-controller-file entries
				foreach ($controllers as $k => $v)
				{
					if (!file_exists($path . $v) || !preg_match($regex, $v))
					{
						unset($controllers[$k]);
					} else
					{
						$name = preg_replace($regex, '$1', $v);
						$controllers[$name] = $name;
						unset($controllers[$k]);
					}
				}

				$this->__data['controllers'] = $controllers;
			}

			return $this->__data['controllers'];
		}

		function getController($controller)
		{
			if (!isset($controller))
			{
				return NULL;
			}

			$controllerList = $this->getControllers();

			if (!isset($controllerList[$controller]))
			{
				return NULL;
			}

			$controllerFileName = '../app/' . $this->__data['name'] . '/controller/' . $controllerList[$controller] . '.php';

			if (!file_exists($controllerFileName))
				return NULL;

			$cwd = getcwd();
			chdir('../app/' . $this->__data['name'] . '/');

			ob_start();
			include('controller/' . $controllerList[$controller] . '.php');
			$out = ob_get_contents();
			ob_end_clean();

			chdir($cwd);

			if (!class_exists($controllerList[$controller]))
			{
				require('AbstractController.php');
				$res = new AbstractController($controller, $out);

				return $res;
			}

			$controller = new $controllerList[$controller];
			return $controller;
		}

		function dispatch($routingParams)
		{
			if (!isset($routingParams['controller']) || empty($routingParams['controller']))
			{
				$routingParams['controller'] = 'index';
				$controller = $this->getController('index');

				if (!isset($controller))
					return FALSE;
			}

			if (!isset($controller))
				$controller = $this->getController($routingParams['controller']);

			$controllerClassName = get_class($controller);

			if ($controllerClassName != $routingParams['controller'])
			{
				if ($controllerClassName != 'AbstractController')
				{
					return FALSE;
				} else
				{
					echo $controller->getContent();

					return TRUE;
				}
			}

			if (!isset($routingParams['action']) || empty($routingParams['action']) || ($routingParams['action'] != 'index' && !method_exists($controller, $routingParams['action'])))
				$routingParams['action'] = 'index';

			if (!isset($controller) || (isset($routingParams['action']) && !method_exists($controller, $routingParams['action'])))
				return FALSE;

			if (!isset($routingParams['params']))
				$routingParams['params'] = array();

			$cwd = getcwd();
			chdir('../app/' . $this->__data['name'] . '/');

			call_user_func_array(array($controller, $routingParams['action']), $routingParams['params']);

			chdir($cwd);
		}

		function getRootController()
		{
			if (isset($this->__data['routes']) && isset($this->__data['routes']['root']) && isset($this->__data['routes']['root']['controller']))
			{
				$controllerName = $this->__data['routes']['root']['controller'];
				$path = '../app/' . $this->__data['name'] . '/controller/' . $controllerName . '.php';

				if (file_exists($path))
				{
					//ob_start();
					require_once($path);
					$controller = new $controllerName;
					//$logMessage = ob_get_contents();
					//ob_end_clean();
					return $controller;
				}
			}

			return NULL;
		}

		function matchUrl($url)
		{
			if (!isset($url))
				return NULL;

			$res = array();

			foreach ($this->__data['routes'] as $rn => $r)
			{
				if (isset($r['match']))
				{
					if (isset($r['args']))
					{
						foreach ($r['args'] as $k => $v)
						{
							$r['match'] = str_replace(":{$k}", $v, $r['match']);
						}
					}

					$r['match'] = '/' . str_replace("/", "\/", $r['match']) . '/';

					if (preg_match($r['match'], $url))
						$res[] = $r;
				}
			}

			return $res;
		}

		function matchRoutingParams($routingParams)
		{
			if (isset($routingParams['application']) && $this->__data['name'] == $routingParams['application'])
			{
				if (!isset($routingParams['controller']) && !isset($routingParams['action']))
				{
					return TRUE;
				} else
				if (!isset($routingParams['controller']) && isset($routingParams['action']))
				{
					$controllers = $this->getControllers();

					foreach ($controllers as $c)
					{
						if (isset($routingParams['controller']) && $c != $routingParams['controller'])
						{
							continue;
						}

						$actions = get_class_methods($c);

						if (isset($actions[$routingParams['action']]))
						{
							return TRUE;
						}
					}
				} else
				{
					return TRUE;
				}
			}

			return FALSE;
		}

		function getDefaultAction()
		{
			if (isset($this->__data['routes']) && isset($this->__data['routes']['root']) && isset($this->__data['routes']))
			{
				return $this->__data['routes']['root']['action'];
			}

			return NULL;
		}
	}


/*function __error_handler($errno, $errstr, $errfile = NULL, $errline = NULL)
{
	die($errstr);
}

function __exception_handler($exception)
{
	die('hellom, i am a `' . $exception->getMessage() . '` exception');
}

function __autoload($classname = NULL)
{
	if (!isset($classname))
		throw new Exception("Could not load empty class");

	var_dump(getcwd(), file_exists("$classname.php"));
	//if (file_exists("core/$classname.php"))

	return FALSE;
}

set_error_handler('__error_handler');
set_exception_handler('__exception_handler');

require_once('Config.php');

$GLOBALS['mooframe']['core_path'] = dirname(__FILE__);

chdir($GLOBALS['mooframe']['core_path']);

$a = new MooController();*/

