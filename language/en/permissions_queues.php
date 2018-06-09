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
    'ACL_M_QUEUE_1' => 'Can moderate the newbie queue',
	'ACL_M_QUEUE_2' => 'Can moderate the mini normal queue',
	'ACL_M_QUEUE_3' => 'Can moderate the large normal queue',
	'ACL_M_QUEUE_4' => 'Can moderate the mini theme queue',
	'ACL_M_QUEUE_5' => 'Can moderate the large theme queue',
	'ACL_M_QUEUE_6' => 'Can moderate the open queue',
	'ACL_M_QUEUE_7' => 'Can moderate the micro queue',
));
