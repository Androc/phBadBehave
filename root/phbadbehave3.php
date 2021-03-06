<?php
/* 
 * Based on bad-behavior-generic.php for phBadBehave3
 */

/*
Bad Behavior - detects and blocks unwanted Web accesses
Copyright (C) 2005,2006,2007,2008,2009,2010,2011,2012 Michael Hampton

Bad Behavior is free software; you can redistribute it and/or modify it under
the terms of the GNU Lesser General Public License as published by the Free
Software Foundation; either version 3 of the License, or (at your option) any
later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License along
with this program. If not, see <http://www.gnu.org/licenses/>.

Please report any problems to bad . bots AT ioerror DOT us
http://www.bad-behavior.ioerror.us/
*/
###############################################################################
###############################################################################
define('BB2_CWD', dirname(__FILE__));

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Settings you can adjust for Bad Behavior.
// Most of these are unused in non-database mode.
// DO NOT EDIT HERE; instead make changes in settings.ini.
// These settings are used when settings.ini is not present.
$bb2_settings_defaults = array(
	'log_table' => BAD_BEHAVIOR_TABLE,
	'logging' => ('true' == $config['pbb3_logging'])? true:false,
	'verbose' => ('true' == $config['pbb3_verbose'])? true:false,
	'strict' => ('true' == $config['pbb3_strict'])? true:false,
	'offsite_forms' => ('true' == $config['pbb3_offsite'])? true:false,
	'httpbl_key' => $config['pbb3_httpbl_key'],
	'httpbl_maxage' => $config['pbb3_httpbl_maxage'],
	'httpbl_threat' => $config['pbb3_httpbl_level'],
	'keep_days'  => $config['pbb3_keep_days'],
	'keep_amount' => $config['pbb3_keep_amount'],
	'display_stats' => false,
	'eu_cookie' => false,
	'reverse_proxy' => false,
	'reverse_proxy_header' => 'X-Forwarded-For',
	'reverse_proxy_addresses' => array(),
);

// Bad Behavior callback functions.

// Return current time in the format preferred by your database.
function bb2_db_date()
{
	return date('Y-m-d H:i:s', time());	// Example is MySQL format
}

// Return affected rows from most recent query.
function bb2_db_affected_rows()
{
	global $db;
	return $db->sql_affectedrows();
}

// Escape a string for database usage
function bb2_db_escape($string)
{
	global $db;
	return $db->sql_escape($string);	// No-op when database not in use.
}

// Return the number of rows in a particular query.
function bb2_db_num_rows($result)
{
	if ($result !== FALSE)
		return count($result);
	return 0;
}

// Run a query and return the results, if any.
// Should return FALSE if an error occurred.
// Bad Behavior will use the return value here in other callbacks.
function bb2_db_query($query)
{
	global $db;
	$query = str_replace(array('`', 'key'), array('', 'code'), $query);
	//remove common session id stuff, so different pages showup as same
	return $db->sql_query(preg_replace('#(?<=(\?|&))(sid|PHPSESSID)=[a-fA-F0-9]{32,32}#i', '', $query));
}

// Return all rows in a particular query.
// Should contain an array of all rows generated by calling mysql_fetch_assoc()
// or equivalent and appending the result of each call to an array.
function bb2_db_rows($result) {
	global $db;
	return $db->sql_fetchrowset($result);
}

// Our log table structure
function bb2_table_structure($name)
{
	// It's not paranoia if they really are out to get you.
	$name_escaped = bb2_db_escape($name);
	return "CREATE TABLE IF NOT EXISTS [$name_escaped] (
		id INT(11) NOT NULL auto_increment,
		ip TEXT NOT NULL,
		date DATETIME NOT NULL default '0000-00-00 00:00:00',
		request_method TEXT NOT NULL,
		request_uri TEXT NOT NULL,
		server_protocol TEXT NOT NULL,
		http_headers TEXT NOT NULL,
		user_agent TEXT NOT NULL,
		request_entity TEXT NOT NULL,
		key TEXT NOT NULL,
		INDEX (ip(15)),
		INDEX (user_agent(10)),
		PRIMARY KEY (id) );";	// TODO: INDEX might need tuning
}

