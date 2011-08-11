<?php
require_once('Application.php');

class Router
{
	private $__applicationList = NULL, $__defaultApplication = NULL;

	public function redirect($url = NULL)
	{
	}

	public function httpError($error = NULL)
	{
	}

	public function isApplicationListLoaded()
	{
		return (isset($this->__applicationList) && is_array($this->__applicationList));
	}

	public function getApplicationList()
	{
		if (!isset($this->__applicationList) || !is_array($this->__applicationList))
			$this->loadApplicationList();

		return $this->__applicationList;
	}

	public function getDefaultApplication()
	{
		if (!isset($this->__defaultApplication))
		{
			if (!$this->isApplicationListLoaded())
			{
				$this->loadApplicationList();
			}

			foreach ($this->__applicationList as $v)
			{
				if ($v->getIsDefault())
				{
					$this->__defaultApplication = $v;
					break;
				}
			}
		}

		return $this->__defaultApplication;
	}

	public function loadApplicationList()
	{
		//$_SERVER['REQUEST_URI'];

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
					include($path . '/etc/routes.php');

					if (isset($routes))
						$instance->setRoutes($routes);

					unset($routes);

					if (isset($isDefault) && $isDefault === TRUE)
					{
						$instance->setIsDefault(TRUE);

						unset($isDefault);
					}
				}

				$this->__applicationList[$app] = $instance;

				unset($instance);
			}
		}

		#var_dump(array('applications avaliable' => $applications, 'routes avaliable' => $routeConfig, 'default applications' => $defaultConfig));
	}

	public function route()
	{
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
			return $_POST;
		}
	}

	public function getParams()
	{
	}

	public function getPostParams()
	{
		return $this->getArrayValues($_POST, func_get_args());
	}

	public function getFileParams()
	{
		return $this->getArrayValues($_FILES, func_get_args());
	}
	
	public function getUrl($controller, $action = NULL)
	{
		if (!isset($controller) || method_exists($controller, $action))
		{
			return NULL;
		}
		
		if (!isset($action))
		{
		}
		
		return NULL;
	}
}
