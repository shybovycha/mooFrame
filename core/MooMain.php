<?php
	require_once('Log.php');
	require_once('Config.php');

	function __MooErrorHandler__($errno, $errstr, $errfile, $errline)
	{
		$errtype = NULL;

		if ($errno == E_USER_ERROR)
			$errtype = 'Error'; else
		if ($errno == E_USER_WARNING)
			$errtype = 'Warning'; else
		if ($errno == E_USER_NOTICE)
			$errtype = 'Notice'; else
				$errtype = 'Error message';

		Log::message("{$errtype} #{$errno}: {$errstr} found in {$errline} line of", $errfile);
	}

	function __MooExceptionHandler__($exception)
	{
		Log::message("Caught an exception:", $exception);
	}

	set_error_handler('__MooErrorHandler__');
	set_exception_handler('__MooExceptionHandler__');

	require_once('Database.php');
	require_once('Router.php');
	require_once('Renderer.php');

	$basedir = Config::get('basedir');
	Config::set('mediadirs', array(
		'media',
		'uploads',
	));