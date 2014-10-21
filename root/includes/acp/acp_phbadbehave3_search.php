<?php
/**
*
* @package acp
* @version $Id acp_phbadbehave3_search.php
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

class acp_phbadbehave3_search
{
	var $u_action;
	var $new_config;
	function main($id, $mode)
	{	
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$this->page_title = 'ACP_PBB3_TITLE_SEARCH';
		$this->tpl_name = 'acp_phbadbehave3_search';
		
		include($phpbb_root_path.'/bb2.2.x/responses.inc.'.$phpEx);

		$form_key = 'pbb3_search';
		add_form_key($form_key);

		$submit = request_var('submit', 'no');
		if ('no' == $submit)
		{
			$result = $db->sql_query_limit(
				'SELECT t.ip, t.date, t.request_uri, t.user_agent, t.code 
				FROM ' . BAD_BEHAVIOR_TABLE . " AS t 
				WHERE t.code <> '00000000' 
				ORDER BY t.id DESC", 20);
		}
		else
		{
			if (!check_form_key($form_key))
			{
				trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
			else
			{
				$value = $db->sql_escape(request_var('search', ''));
				$field = request_var('field', '');
				$limit = (int) request_var('limit', 20);
				$comparison = request_var('where', '=');
				$order = request_var('order', 'ASC');
				$orderby = request_var('orderby', 'date');
				$columns = array('code', 'ip', 'date', 'request_method', 'request_uri', 'server_protocol', 'user_agent', 'http_headers');
				if (!in_array($field, $columns)
				|| !in_array($orderby, $columns)
				|| !in_array($comparison, array('=', '!=', 'LIKE'))
				|| !in_array($order, array('ASC', 'DESC')))
				{
					trigger_error($user->lang['PBB3_SEARCH'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				else
				{
					$request = 'SELECT t.ip, t.date, t.request_uri, t.user_agent, t.code
						FROM ' . BAD_BEHAVIOR_TABLE . " AS t ";
					if ($value !== '')
					{
						if ($comparison == 'LIKE')
							$value = '%'.$value.'%';

						$request .= " WHERE $field $comparison '$value' ";
					}
					$request .= " ORDER BY $orderby $order";
					$result = $db->sql_query_limit($request, $limit);
				}
			}
		}
		global $config; 
		$i = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			//print_r( $row);
			$template->assign_block_vars('sh_loop', array (
				'IP'		=> $row['ip'],
				'DATE'		=> date($config['default_dateformat'], strtotime($row['date'])),
				'URL'		=> $row['request_uri'],
				'AGENT'		=> $row['user_agent'],
				'KEY'		=> $row['code'],
				'ROW'		=> ($i++) % 2 +1,
				'REASON'	=> bb2_get_response($row['code'])['log']));
		}
		if (0 == $i)
		{
			$template->assign_block_vars('sh_none', array());
		}
		$template->assign_var('U_ACTION', $this->u_action);
	}
}
?>