// Create the SQL query for inserting a record in the database.
function bb2_insert($settings, $package, $key)
{
	if (!$settings['logging']) return "";
	$ip = bb2_db_escape($package['ip']);
	$date = bb2_db_date();
	$request_method = bb2_db_escape($package['request_method']);
	$request_uri = bb2_db_escape($package['request_uri']);
	$server_protocol = bb2_db_escape($package['server_protocol']);
	$user_agent = bb2_db_escape($package['user_agent']);
	$headers = "$request_method $request_uri $server_protocol\n";
	foreach ($package['headers'] as $h => $v) {
		$headers .= bb2_db_escape("$h: $v\n");
	}
	$request_entity = "";
	if (!strcasecmp($request_method, "POST")) {
		foreach ($package['request_entity'] as $h => $v) {
			$request_entity .= bb2_db_escape("$h: $v\n");
		}
	}
	return "INSERT INTO [" . bb2_db_escape($settings['log_table']) . "]
		(ip, date, request_method, request_uri, server_protocol, http_headers, user_agent, request_entity, key) VALUES
		('$ip', '$date', '$request_method', '$request_uri', '$server_protocol', '$headers', '$user_agent', '$request_entity', '$key')";
}

// Return emergency contact email address.
function bb2_email() {
	global $config;
	return $config['phpBB_email'];
}

// retrieve settings from database
// Settings are hard-coded for non-database use
function bb2_read_settings()
{
	global $bb2_settings_defaults;
	$settings = @parse_ini_file(dirname(__FILE__) . "/settings.ini");
	if (!$settings) $settings = array();
	return @array_merge($bb2_settings_defaults, $settings);
}

// write settings to database
function bb2_write_settings($settings)
{
	return false;
}

// installation
function bb2_install() {
	return false;
}

// Screener
// Insert this into the <head> section of your HTML through a template call
// or whatever is appropriate. This is optional we'll fall back to cookies
// if you don't use it.
function bb2_insert_head()
{
}

// Display stats? This is optional.
function bb2_insert_stats($force = false)
{
//TODO to be done
}

// Return the top-level relative path of wherever we are (for cookies)
// You should provide in $url the top-level URL for your site.
function bb2_relative_path()
{
	global $config;
	return $config['server_name'];
}

//If it fails the protection is done but board will not be down
if (!defined('BB2_VERSION'))
{
	include($phpbb_root_path . "bb2.2.x/core.inc." . $phpEx);
}

global $config;
if (isset($config['pbb3_installed']))
{
	$settings = bb2_read_settings(); 
	bb2_start($settings);
	global $db;
	if ('true' == $settings['log'])
	{
		$db->sql_query(
			'SELECT COUNT(id) AS count 
			FROM ' . BAD_BEHAVIOR_TABLE, 0);
		if (0 == (int)$db->sql_fetchfield('count') % 10)
		{
			if (-1 != (int)$settings['keep_days'])
			{
				$db->sql_query(
					'DELETE 
					FROM ' .BAD_BEHAVIOR_TABLE . ' 
					WHERE date < '. (time() - (int) $settings['keep_days'] * 86400)  .' DAY))');
			}
			if (-1 != (int) $settings['keep_amount'])
			{
				$db->sql_query(
					'SELECT MAX(id) AS max 
					FROM ' . BAD_BEHAVIOR_TABLE);
				$num = (int)$db->sql_fetchfield('max') - (int) $settings['keep_amount'];
				$db->sql_query(
					'DELETE 
					FROM ' . BAD_BEHAVIOR_TABLE . ' 
					WHERE id < ' . $num);
			}
		}
	}
}
?>
