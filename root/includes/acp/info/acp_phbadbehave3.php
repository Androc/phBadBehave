<?php
/**
*
* @package acp
* @version $Id acp_phbadbehave3.php
* @copyright (c) 2011 philnate <phsoftware.de>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_phbadbehave3_info
{
    function module()
    {
        return array(
            'filename'	=> 'acp_phbadbehave3',
            'title'		=> 'ACP_PBB3_TITLE',
            'version'	=> '2.0.0',
            'modes'		=> array(
            'overview'	=> array('title' => 'ACP_PBB3_OVERVIEW', 'auth' => 'acl_a_', 'cat' => array('')),
			'settings'	=> array('title' => 'ACP_PBB3_SETTINGS', 'auth' => 'acl_a_', 'cat' => array('')),	
            )
        );
    }

    function install()
    {
    }

    function uninstall()
    {
    }
}
?>
