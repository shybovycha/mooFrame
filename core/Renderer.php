<?php
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
					return NULL;

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
			echo $r->partial($file, $args);
		}
	}

	/*$app  = new Renderer();
	echo $app->render('template1.phtml', array('title' => 'hello, world!', 'body' => 'Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam eaque ipsa, quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt, explicabo. Nemo enim ipsam voluptatem, quia voluptas sit, aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos, qui ratione voluptatem sequi nesciunt, neque porro quisquam est, qui dolorem ipsum, quia dolor sit amet, consectetur, adipisci[ng] velit, sed quia non numquam [do] eius modi tempora inci[di]dunt, ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit, qui in ea voluptate velit esse, quam nihil molestiae consequatur, vel illum, qui dolorem eum fugiat, quo voluptas nulla pariatur?'));*/
