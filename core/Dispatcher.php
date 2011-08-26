<?php
	require_once('Log.php');
	
	class Dispatcher
	{
		private static $__handlers = array();
		
		public static function subscribe($event, $handle)
		{
			// handle format:
			//   app:<path>
			//   app:<path>:<function>
			//   ext:<path>
			//   ext:<path>:<function>
			// handle examples:
			//   app:Foo/controller/index.php
			//   app:MooApplication/controller/auth.php:AuthController::authorize
			//   ext:Authorizator/Authorizator.php
			//   ext:FileUploader/upload.php:uploadFileTo
			
			$cwd = getcwd();
			chdir(dirname(__FILE__));
			chdir('../');
			
			$pieces = preg_split('/\b:\b/', $handle);
			
			if (count($pieces) < 2)
			{
				Log::message("Not enough arguments to subscribe for event.", Log::trace());
				
				chdir($cwd);
				
				return FALSE;
			}
			
			if ($pieces[0] == 'app' || $pieces[0] == 'ext')
			{
				$pieces[1] = $pieces[0] . '/' . $pieces[1];
				
				if (!file_exists($pieces[1]) || !is_file($pieces[1]))
				{
					Log::message("Could not subscribe to {$event} with {$handle} because {$pieces[1]} file could not be found.");
					
					chdir($cwd);
					
					return FALSE;
				}
				
				$handler = array('file' => NULL);
				
				if (isset($pieces[2]))
				{
					$handler['function'] = $pieces[2];
				}
				
				if (!isset(self::$__handlers[$event]))
				{
					self::$__handlers[$event] = array($handler);
				} else
				{
					self::$__handlers[$event][] = $handler;
				}
			} else
			{
				Log::message("Could not understand event handler format for {$handle}.", "Please, verify that handle matches one of these:", "app:<path>", "app:<path>:<function>", "ext:<path>", "ext:<path>:<function>");
				
				chdir($cwd);
				
				return FALSE;
			}
			
			chdir($cwd);
			
			return TRUE;
		}
		
		public static function fire($event)
		{
			if (!isset(self::$__handlers[$event]))
			{
				Log::message("No routines found for {$event} event.", "Routines registered:", Log::dump(self::$__handlers));
				
				return FALSE;
			}
			
			$cwd = getcwd();
			chdir(dirname(__FILE__));
			chdir('../');
			
			$args = array_slice(func_get_args(), 1);
			$res = TRUE;
			
			foreach (self::$__handlers[$event] as $k => $h)
			{
				try
				{
					if (!isset($h['file']) || !file_exists($h['file']))
					{
						unset(self::$__handlers[$event][$k]);
						
						if (Config::get('dispatcherFailOnNonExist'))
							$res &= FALSE;
						
						continue;
					}
					
					ob_start();
					
					include_once($h['file']);
					
					if (isset($h['function']))
					{
						$res &= (call_user_func_array($h, $args) == TRUE);
					}
					
					ob_end_clean();
				} catch (Exception $e)
				{
					Log::message("Exception caught while trying to fire {$event} event:", $e);
					
					chdir($cwd);
					
					return FALSE;
				}
			}
			
			chdir($cwd);
			
			return $res;
		}
	}
