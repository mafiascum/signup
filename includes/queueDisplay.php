<?php
/**
* @package phpBB Extension - Mafiascum Signup
* @copyright (c) 2018 mafiascum.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace mafiascum\signup\includes;

use phpbb\db\driver\factory as database;

class queueDisplay{

	/** @var \phpbb\db\driver\factory */
	protected $db;
	/** @var string */
	protected $root_path;
	/** @var string */
	protected $php_ext;
	/** @var \phpbb\db\driver\factory */
	protected $template;
	/** @var string */
	protected $pagination;
	/**
	 * Constructor of the helper class.
	 *
	 * @param \phpbb\db\driver\factory		$db
	 * @param string						$root_path
	 * @param string						$php_ext
	 *
	 * @return void
	 */

	public function __construct(database $db, $root_path, $php_ext, $template, $pagination){
		$this->db = $db;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->template = $template;
		$this->pagination = $pagination;
	}
	public function buildQueueBreadCrumbs($gameID, $queueID){
		$this->template->assign_block_vars('navlinks', array(
					'FORUM_NAME'         => $user->lang['QUEUES'],
					'U_VIEW_FORUM'      => append_sid("{$phpbb_root_path}viewqueue.$phpEx"))
		);
		if($gameID)
		{
			$sql_ary = array(
				'SELECT'	=> 'g.name, g.game_id, t.type_name, t.type_id',
				'FROM'		=> array( MAFIA_GAMES_TABLE => 'g'),
				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array(MAFIA_GAME_TYPES_TABLE => 't'),
						'ON'	=> 'g.game_type = t.type_id')),
				'WHERE'		=> 'g.game_id = '.$this->db->sql_escape($gameID)
				);
				$sql = $this->db->sql_build_query('SELECT', $sql_ary);
				$res = $this->db->sql_query($sql);
				$game = $this->db->sql_fetchrow($res);
				$this->db->sql_freeresult($res);
			
			//Assign queue breadcrumb.
			$this->template->assign_block_vars('navlinks', array(
						'FORUM_NAME'         => sprintf($user->lang['SINGLE_QUEUE'], $game['type_name']),
						'U_VIEW_FORUM'      => append_sid($phpbb_root_path.'viewqueue.'.$this->php_ext.'?q='.$game['type_id']))
			);
			//Assign game breadcrumb.
			$this->template->assign_block_vars('navlinks', array(
						'FORUM_NAME'         => $game['name'],
						'U_VIEW_FORUM'      => append_sid($phpbb_root_path.'viewgame.'.$this->php_ext.'?g='.$game['game_id']))
			);
		}
		else if($queueID)
		{
			$sql_ary = array(
				'SELECT'	=> ' t.type_name, t.type_id',
				'FROM'		=> array( MAFIA_GAME_TYPES_TABLE => 't'),
				'WHERE'		=> 't.type_id = '.$this->db->sql_escape($queueID)
				);
				$sql = $this->db->sql_build_query('SELECT', $sql_ary);
				$res = $this->db->sql_query($sql);
				$queue = $this->db->sql_fetchrow($res);
				$this->db->sql_freeresult($res);
				
			//Assign queue breadcrumb.
			$this->template->assign_block_vars('navlinks', array(
						'FORUM_NAME'         => sprintf($user->lang['SINGLE_QUEUE'], $queue['type_name']),
						'U_VIEW_FORUM'      => append_sid($phpbb_root_path.'viewqueue.'.$this->php_ext.'?q='.$queue['type_id']))
			);
		}
	}
}
?>