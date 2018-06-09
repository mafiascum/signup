<?php
/**
* @package phpBB Extension - Mafiascum Signup
* @copyright (c) 2018 mafiascum.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace mafiascum\signup\includes;

use phpbb\db\driver\factory as database;

class gameManager{

	/** @var \phpbb\db\driver\factory */
	protected $db;
	/** @var string */
	protected $root_path;
	/** @var string */
	protected $php_ext;
	/**
	 * Constructor of the helper class.
	 *
	 * @param \phpbb\db\driver\factory		$db
	 * @param string						$root_path
	 * @param string						$php_ext
	 *
	 * @return void
	 */

	public function __construct(database $db, $root_path, $php_ext)
		$this->db = $db;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}
	public function generateQueueList($template, $template_block = 'queues', $activeID = 0){
		$sql = 'SELECT * FROM '.MAFIA_GAME_TYPES_TABLE;
		$result = $this->db->sql_query($sql);
		while($queues = $this->db->sql_fetchrow($result))
		{
			$template->assign_block_vars($template_block, array(
				'QUEUE_NAME' 	=> $queues['type_name'],
				'QUEUE_ID'		=> $queues['type_id'],
				'QUEUE_LINK' 	=> append_sid('viewqueue.' . $this->php_ext . '?q=' . $queues['type_id']),
				'IS_ACTIVE'		=> ($activeID == $queues['type_id']) ? true : false,
			));
		}
		$template->assign_vars(array(
			'REPLACEMENT_LINK' => append_sid('viewqueue.' . $this->php_ext . '?mode=replacement'),
		));
		$this->db->sql_freeresult($result);
	}
	public function bumpIntoSignups($queueID){
		//Find limit by queue
		$ga_ary = array(
			'SELECT'	=> 't.signup_limit',
			'FROM'		=> array(MAFIA_GAME_TYPES_TABLE => 't'),
			'WHERE'		=> 't.type_id = '.(int)$this->db->sql_escape($queueID),
		);
		$sql = $this->db->sql_build_query('SELECT', $ga_ary);
		$res = $this->db->sql_query($sql);
		$info = $this->db->sql_fetchrow($res);
		$this->db->sql_freeresult($res);

		//Find out how many games are currently in signups.
		$sql_ary = array(
			'SELECT'	=> 'COUNT(g.game_id) as count',
			'FROM'		=> array(MAFIA_GAMES_TABLE => 'g'),
			'WHERE'		=> 'g.game_type = '.(int)$this->db->sql_escape($queueID).' AND g.status = '.GAME_PROGRESS_SIGNUPS,
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$res = $this->db->sql_query($sql);
		if($co = $this->db->sql_fetchrow($res))
		{
			$total = ($info['signup_limit'] - $co['count']);
			if($total > 0)
			{
				//Find the game that we will be moving in next.
				$move_ary = array(
					'SELECT'	=> 'g.*',
					'FROM'		=> array(MAFIA_GAMES_TABLE => 'g'),
					'WHERE'		=> 'g.approved_time is NOT NULL AND g.game_type=' . (int)$this->db->sql_escape($queueID) . ' AND status = '.GAME_PROGRESS_QUEUED,
					'ORDER_BY'	=> 'g.approved_time ASC',
				);
				$sql = $this->db->sql_build_query('SELECT', $move_ary);

				$result = $this->db->sql_query_limit($sql, 1);
				//Make sure we actually have a game to bump.
				if($game = $this->db->sql_fetchrow($result))
				{
					//Move preins to entered players.
					$p_ary = array('type' => STANDARD_IN);
					
					$sql = 'UPDATE ' . MAFIA_PLAYERS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $p_ary) . '
					WHERE game_id=' . $game['game_id'] .'
					AND type = 1';		
				
					$this->db->sql_query($sql);
					
					//Finally update the games.
					$sql = 'UPDATE ' . MAFIA_GAMES_TABLE . ' SET
						entered_players = requested_players,
						requested_players = 0,
						status = '. GAME_PROGRESS_SIGNUPS.'
						WHERE game_id=' . $game['game_id'];

						$this->db->sql_query($sql);
				}
				$this->db->sql_freeresult($result);
			}
		}
	}
}
?>