<?php
/**
*
* @package umil
* @version $Id install_phbadbehave3.php
* @copyright (c) 2011 philnate <phsoftware.de>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('UMIL_AUTO', true);
define('IN_PHPBB', true);

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}
/*
* The name of the config variable which will hold the currently installed version
* You do not need to set this yourself, UMIL will handle setting and updating the version itself.
*/
$version_config_name = 'pbb3_version';

$language_file = 'mods/umil_phbadbehave_install';

// The name of the mod to be displayed during installation.
$mod_name = 'ACP_PBB3_TITLE_LONG';

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
global $table_prefix;

$versions = array(
	//Version 1.0
	'2.0.0' => array(
		//Add module
		'module_add' => array(
			array('acp', 0, 'ACP_PBB3_MENU'),
			array('acp', 'ACP_PBB3_MENU', 'ACP_PBB3_MENU_GENERAL'),
			array('acp', 'ACP_PBB3_MENU_GENERAL', array (
				'module_basename'	=> 'phbadbehave3_overview',
				'module_langname'	=> 'ACP_PBB3_MENU_OVERVIEW',
				'module_mode'		=> 'overview')),
			array('acp', 'ACP_PBB3_MENU_GENERAL', array (
				'module_basename'	=> 'phbadbehave3_search',
				'module_langname'	=> 'ACP_PBB3_MENU_SEARCH')),
			array('acp', 'ACP_PBB3_MENU_GENERAL', array (
				'module_basename'	=> 'phbadbehave3_settings',
				'module_langname'	=> 'ACP_PBB3_MENU_SETTINGS')),
			array('acp','ACP_PBB3_MENU_GENERAL', array (
				'module_basename'	=> 'phbadbehave3_overview',
				'module_langname'	=> 'ACP_PBB3_MENU_LEGEND',
				'module_mode'		=> 'legend'))
		),
		//Property additions
		'config_add' => array(
			array('pbb3_installed', 'true', 0),
			array('pbb3_logging', 'true', 0),
			array('pbb3_verbose', 'false', 0),
			array('pbb3_strict', 'false', 0),
			array('pbb3_offsite', 'false', 0),
			array('pbb3_httpbl_key', '', 0),
			array('pbb3_httpbl_maxage', 30, 0),
			array('pbb3_httpbl_level', 25, 0),
			array('pbb3_keep_days', 30, 0),
			array('pbb3_keep_amount', 4000, 0),
		),

		'table_add' => array(
			array($table_prefix . 'phbadbehave3', array(
				'COLUMNS' => array(
					'id' => array('UINT', NULL, 'auto_increment'),
					'ip' => array('TEXT', ''),
					'date' => array('CHAR:19', '0000-00-00 00:00:00'),
					'request_method' => array('TEXT', ''),
					'request_uri' => array('TEXT', ''),
					'server_protocol' => array('TEXT', ''),
					'http_headers' => array('TEXT', ''),
					'user_agent' => array('TEXT', ''),
					'request_entity' => array('TEXT', ''),
					'code' => array('TEXT', ''),
				),
				'PRIMARY_KEY'	=> 'id',
				'KEYS'		=> array(
					'pbb3_ip' => array('INDEX', array('ip(15)')),
					'pbb3_useragent' => array('INDEX', array('user_agent(10)')),
				),
			)),

		),

	)

);

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

?>
