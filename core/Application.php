<?php
require_once('Renderer.php');
require_once('Router.php');

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
	
	function getControllers()
	{
		if (!isset($this->__data['controllers']))
		{
			$path = '../app/' . $this->__data['name'] . '/controller/';
			
			$controllers = scandir($path);
			$regex = '/^(\w+)(Controller)\.(\w+)$/';
			
			// Remove '.', '..' and non-controller-file entries
			foreach ($controllers as $k => $v)
			{
				if (!file_exists($path . $v) || !preg_match($regex, $v))
				{
					unset($controllers[$k]);
				} else
				{
					$name = preg_replace($regex, '$1', $v);
					$controllers[$name] = $name . 'Controller';
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
		
		require_once('../app/' . $this->__data['name'] . '/controller/' . $controllerList[$controller] . '.php');
		return new $controllerList[$controller];
	}
	
	function dispatch($routingParams)
	{
		if (!isset($routingParams['controller']) || empty($routingParams['controller']))
		{
			$controller = $this->getController('index');

			if (!isset($controller))
				return FALSE;
		}

		if (!isset($controller))
			$controller = $this->getController($routingParams['controller']);

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

	function getRootRoute()
	{
		if (isset($this->__data['routes']) && is_array($this->__data['routes']) && array_key_exists('root', $this->__data['routes']) && isset($this->__data['routes']['root']))
			return $this->__data['routes']['root'];
	}
	
	function getRootController()
	{
		if (isset($this->__data['routes']) && isset($this->__data['routes']['root']) && isset($this->__data['routes']['root']['controller']))
		{
			$controllerName = $this->__data['routes']['root']['controller'] . 'Controller';
			$path = '../app/' . $this->__data['name'] . '/controller/' . $controllerName . '.php';
			
			if (file_exists($path))
			{
				require_once($path);
				return new $controllerName;
			}
		}
		
		return NULL;
	}
	
	function getDefaultAction()
	{
		if (isset($this->__data['routes']) && isset($this->__data['routes']['root']) && isset($this->__data['routes']['root']['action']))
		{
			return $this->__data['routes']['root']['action'];
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

