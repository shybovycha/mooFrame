<?php
	class Log
	{
		public static function dump()
		{
			$args = func_get_args();

			if (count($args) == 1)
				$args = $args[0];

			ob_start();

			var_dump($args);

			$res = trim(ob_get_contents());
			ob_end_clean();

			return $res;
		}

		public static function message($message)
		{
			$logFilename = Config::get('logfile');
			$args = func_get_args();
			$f = NULL;

			if (file_exists($logFilename))
			{
				$f = fopen($logFilename, 'a');
			} else
			{
				try
				{
					$f = fopen($logFilename, 'w');
				} catch (Exception $e)
				{
					// ...
				}
			}

			fprintf($f, "\n========\n%s\n", date('H:i:s') . "\t" . date('d-m-Y'));

			foreach ($args as $k => $v)
			{
				if (is_string($v))
					fprintf($f, "%s\n", $v); else
						fprintf($f, "%s\n", Log::dump($v));
			}

			fprintf($f, "\n");

			fclose($f);
		}

		public static function trace()
		{
			$logFilename = Config::get('logfile');
			$args = func_get_args();
			$f = NULL;

			$trace = debug_backtrace();

			if (file_exists($logFilename))
			{
				$f = fopen($logFilename, 'a');
			} else
			{
				try
				{
					$f = fopen($logFilename, 'w');
				} catch (Exception $e)
				{
					// ...
				}
			}

			fprintf($f, "\n========\n%s\n", date('H:i:s') . "\t" . date('d-m-Y'));

			foreach ($trace as $t)
			{
				//fprintf($f, "%s\n", self::dump($t));

				fprintf($f, "%s\n", $t['file'] . '  :  ' . $t['line']);

				if (isset($t['function']) && isset($t['class']) && isset($t['type']) && isset($t['args']))
				{
					fprintf($f, "%s\n", ">>   {$t['class']}{$t['type']}{$t['function']} (" . self::dump($t['args']) . ")");
				} else
				if (isset($t['function']) && isset($t['args']))
				{
					fprintf($f, "%s\n", ">>   {$t['function']} (" . self::dump($t['args']) . ")");
				} else
				if (isset($t['function']))
				{
					fprintf($f, "%s\n", ">>   {$t['function']} ()");
				}
			}

			fprintf($f, "\n");

			fclose($f);
		}
	}
