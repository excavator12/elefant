<?php

/**
 * The database package is just a list of functions that act on
 * a global `$db` PDO object. These provide direct access to the database
 * through a set of convenience functions, and can help reduce
 * direct-to-database logic down to just one or two lines of code.
 *
 * For object modelling, see `lib/Model.php`.
 *
 * Usage:
 *
 *     if (! db_open (array (
 *         'driver' => 'sqlite', 'file' => 'conf/site.db'
 *     ))) {
 *         die (db_error ());
 *     }
 *     
 *     db_execute (
 *         'insert into sometable values (%s, %s)', 'one', 'two'
 *     );
 *
 *     $id = db_lastid ();
 *     
 *     $res = db_fetch_array ('select * from sometable');
 *     foreach ($res as $row) {
 *         echo $row->fieldname;
 *     }
 *     
 *     $row = db_single ('select * from sometable where id = %s', $id);
 *     
 *     $fieldname = db_shift (
 *         'select fieldname from sometable where id = %s', $id
 *     );
 *
 * Note that values inserted in the above way are automatically
 * escaped upon insertion.
 */
function db_open ($conf) {
	global $db, $db_err;
	try {
		switch ($conf['driver']) {
			case 'sqlite':
				$db = new PDO ('sqlite:' . $conf['file']);
				break;
			default:
				if (strstr ($conf['host'], ':')) {
					$conf['host'] = str_replace (':', ';port=', $conf['host']);
				}
				$db = new PDO ($conf['driver'] . ':host=' . $conf['host'] . ';dbname=' . $conf['name'], $conf['user'], $conf['pass']);
		}
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
	$db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->setAttribute (PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
	return true;
}

/**
 * Get the last error message.
 */
function db_error () {
	global $db_err;
	if ($db_err) {
		return $db_err;
	}
	return false;
}

/**
 * Normalizes arguments passed as an array, object, or as
 * multiple extra parameters.
 */
function db_args ($args) {
	if (count ($args) == 0) {
		return null;
	} elseif (count ($args) == 1 && is_object ($args[0])) {
		$res = array ();
		foreach ((array) $args[0] as $arg) {
			$res[] = $arg;
		}
		return $res;
	} elseif (count ($args) == 1 && is_array ($args[0])) {
		$res = array ();
		foreach ($args[0] as $arg) {
			$res[] = $arg;
		}
		return $res;
	}
	return $args;
}

/**
 * Fetch an array of all result objects.
 */
function db_fetch_array ($sql) {
	global $db, $db_err, $db_sql, $db_args;
	$args = func_get_args ();
	array_shift ($args);
	$args = db_args ($args);
	$db_sql = $sql;
	$db_args = $args;

	try {
		$stmt = $db->prepare ($sql);
		$stmt->execute ($args);
		return $stmt->fetchAll ();
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
}

/**
 * Execute a statement and return true/false.
 */
function db_execute ($sql) {
	global $db, $db_err, $db_sql, $db_args;
	$args = func_get_args ();
	array_shift ($args);
	$args = db_args ($args);
	$db_sql = $sql;
	$db_args = $args;

	try {
		$stmt = $db->prepare ($sql);
		return $stmt->execute ($args);
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
}

/**
 * Fetch a single object.
 */
function db_single ($sql) {
	global $db, $db_err, $db_sql, $db_args;
	$args = func_get_args ();
	array_shift ($args);
	$args = db_args ($args);
	$db_sql = $sql;
	$db_args = $args;

	try {
		$stmt = $db->prepare ($sql);
		$stmt->execute ($args);
		return $stmt->fetchObject ();
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
}

/**
 * Fetch the a single value from the first result returned.
 * Useful for `count()` and other such calculations, and when
 * you only need a single piece of information.
 */
function db_shift ($sql) {
	global $db, $db_err, $db_sql, $db_args;
	$args = func_get_args ();
	array_shift ($args);
	$args = db_args ($args);
	$db_sql = $sql;
	$db_args = $args;

	try {
		$stmt = $db->prepare ($sql);
		$stmt->execute ($args);
		$res = (array) $stmt->fetchObject ();
		return array_shift ($res);
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
}

/**
 * Fetch an array of a single field.
 */
function db_shift_array ($sql) {
	global $db, $db_err, $db_sql, $db_args;
	$args = func_get_args ();
	array_shift ($args);
	$args = db_args ($args);
	$db_sql = $sql;
	$db_args = $args;

	try {
		$stmt = $db->prepare ($sql);
		$stmt->execute ($args);
		$res = $stmt->fetchAll ();
		$out = array ();
		foreach ($res as $row) {
			$row = (array) $row;
			$out[] = array_shift ($row);
		}
		return $out;
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
}

/**
 * Fetch an associative array of two fields, the first
 * being the keys and the second being the values.
 */
function db_pairs ($sql) {
	global $db, $db_err, $db_sql, $db_args;
	$args = func_get_args ();
	array_shift ($args);
	$args = db_args ($args);
	$db_sql = $sql;
	$db_args = $args;

	try {
		$stmt = $db->prepare ($sql);
		$stmt->execute ($args);
		$res = $stmt->fetchAll ();
		$out = array ();
		foreach ($res as $row) {
			$row = (array) $row;
			$out[array_shift ($row)] = array_shift ($row);
		}
		return $out;
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
}

/**
 * Get the last inserted id value.
 */
function db_lastid () {
	global $db;
	return $db->lastInsertId ();
}

/**
 * Get the last error message directly from PDO.
 * Useful for queries that were done with the global $db
 * object directly.
 */
function db_last_error () {
	global $db;
	$err = $db->errorInfo ();
	return $err[2];
}

/**
 * Fetch the last SQL statement.
 */
function db_last_sql () {
	return $GLOBALS['db_sql'];
}

/**
 * Fetch the arguments for the last SQL statement.
 */
function db_last_args () {
	return $GLOBALS['db_args'];
}

?>