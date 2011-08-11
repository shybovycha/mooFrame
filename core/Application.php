<?php
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

	public function getRootRoute()
	{
		if (isset($this->__data['routes']) && is_array($this->__data['routes']) && array_key_exists('root', $this->__data['routes']) && isset($this->__data['routes']['root']))
			return $this->__data['routes']['root'];
	}
}
