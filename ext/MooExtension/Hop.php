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
