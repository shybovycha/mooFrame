<?php
	require_once('Log.php');

	class Database
	{
		private static $__connections = array();

		/*
		 * Connection examples:
		 *
		 * * MySQL:
		 * *	Database::connect("mysql:host=hostname;dbname=database", "dbuser", "dbpassword");
		 *
		 * * PostgreSQL:
		 * *	Database::connect("pgsql:host=localhost;dbname=pdo", "dbuser", "dbpassword");
		 *
		 * More detailed info on using PDO: http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html
		 */
		
		public static function connect($dbdns, $dbuser = NULL, $dbpass = NULL, $options = NULL)
		{
			if (isset(self::$__connections[$dbdns]))
				return self::$__connections[$dbdns];

			$key = 'dbconn' . strval(count(self::$__connections) + 1);

			try
			{
				$pdo = new PDO($dbdns, $dbuser, $dbpass, $options);
				self::$__connections[$key] = $pdo;
			} catch (Exception $e)
			{
				Log::message("Could not establish a DB connection.\nFailed with exception:", $e->getMessage(), "Trace:", $e->getTrace());
			}

			return $key;
		}

		public static function forceQuery($connectionName, $query, $bindings = NULL)
		{
			/* @var $connection PDO */
			$connection = self::$__connections[$connectionName];

			try
			{
				/* @var $query PDOStatement */
				$query = $connection->prepare($query);

				if (isset($bindings) && is_array($bindings))
				{
					foreach ($bindings as $k => $v)
					{
						$query->bindParam($k, $v);
					}
				}
			} catch (Exception $e)
			{
				Log::message("DB querying failed during query preparation and param binding with exception:", $e->getMessage(), "Trace:", $e->getTrace());

				return NULL;
			}

			try
			{
				$connection->beginTransaction();

				if (Config::get('dbLogQuery'))
				{
					Log::message("Trying to execute query:", $query);
				}

				$connection->commit();
			} catch (Exception $e)
			{
				Log::message("DB querying failed during query execution with exception:", $e->getMessage(), "Trace:", $e->getTrace(), "Trying to roll back...");

				try
				{
					$connection->rollBack();
				} catch (Exception $e)
				{
					Log::message("Rolling back DB changes is not possible due to exception:", $e->getMessage(), "Trace:", $e->getTrace());
				}

				return NULL;
			}

			return $query->fetchAll(PDO::FETCH_ASSOC);
		}

		public static function query($connectionName, $query, $bindings = NULL)
		{
			if (!isset($connectionName) || !isset(self::$__connections[$connectionName]))
			{
				Log::message("Could not query DB: no '{$connectionName}' connection found.\nYou should call to `Database::connect()` first\nand use its return value as `connectionName` argument\nfor `Database::query()` or (!!!UNSAFE!!!) `Database::forceQuery()` methods");
				return NULL;
			}

			// check query for some rule set
			// in base case, we should be sure
			// that querying DB would not hurt it.
			// so the entire query should not
			// contain strings in raw format.
			// oh, and empty strings are valid
			
			$filters = array(
				'Quoted raw string values SHOULD NOT be used' => '/\'.+\'/mi',
				'Double-quoted raw string values SHOULD NOT be used' => '/\".+\"/mi'
				);

			foreach ($filters as $k => $regex)
			{
				if (preg_match($regex, $query))
				{
					Log::message("Database could not be queried: query did not match rules ('{$k}' rule) and may hurt DB.\nIf you want to run that query anyway, you may want to call\n\n(!!!UNSAFE!!!)\n\n`Database::forceQuery()`\n\nmethod.");
					return NULL;
				}
			}

			return Database::forceQuery($connectionName, $query, $bindings);
		}
	}
