<?php
	require_once('Log.php');
	
	class Dispatcher
	{
		private static $__handlers = array();
		
		public static function subscribe($event, $handle)
		{
			// handle should be reviewed
			// like this:
			//   app:<path>
			//   app:<path>:<function>
			//   ext:<path>
			//   ext:<path>:<function>
			// handle examples:
			//   app:Foo/controller/index.php
			//   app:MooApplication/controller/auth.php:AuthController::authorize
			//   ext:Authorizator/Authorizator.php
			//   ext:FileUploader/upload.php:uploadFileTo
			
			return FALSE;
			
			if (!isset($event) || !isset($handle) || !is_callable($handle))
			{
				Log::message("Could not subscribe {$handle} to {$event}.", "Please, verify that {$handle} could be called staticly.");
				
				return FALSE;
			}
			
			if (!isset(self::$__handlers[$event]))
			{
				self::$__handlers[$event] = array($handle);
			} else
			{
				self::$__handlers[$event][] = $handle;
			}
			
			return TRUE;
		}
		
		public static function fire($event)
		{
			if (!isset(self::$__handlers[$event]))
			{
				Log::message("No routines found for {$event} event.");
				
				return FALSE;
			}
			
			$args = array_slice(func_get_args(), 1);
			$res = TRUE;
			
			foreach (self::$__handlers[$event] as $h)
			{
				try
				{
					$res &= (call_user_func_array($h, $args) == TRUE);
				} catch (Exception $e)
				{
					Log::message("Exception caught while trying to fire {$event} event:", $e);
					
					return FALSE;
				}
			}
			
			return $res;
		}
	}
