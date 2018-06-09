<?php
/**
*
* @package phpBB Extension - MafiaScum Authentication
* @copyright (c) 2017 mafiascum.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}
	$lang = array_merge($lang, array(
		'GAME_STATUS' 		=>	'Status',
		'MAIN_MOD'			=>	'Mod',
		'GAME_TYPE'			=>	'Game Type',
		'ALL_STATUSES'		=>	'All Statuses',
		'NOT_APPROVED'		=>	'Not Approved',
		'APPROVED'			=>	'Approved',
	));

