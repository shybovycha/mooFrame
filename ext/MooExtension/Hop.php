<?php
	function hop($x)
	{
		return "Hop, $x, hop!";
	}

	function quote()
	{
		$args = func_get_args();
		$res = '';
		
		foreach ($args as $v)
		{
			$res .= "<i>$v</i><br />";
		}
		
		return $res;
	}

	function compareArgs()
	{
		$args = func_get_args();
		$n = count($args);
		$r = NULL;
		
		for ($i = 0; $i < $n; $i += 2)
			if (isset($r))
				$r &= ($args[$i] == $args[$i + 1]); else
					$r = ($args[$i] == $args[$i + 1]);
		
		return $r;
	}
