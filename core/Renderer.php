<?php
	require_once('Log.php');
	
	class __renderer__
	{
		private $__data = array();

		public function __set($key, $value)
		{
			$this->__data[$key] = $value;
		}

		public function __get($key)
		{
			return (isset($this->__data[$key]) ? $this->__data[$key] : NULL);
		}

		public function partial($file, $args = NULL)
		{
			$renderer = new self();
			return $renderer->render($file, $args);
		}

		public function render($file, $args = NULL)
		{
			if (!file_exists($file))
			{
				Log::message("Could not find '{$file}' template file", "cwd:", getcwd());
				Log::trace();
				
				return NULL;
			}

			if (isset($args) && is_array($args))
				$this->__data = $args;

			ob_start();
			include($file);
			$res = ob_get_contents();
			ob_end_clean();

			return $res;
		}
	}

	class Renderer
	{
		private static $__renderer;

		private static function getRenderer()
		{
			if (!isset(self::$__renderer))
				self::$__renderer = new __renderer__();

			return self::$__renderer;
		}

		public static function render($file, $args = NULL)
		{
			$r = self::getRenderer();
			echo $r->render($file, $args);
		}

		public static function partial($file, $args = NULL)
		{
			$r = self::getRenderer();
			return $r->partial($file, $args);
		}
	}