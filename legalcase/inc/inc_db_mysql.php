<?php

/*
	This file is part of the Legal Case Management System (LCM).
	(C) 2004-2005 Free Software Foundation, Inc.

	This program is free software; you can redistribute it and/or modify it
	under the terms of the GNU General Public License as published by the
	Free Software Foundation; either version 2 of the License, or (at your
	option) any later version.

	This program is distributed in the hope that it will be useful, but
	WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
	or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
	for more details.

	You should have received a copy of the GNU General Public License along
	with this program; if not, write to the Free Software Foundation, Inc.,
	59 Temple Place, Suite 330, Boston, MA  02111-1307, USA

	$Id$
*/

if (defined('_INC_DB_MYSQL')) return;
define('_INC_DB_MYSQL', '1');

if (! function_exists("mysql_query"))
	die("ERROR: MySQL is not correctly installed. Verify that the php-mysql
	module is installed and that the php.ini has something similar to
	'extension=mysql.so'. Refer to the user's manual FAQ for more information.");

//
// SQL query functions
//

function lcm_sql_server_info() {
	return "MySQL " . @mysql_get_server_info();
}

function lcm_query_db($query, $accept_fail = false) {
	global $lcm_mysql_link;
	static $tt = 0;

	$my_debug   = $GLOBALS['sql_debug'];
	$my_profile = $GLOBALS['sql_profile'];

	$query = process_query($query);

	if ($my_profile)
		$m1 = microtime();

	if ($GLOBALS['mysql_recall_link'] AND $lcm_mysql_link)
		$result = mysql_query($query, $lcm_mysql_link);
	else 
		$result = mysql_query($query);

	if ($my_debug AND $my_profile) {
		$m2 = microtime();
		list($usec, $sec) = explode(" ", $m1);
		list($usec2, $sec2) = explode(" ", $m2);
		$dt = $sec2 + $usec2 - $sec - $usec;
		$tt += $dt;
		echo "<small>".htmlentities($query);
		echo " -> <font color='blue'>".sprintf("%3f", $dt)."</font> ($tt)</small><p>\n";
	}

	if ($my_debug)
		lcm_log("QUERY: $query\n", "mysql");

	if (lcm_sql_errno() && (!$accept_fail)) {
		$s = lcm_sql_error();
		$error = _T('warning_sql_query_failed') . "<br />\n" . htmlentities($query) . "<br />\n";
		$error .= "&laquo; " . htmlentities($s) . " &raquo;<br />";
		lcm_panic($error);
	}

	return $result;
}

function spip_query_db($query) {
	lcm_log("use of deprecated function: spip_query_db, use lcm_query_db instead");
	return lcm_query_db($query);
}

function lcm_create_table($table, $query) {
	lcm_log("use of deprecated function: lcm_create_table, use lcm_query instead");
	return lcm_query_db('CREATE TABLE '.$GLOBALS['table_prefix'].'_'.$table.'('.$query.')');
}


//
// Process a standard query
// This includes the "prefix" name for the database tables
//
function process_query($query) {
	$db = '';
	$suite = '';

	if ($GLOBALS['mysql_recall_link'] AND $db = $GLOBALS['lcm_mysql_db'])
		$db = '`'.$db.'`.';

	// change the names of the tables ($table_prefix)
	// for example, lcm_case may become foo_case
	if ($GLOBALS['flag_pcre']) {
		if (preg_match('/\s(VALUES|WHERE)\s/i', $query, $regs)) {
			$suite = strstr($query, $regs[0]);
			$query = substr($query, 0, -strlen($suite));
		}
		$query = preg_replace('/([,\s])lcm_/', '\1'.$db.$GLOBALS['table_prefix'].'_', $query) . $suite;
	}
	else {
		if (eregi('[[:space:]](VALUES|WHERE)[[:space:]]', $query, $regs)) {
			$suite = strstr($query, $regs[0]);
			$query = substr($query, 0, -strlen($suite));
		}
		$query = ereg_replace('([[:space:],])lcm_', '\1'.$db.$GLOBALS['table_prefix'].'_', $query) . $suite;
	}

	return $query;
}


//
// Connection to the database
//

function lcm_connect_db($host, $port = 0, $login, $pass, $db = 0, $link = 0) {
	global $lcm_mysql_link, $lcm_mysql_db;	// for multiple connections

	lcm_debug("lcm_connect_db: host = $host, login = $login, pass =~ " . strlen($pass) . " chars", "lcm");

	if (! $login)
		lcm_panic("missing login?");

	if ($link && $db)
		return mysql_select_db($db);

	if ($port > 0) $host = "$host:$port";
	$lcm_mysql_link = @mysql_connect($host, $login, $pass);

	if ($lcm_mysql_link && $db) {
		$lcm_mysql_db = $db;
		return @mysql_select_db($db);
	} else {
		return $lcm_mysql_link;
	}
}

function lcm_connect_db_test($host, $login, $pass, $port = 0) {
	unset($link);

	// Non-silent connect, should be shown in <!-- --> anyway
	if ($port > 0) $host = "$host:$port";
	$link = mysql_connect($host, $login, $pass, $port);

	if ($link) {
		mysql_close($link);
		return true;
	} else {
		return false;
	}
}

function lcm_list_databases($host, $login, $pass, $port = 0) {
	$databases = array();

	if ($port > 0) $host = "$host:$port";
	$link = @mysql_connect($host, $login, $pass, $port);

	if ($link) {
		$result = @mysql_list_dbs();

		if ($result AND (($num = mysql_num_rows($result)) > 0)) {
			for ($i = 0; $i < $num; $i++) {
				$name = mysql_dbname($result, $i);
				if ($name != 'test')
					array_push($databases, $name);
			}
		}

		return $databases;
	} else {
		echo "<!-- NO LINK -->\n";
		return NULL;
	}
}


//
// Fetch the results
//

function lcm_fetch_array($r) {
	if ($r)
		return mysql_fetch_array($r);
}

function lcm_fetch_assoc($r) {
	if ($r)
		return mysql_fetch_assoc($r);
}

function spip_fetch_array($r) {
	lcm_log("use of deprecated function: spip_fetch_array, use lcm_fetch_array instead");
	return lcm_fetch_array($r);
}

function lcm_fetch_object($r) {
	if ($r)
		return mysql_fetch_object($r);
}

function spip_fetch_object($r) {
	lcm_log("use of deprecated function: spip_fetch_object, use lcm_fetch_object instead");
	return lcm_fetch_object($r);
}

function lcm_fetch_row($r) {
	if ($r)
		return mysql_fetch_row($r);
}

function spip_fetch_row($r) {
	lcm_log("use of deprecated function: spip_fetch_row, use lcm_fetch_row instead");
	return lcm_fetch_row($r);
}

function lcm_sql_error() {
	return mysql_error();
}

function lcm_sql_errno() {
	return mysql_errno();
}

function lcm_num_rows($r) {
	if ($r)
		return mysql_num_rows($r);
}

function spip_num_rows($r) {
	lcm_log("use of deprecated function: spip_num_rows, use lcm_num_rows instead");
	return lcm_num_rows($r);
}

function lcm_data_seek($r,$n) {
	if ($r)
		return mysql_data_seek($r,$n);
}

function lcm_free_result($r) {
	if ($r)
		return mysql_free_result($r);
}

function spip_free_result($r) {
	lcm_log("use of deprecated function: spip_free_result, use lcm_free_result instead");
	return lcm_free_result($r);
}

function lcm_insert_id() {
	return mysql_insert_id();
}

function spip_insert_id() {
	lcm_log("use of deprecated function: spip_insert_id, use lcm_insert_id instead");
	return lcm_insert_id();
}

// Put a local lock on a given LCM installation
// [ML] we can probably ignore this
function spip_get_lock($nom, $timeout = 0) {
	global $lcm_mysql_db, $table_prefix;
	if ($table_prefix) $nom = "$table_prefix:$nom";
	if ($lcm_mysql_db) $nom = "$lcm_mysql_db:$nom";

	$nom = addslashes($nom);
	list($lock_ok) = spip_fetch_array(spip_query("SELECT GET_LOCK('$nom', $timeout)"));
	return $lock_ok;
}

function spip_release_lock($nom) {
	global $lcm_mysql_db, $table_prefix;
	if ($table_prefix) $nom = "$table_prefix:$nom";
	if ($lcm_mysql_db) $nom = "$lcm_mysql_db:$nom";

	$nom = addslashes($nom);
	spip_query("SELECT RELEASE_LOCK('$nom')");
}

?>
