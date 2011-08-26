<?php
	class Mooer
	{
		function moo()
		{
			$args = func_get_args();
			$res = '';

			foreach ($args as $arg)
			{
				$res .= "Moo~, $arg, Moo~!<br />";
			}

			return $res;
		}
	}
