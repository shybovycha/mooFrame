<?php
	class AbstractController
	{
		private $__data = array();
		
		function __construct($name, $content = NULL)
		{
			$this->__data['name'] = $name;
			$this->__data['content'] = $content;
		}
		
		function __set($key, $value)
		{
			$this->__data[$key] = $value;
		}
		
		function __get($key)
		{
			if (isset($this->__data[$key]))
				return $this->__data[$key];
				
			return NULL;
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
					
					//var_dump($this->__data);

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
	}
