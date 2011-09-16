<?php
	class Config
	{
		private static $__data = array();
		private static $__const = array('basedir', 'dbdns', 'dbuser', 'dbpass');

		public static function get($key)
		{
			if (isset($key) && isset(self::$__data[$key]))
				return self::$__data[$key]; else
					return NULL;
		}

		public static function set($key, $value)
		{
			if (isset($key))
			{
				if (!is_array($key))
				{
					if(!array_key_exists($key, self::$__data))
						self::$__data[$key] = $value;
				} else
				{
					foreach ($key as $k => $v)
					{
						if(!array_key_exists($k, self::$__data))
							self::$__data[$k] = $v;
					}
				}
			}
		}

		public static function override($key, $value)
		{
			if (!isset($key))
				return;

			$key = trim($key);

			if (isset(self::$__const[$key]))
				return;

			self::$__data[$key] = $value;
		}
	}

	Config::set('basedir', dirname(dirname(__FILE__)));
	Config::set('logfile', Config::get('basedir') . '/log/frame.log');