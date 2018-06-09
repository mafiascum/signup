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
	public function createGame($userID, $gameName, $gameType, $main_mod, $requested_slots, $game_description = '', $altMods = ''){
		$message = $game_description;
		$allow_bbcode = $allow_smilies = $allow_urls = true;
		generate_text_for_storage($message, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);
		$insertArray = array
		(
			"name"					=> $gameName,
			"description"			=> $message,
			"game_type"				=> $gameType,
			"status"				=> 1, //Pending should ALWAYS default to 1.//TODO
			"maximum_players" 		=> $requested_slots,
			"created_time"			=> time(),
			"main_mod_id"			=> $main_mod,
			"created_by_user_id"	=> $userID,
			"bbcode_uid"			=> $uid,
			"bbcode_bitfield"		=> $bitfield
		);
		
		$sql = 'INSERT INTO ' . MAFIA_GAMES_TABLE . ' ' . $this->db->sql_build_array('INSERT', $insertArray);
		$this->db->sql_query($sql);
		$new_game_id = $db->sql_nextid();
		
		$sql_ary = array(
			'user_id'	=> $main_mod,
			'game_id'	=> $new_game_id,
			'type'		=> 0,
		);
		$sql = 'INSERT INTO ' . MAFIA_MODERATORS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);
		
		return $new_game_id;
	}
	public function loadGame($template, $queueID = 0, $gameID = 0, $moderator = 0, $approval = 0, $status = 0, $start = 0, $limit = 25, $template_block = '', $moderate = false, $gameIDs = array(), $allqueues = false){
		// no result rows greater than 100 per page
		$limit = ($limit > 100) ? 100 : $limit;

		//Build WHERE clause and parameters for pagination.
		$where = $params = '';
		if($gameID) {
			$where .= (empty($where)) ? 'g.game_id = '.$this->db->sql_escape($gameID) : ' AND g.game_id = '.$this->db->sql_escape($gameID);
			
		} else if($queueID){
			$where .= 'game_type = '.$queueID;
			$params .= 'q='.$queueID;
		} else if (sizeOf($gameID)) {
			$ids = join(',',$gameIDs);
			$where .= (empty($where)) ? 'g.game_id IN (' . $ids . ')' : ' AND g.game_id IN (' . $ids . ')' ;
		} else if (!$allqueues) {
			$where .= (empty($where)) ? '1=0' : ' AND 1=0' ;
		}
		
		if($approval)
		{
			$where .= (empty($where)) ? 'approved_time IS NOT NULL' : ' AND approved_time IS NOT NULL';
		} else {
			$params .= (empty($params)) ? 'appr='.$approval : '&amp;appr='.$approval;
		}
		
		if($status)
		{
			if (is_array($status)){
				$statuslist = '(' . $db->sql_escape($status[0]);
				for ($i =1; $i < sizeOf($status); $i++){
					$statuslist .= ',' . $this->db->sql_escape($status[$i]);
				}
				$statuslist .= ')';
				 $where .= (empty($where)) ? 'status IN '. $statuslist : ' AND status IN '. $statuslist ;
				 $params .= (empty($params)) ? 'sta='.$status : '&amp;sta='.$status;
			} else {
				$where .= (empty($where)) ? 'status = '.$this->db->sql_escape($status) : ' AND status = '.$this->db->sql_escape($status);
				$params .= (empty($params)) ? 'sta='.$status : '&amp;sta='.$status;
			}
		} else {
			$params .= (empty($params)) ? 'sta='.$status : '&amp;sta='.$status;
		}
		if($moderator)
		{
			$where .= (empty($where)) ? 'main_mod_id = '.$this->db->sql_escape($moderator) : ' AND main_mod_id = '.$this->db->sql_escape($moderator);
		}
		if($limit)
		{
			$params .= (empty($params)) ? 'limit='.$limit : '&amp;limit='.$limit;
		}
		
		// Build a SQL Query...
		$sql_ary = array(
			'SELECT'    =>  'g.* , c.username as game_creator_name, u.user_id as mod_user_id, u.username as mod_username, a.user_id as app_user_id, a.username as app_username, s.status_id, s.status_name, t.* ',
			'FROM'      => array(MAFIA_GAMES_TABLE => 'g'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'), //Main mod user info.
					'ON'	=> 'u.user_id = g.main_mod_id'),
				array(
					'FROM'	=> array(USERS_TABLE => 'a'), //Approval mod user info.
					'ON'	=> 'a.user_id = g.approved_by_user_id'),
				array(
					'FROM'	=> array(MAFIA_GAME_STATUS_TABLE => 's'), //Status info.
					'ON'	=> 's.status_id = g.status'),
				array(
					'FROM'	=> array(MAFIA_GAME_TYPES_TABLE => 't'), //Type info.
					'ON'	=> 't.type_id = g.game_type'),
				array(
					'FROM'	=> array(USERS_TABLE => 'c'), //creator user info
					'ON'	=> 'c.user_id = g.created_by_user_id'),
					),
			'WHERE'		=> $where,	
		);
		
		
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$sql .= ' ORDER BY game_type ASC, approved_time ASC';
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		//Assign all necessary template variables.
		$blockt = ($template_block) ? true : false;
		$data = array();
		while($row = $this->db->sql_fetchrow($result))
		{
			$data[] = $row;
		}
		foreach($data as $game)
		{
			$sql = 'SELECT COUNT(slot_id) AS slots_alive FROM ' . MAFIA_SLOTS_TABLE . ' WHERE game_id=' . $game['game_id'] . ' AND slot_status=' . SLOT_ALIVE;
			$result = $this->db->sql_query_limit($sql, 1);
			$temp_var = $this->db->sql_fetchrow($result);
			$game['slots_alive'] = $temp_var['slots_alive']; 
			$this->assignGameVars($game, $blockt, $template_block);
		}
		$this->db->sql_freeresult($result);
		
			$sql = 'SELECT count(game_id) as num_games FROM ' . MAFIA_GAMES_TABLE . ' g ' . (empty($where) ? '' : ('WHERE ' . $where));
			$result = $this->db->sql_query($sql);
			$data2 = $this->db->sql_fetchrow($result);

			$count = $data2['num_games'];
			$pagination_url = append_sid($this->root_path . 'viewqueue.' . $this->php_ext, $params);
			// Assign the pagination variables to the template.
			$template->assign_vars(array(
				'PAGINATION'        => generate_pagination($pagination_url, $count, $limit, $start),
				'PAGE_NUMBER'       => on_page($count, $limit, $start),
				'TOTAL_GAMES'		=> $count,
			));
		
		
		//Return the game dataset for other uses.
		//If there is only one game, only return that entry, otherwise the full game array.
		if(sizeof($data) > 1)
		{
			return $data;
		}
		else
		{
			return $data[0];
		}
	}
	public function assignGameVars($template, $game, $block = false, $block_name = 'games'){
		if(!$block){
			//build game type select element
			$gameType = $game['game_type'];
			$typeSelect = '<select id="gameInfoGameTypeInputField" name="gameInfoGameType" class="gameInfoLaben"';
			$typeSelect .='>';

			$sql = " SELECT *"
				 . " FROM " . MAFIA_GAME_TYPES_TABLE;

			$result = $this->db->sql_query($sql);

			while($row = $this->db->sql_fetchrow($result)){
				$typeSelect .= '<option value="';
				$typeSelect .= $row['type_id'];
				$typeSelect .= '"';
				if ($row['type_id'] == $gameType){
					$typeSelect .= ' selected ';
					$gameType = $row['type_name'];
					$forum_id = $row['forum_id'];
				}
				$typeSelect .= '>';
				$typeSelect .= $row['type_name'];
				$typeSelect .= '</option>';
			}
			$this->db->sql_freeresult($result);

			$typeSelect .= '</select>';

			//build game status select element
			$gameStatusVal = $game_data['status'];
			
			$statusSelect = '<select id="gameInfoGameStatusInputField" name="gameInfoGameStatus" class="gameInfoLabel"';
			$statusSelect .='>';
			$sql = " SELECT *"
				 . " FROM " . MAFIA_GAME_STATUS_TABLE;

			$result = $this->db->sql_query($sql);
			$statusOptions = $auth->acl_get('u_queue_'.$game['game_type']) ? 0 : 3;
			while($row = $this->db->sql_fetchrow($result)){
				if ($row['status_id'] >$statusOptions){
					$statusSelect .= '<option value="';
					$statusSelect .= $row['status_id'];
					$statusSelect .= '"';
				}
				if ($row['status_id'] == $game['status'] ){
						$statusSelect .= ' selected ';
				}
				if ($row['status_id'] >$statusOptions){
					$statusSelect .= '>';
					$statusSelect .= $row['status_name'];
					$statusSelect .= '</option>';
				}
			}
			$db->sql_freeresult($result);
			$statusSelect .= '</select>';
		}
		$bbcode_bitfield = $bbcode_bitfield | base64_decode($game['bbcode_bitfield']);
		
		// Instantiate BBCode if need be
		if ($bbcode_bitfield !== '')
		{
			$bbcode = new bbcode(base64_encode($bbcode_bitfield));
		}
		$message = censor_text($game['description']);

		// Second parse bbcode here
		if ($game['bbcode_bitfield'])
		{
			$bbcode->bbcode_second_pass($message, $game['bbcode_uid'], $game['bbcode_bitfield']);
		}

		$message = bbcode_nl2br($message);
		$message = smiley_text($message);
		
		$sql = 'SELECT s.slot_id FROM ' . MAFIA_SLOTS_TABLE . ' s LEFT JOIN ' . MAFIA_PLAYERS_TABLE . ' p ON p.slot_id = s.slot_id AND p.game_id = s.game_id AND p.type<>5 WHERE p.player_id IS NULL AND s.game_id=' . $game['game_id'] .' LIMIT 1';
		$result = $this->db->sql_query($sql);
		$slot = $this->db->sql_fetchrow($result);
		if ($slot['slot_id']){
			$replace = true;
		}
		$gamevars = array(
			'GAME_NAME'		=> $game['name'],
			'GAME_TYPE'	=> $game['type_name'],
			'GAME_ID'	=> $game['game_id'],
			'CREATOR_NAME' 	=> get_username_string('full', $game['created_by_user_id'], $game['game_creator_name']),
			'GAME_LINK' => append_sid('viewgame.' . $php_ext . '?g=' . $game['game_id']),
			'PREIN_TOTAL'	=> $game['requested_players'],
			'ENTERED_PLAYER_TOTAL'	=> $game['entered_players'],
			'MAXIMUM_PLAYER_TOTAL'	=> $game['maximum_players'],
			'MAXIMUM_LIMIT'			=> $game['max_players'], //Maximum players available for this game type.
			'GAME_PROGRESS'			=> $game['status_id'] != 1 ? (($game['status_id'] == 2 || $game['status_id'] == 3) ? 'Signup progress: ' .$game['entered_players'] . '/' . $game['maximum_players'] : 'Game progress: ' . $game['slots_alive'] . '/' . $game['maximum_players']) : '',
			'AVAILABLE_REPLACEMENT_TOTAL'	=> $game['replacements'],
			'STATUS'	=> $game['status_name'],
			'STATUS_ID'	=> $game['status_id'],
			'STATUS_ALTERNATE'	=> $game['status_alternate'],
			'GAME_DESCRIPTION'	=> $message,
			'APPROVAL_USER'		=> get_username_string('full', $game['app_user_id'], $game['app_username']),
			'MAIN_MODERATOR'	=> get_username_string('full', $game['mod_user_id'], $game['mod_username']),
			'SUBMISSION_USER'	=> 'ADD_THIS',
			'CREATION_TIME'		=> !empty($game['created_time'])? strftime("%Y-%m-%d",$game['created_time']) : "", //TODO - Nicely format datetime.
			'APPROVAL_TIME'		=> !empty($game['approved_time'])? strftime("%Y-%m-%d",$game['approved_time']) : "", //TODO - Nicely format datetime.
			'STARTED_TIME'		=> !empty($game['started_time'])? strftime("%Y-%m-%d",$game['started_time']) : "", //TODO - Nicely format datetime.
			'COMPLETED_TIME'	=> !empty($game['completed_time'])? strftime("%Y-%m-%d",$game['completed_time']) : "", //TODO - Nicely format datetime.
			'IS_APPROVED'		=> ($game['approved_time']) ? true : false,
			'IS_STARTED'		=> ($game['started_time']) ? true : false,
			'APPROVAL_LINK'		=> ($game['approved_time']) ? '' : '<a href="'. append_sid("viewqueue." .$php_ext. '?mode=approve&amp;g=' .$game['game_id']) . '">',
			'STATUS_IMG'		=> $user->img('forum_unread_locked', '', false, '', 'src'),
			'IS_ONGOING'		=> ($game['status'] == GAME_PROGRESS_ONGOING) ? true : false,
			'STATUS_VAL'		=> $gameStatusVal,
			'IN_GAME' 				=> $this->checkPlayer($game['game_id'], $user->data['user_id'], 0),
			'U_EDITFORM'			=> append_sid("{$this->root_path}viewgame.$php_ext?g=" . $game['game_id']),
			'TYPE_SELECT'			=> $typeSelect,
			'STATUS_SELECT'			=> $statusSelect,
			'REPLACE'				=> $replace,
			'GAME_TOPIC'			=> empty($game['topic_id']) ? "" : $this->root_path . "viewtopic.$php_ext?t=" . $game['topic_id'],
		);
		if($block)
		{
			$template->assign_block_vars($block_name, $gamevars);
		}
		else
		{
			$template->assign_vars($gamevars);
		}
	}
	public function checkModLimits(){
		return true;
	}
	public function checkPlayerLimits(){
		return true;
	}
	public function errorsInGameData($data, $user){
		$errors = array(); 
	
		//Check moderator and their limits.
		$mod = $this->checkModerator($data['main_mod']);
		if(!$mod)
		{
			$errors[] = $user->lang['BAD_MOD_SELECTED'];
		}
		else
		{
			if(!$this->checkModLimits($data['main_mod'], $data['game_type']))
			{
				$errors[] = $user->lang['MOD_QUEUE_LIMITS']; 
			}
		}
			
		//Validate game name.
		if(!$data['game_name']) 
		{
			$errors[] = $user->lang['MISSING_GAME_NAME'];
		}
		if(strlen($data['game_name'] ) > 80)
		{
			$errors[] = $user->lang['GAME_NAME_TOO_LONG'];
		}
		
		//Validate game type.
		if(!$data['game_type'])
		{
			$errors[] = $user->lang['MISSING_GAME_TYPE'];
		} else {
			if(!$data['requested_slots'])
			{
				$errors[] = $user->lang['MISSING_REQUESTED_SLOTS'];
			}
			else
			{
				$sql = 'SELECT * FROM phpbb_mafia_game_types WHERE type_id=' . $data['game_type'];
				$res = $this->db->sql_query($sql);
				$game_type_data = $this->db->sql_fetchrow($res);
				if($data['requested_slots'] < $game_type_data['min_players'])
				{
					$errors[] = $user->lang['NOT_ENOUGH_REQUESTED_SLOTS'];
				} else if ($data['requested_slots'] > $game_type_data['max_players']){
					$errors[] = $user->lang['TOO_MANY_SLOTS'];
				}
			}
		}
		
		return $errors;
		
	}
	public function checkModerator($modName, $getID = false){
		$checkusernameArray = array($modName);
		$user_id_ary = array();
		user_get_id_name($user_id_ary, $checkusernameArray);

		if(sizeof($user_id_ary) && $getID) {
			return $user_id_ary[0]; }
		elseif(sizeof($user_id_ary)) {
			return true; }
		else { return false; }
	}
	public function addPlayer($gameID, $userID, $type = 0, $replacement_start = null){
		if ($type == 1 || $type == 2 || $type == 0)
		{
			//Insert the player into the table.
			$player_ary = array(
				'game_id'	=> (int) $gameID,
				'user_id'	=> (int) $userID,
				'type'		=> (int) $type,
				'replacement_start'	=> $replacement_start,
			);
			
			$sql = 'INSERT INTO ' . MAFIA_PLAYERS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $player_ary);
			$this->db->sql_query($sql);
			$this->db->sql_freeresult($result);
			//Update game player counts if we have successfully added a player.
			switch($type)
			{
				case STANDARD_IN:
					$ptype = 'requested_players';
					break;
				case PREIN:
					$ptype = 'requested_players';
					break;
				case REPLACEMENT:
					$ptype = 'replacements';
					break;
			}
			
			$sql = 'UPDATE ' . MAFIA_GAMES_TABLE . '
					SET ' .$this->db->sql_escape($ptype). ' = '.$this->db->sql_escape($ptype).' + 1
					WHERE game_id = ' . (int)$this->db->sql_escape($gameID);
			$this->db->sql_query($sql);
		}
		else if ($type == 3)
		{
			//update player table
			$sql = 'UPDATE phpbb_mafia_players p
				SET p.type=' . APPROVED_IN . '
				WHERE p.user_id = '.$this->db->sql_escape($userID).'
				AND p.game_id = '.$this->db->sql_escape($gameID);
			$this->db->sql_query($sql);
			//update game table
			$sql = 'UPDATE phpbb_mafia_games g
				SET g.entered_players = g.entered_players + 1
				WHERE g.game_id = '.$this->db->sql_escape($gameID);
			$this->db->sql_query($sql);
		}
	}
	public function addSlot($playerID, $slotID, $gameID, $manual = false, $replace = false){
		//Insert the slot.
		$sql = 'SELECT * FROM ' . MAFIA_SLOTS_TABLE . ' WHERE slot_id=' . $slotID . ' AND game_id=' . $gameID;
		$result = $this->db->sql_query($sql);
		$slot = $this->db->sql_fetchrow($result);
		if (sizeOf($slot))
		{
		
		}
		else
		{	
			$slot_ary = array(
				'game_id'	=> (int)$this->db->sql_escape($game_id),
				'slot_id' 	=> (int)$this->db->sql_escape($slot_id)
			);
				
			$sql = 'INSERT INTO ' . MAFIA_SLOTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $slot_ary);
			$this->db->sql_query($sql);
			$this->db->sql_freeresult($result);
		}
		$player_ary = array(
				'slot_id'	=> $slotID,
			);
		//Update game player counts.
		$sql = 'UPDATE ' . MAFIA_GAMES_TABLE . '
				SET '.(($manual) ? '' : 'requested_players = requested_players - 1,') .'
				entered_players = entered_players + 1 
				WHERE game_id = ' . (int)$this->db->sql_escape($gameID);
		$this->db->sql_query($sql);
		//Update the player table with the proper slot id.
		
		
		$sql = 'UPDATE ' . MAFIA_PLAYERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $player_ary)
			. ' WHERE player_id = '. $this->db->sql_escape($playerID);
		$this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		if (!$replace){
			//Check to see if signups are complete, move into setup status.
			//Then bump up any approved games to make sure that the max amount are in signups.
			$com_ary = array(
				'SELECT'	=> 'g.maximum_players, g.entered_players, g.game_type',
				'FROM'		=> array(MAFIA_GAMES_TABLE => 'g'),
				'WHERE'		=> 'g.game_id = '.(int)$this->db->sql_escape($gameID),
				);
			
			$sql = $this->db->sql_build_query('SELECT', $com_ary);
			$res = $this->db->sql_query($sql);
			if($ga = $this->db->sql_fetchrow($res))
			{
				if($ga['entered_players'] >= $ga['maximum_players'])
				{
					//Delete any orphaned unapproved player signups.
					$del = 'DELETE FROM '.MAFIA_PLAYERS_TABLE.' WHERE slot_id = 0 AND game_id = '.$this->db->sql_escape($gameID);
					$this->db->sql_query($del);	
						
					bumpIntoSignups($ga['game_type']);
					$sql = 'UPDATE ' . MAFIA_GAMES_TABLE . '
							SET status = '.GAME_PROGRESS_SETUP.'
							WHERE game_id = ' . (int)$this->db->sql_escape($gameID);
					$this->db->sql_query($sql);
				}
			}
			$this->db->sql_freeresult($res);
			$this->startGame($gameID);
		}
	}
	public function checkPlayer($gameID, $userID, $type = 0){
		$asPlayer = false;
		$asMod = false;
		$row = array();
		if($type == 0 || $type == 1)
		{
			$sql = 'SELECT m.game_id, m.user_id
				FROM phpbb_mafia_moderators m
				WHERE m.user_id = '.$this->db->sql_escape($userID).'
				AND m.game_id = '.$this->db->sql_escape($gameID);
			$result = $this->db->sql_query($sql);
			$asMod = ($row = $db->sql_fetchrow($result)) ? true : false;
			$this->db->sql_freeresult($result);
		}
		if($type == 0 || $type == 2)
		{
			$sql = 'SELECT p.game_id, p.user_id, p.type
				FROM phpbb_mafia_players p
				WHERE p.user_id = '.$this->db->sql_escape($userID).'
				AND p.game_id = '.$this->db->sql_escape($gameID);
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$asPlayer = ($row) ? true : false;
			$this->db->sql_freeresult($result);
		}
		switch($type)
		{
			case 0:
				return ($asPlayer || $asMod) ? true : false;
				break;
			case 1: 
				return $asMod;
				break;
			case 2:
				return $asPlayer;
				break;
		}
	}
	public function setReplaceDateTemplate($template){
		//Select the proper display date based on V/LA status. Select today's date if none specified.
		$selectionDay = date('j');
		$selectionMonth = date('n');
		$selectionYear = date('Y');
		
		//Start Dates.
		for ($i = 1; $i < 32; $i++)
		{
			$selected = ($i == $selectionDay) ? ' selected="selected"' : '';
			$s_replace_day_options .= "<option value=\"$i\"$selected>$i</option>";
		}
		
		for ($i = 1; $i < 13; $i++)
		{
			$selected = ($i == $selectionMonth) ? ' selected="selected"' : '';
			$s_replace_month_options .= "<option value=\"$i\"$selected>" . date('F', mktime(0, 0, 0, $i, 1, date('Y'))) ."</option>";
		}
		$s_replace_year_options = '';
		
		$now = getdate();
		for ($i = $now['year']; $i <= ($now['year'] + 1); $i++)
		{
			$selected = ($i == $selectionyear) ? ' selected="selected"' : '';
			$s_replace_year_options .= "<option value=\"$i\"$selected>$i</option>";
		}
		unset($now);

		$template->assign_vars(array(
			'S_REPLACE_DAY_OPTIONS'	=> $s_replace_day_options,
			'S_REPLACE_MONTH_OPTIONS'	=> $s_replace_month_options,
			'S_REPLACE_YEAR_OPTIONS'	=> $s_replace_year_options,
		));	
	}
	public function checkReplacementDate(){
		$data['replace_day'] = request_var('replace_day', 0);
		$data['replace_month'] = request_var('replace_month', 0);
		$data['replace_year'] = request_var('replace_year', 0);

		
		//Validate the submitted dates.
		$validate_array = array(
			'replace_day'		=> array('num', true, 1, 31),
			'replace_month'	=> array('num', true, 1, 12),
			'replace_year'		=> array('num', true, 2011, gmdate('Y', time()) + 50),
		);
		$error = validate_data($data, $validate_array);
		if($error)
		{
			trigger_error('Shit messed up.');
		}
		
		//Static timestamp for date validations.
		$mkStatic = mktime(0,0,0,0,0,0);
		//Start Date timestamp for date comparison.
		$mkStart =  mktime(0,0,0,$data['replace_month'],$data['replace_day'],$data['replace_year']);
		
		
		//Make sure that every variable is set properly.
		if(($data['replace_day'] === 0) || ($data['replace_month'] === 0) || ($data['replace_year'] === 0))
		{
			trigger_error('TOO_SMALL');
		}
		//Make sure date exists.
		else if($mkStart == $mkStatic)
		{
			trigger_error('NO_REPLACE_DATA');
		}
		//Make sure that the end date is after today.
		else if($mkStart < time())
		{
			trigger_error('REPLACE_DATE_PRIOR');
		}
		//Make sure that date is not farther away than 2 months.
		else if(($mkStart - time()) > 5259487)
		{
			trigger_error('REPLACE_TOO_LARGE');
		}

		return $mkStart;
	}
	public function assembleGameTopic($queue_forum){

		$uid = $bitfield = $options = ''; // will be modified by generate_text_for_storage
		$allow_bbcode = $allow_smilies = $allow_urls = true;
		$message = 'bonus2!';
		generate_text_for_storage($message, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);
						
	// New Topic Example
		$data = array( 
	    // General Posting Settings
	    'forum_id'            => $queue_forum,    // The forum ID in which the post will be placed. (int)
	    'topic_id'            => 0,    // Post a new topic or in an existing one? Set to 0 to create a new one, if not, specify your topic ID here instead.
	    'icon_id'            => false,    // The Icon ID in which the post will be displayed with on the viewforum, set to false for icon_id. (int)

	    // Defining Post Options
	    'enable_bbcode'    => true,    // Enable BBcode in this post. (bool)
	    'enable_smilies'    => true,    // Enabe smilies in this post. (bool)
	    'enable_urls'        => true,    // Enable self-parsing URL links in this post. (bool)
	    'enable_sig'        => true,    // Enable the signature of the poster to be displayed in the post. (bool)

	    // Message Body
	    'message'            => $message,        // Your text you wish to have submitted. It should pass through generate_text_for_storage() before this. (string)
	    'message_md5'    => md5($message),// The md5 hash of your message

	    // Values from generate_text_for_storage()
	    'bbcode_bitfield'    => $bitfield,    // Value created from the generate_text_for_storage() function.
	    'bbcode_uid'        => $uid,        // Value created from the generate_text_for_storage() function.

	    // Other Options
		//Automatically lock the topic so that the game mod can work on it.
	    'post_edit_locked'    => 1,        // Disallow post editing? 1 = Yes, 0 = No
	    'topic_title'        => $subject,    // Subject/Title of the topic. (string)

	    // Email Notification Settings
	    'notify_set'        => false,        // (bool)
	    'notify'            => false,        // (bool)
	    'post_time'         => 0,        // Set a specific time, use 0 to let submit_post() take care of getting the proper time (int)
	    'forum_name'        => '',        // For identifying the name of the forum in a notification email. (string)

	    // Indexing
	    'enable_indexing'    => true,        // Allow indexing the post? (bool)

	    // 3.0.6
	    'force_approved_state'    => true, // Allow the post to be submitted without going into unapproved queue
		'autolock_time'			=> 0,
		'autolock_input'		=> ''
		);

		return $data;
	}
	public function startGame($gameID){
		// make sure the game exists
		if($game){
			//Create the game topic for the moderator.
			//TODO - Review permission scheme.
			$que = 'SELECT * FROM '.MAFIA_GAME_TYPES_TABLE.' WHERE
			type_id = '. $game['game_type'];
			$res = $this->db->sql_query($que);
			$forum = $this->db->sql_fetchrow($res);
			$this->db->sql_freeresult($res);
			
			//Create subject line.
			$subject = $game['type_name'] . ' ' . ($nu['numbering'] + 1) . ' - ' . $game['name'];
			$data = assembleGameTopic($forum['forum_id']);
			submit_post('post',  $subject,  'mith',  POST_NORMAL,  $poll,  $data);
			//Correct all the discrepancies in thread poster.
			correctThreadPoster($data['post_id'],$game['main_mod_id']);
			//Add them to the proper group so they have permissions.
			group_user_add($forum['group_id'], $game['main_mod_id']); 
			
			//update the status, started_time and game topic
			$sql = 'UPDATE ' . MAFIA_GAMES_TABLE . '
				SET started_time = '. time() .',
					status=4 ,
					topic_id = ' . $data['topic_id'] . '
				WHERE game_id = ' . (int)$this->db->sql_escape($gameID);
			$this->db->sql_query($sql);
			return true;
		} else { 
			return false;
		}
	}
	public function removeSignup($template, $gameID, $userID){
		$game = load_game(0, $gameID);

	
		$sql_ary = array(
			'SELECT'	=> 'p.slot_id, p.type',
			'FROM'		=> array(
							MAFIA_PLAYERS_TABLE	=> 'p'),
			'WHERE'		=> 'p.game_id = '.$gameID.'
							AND p.user_id = '.$userID,
		);
		
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		if($row = $this->db->sql_fetchrow($result))
		{
		
			//First, make sure game isn't already started, otherwise this shouldn't happen.
			if($game['started_time']){
				$del = 'UPDATE '.MAFIA_PLAYERS_TABLE.' SET type=' . REPLACED_OUT . ' WHERE user_id = '.$this->db->sql_escape($userID).' AND game_id = '.$this->db->sql_escape($gameID);
				$this->db->sql_query($del);
			} else {
				$del = 'DELETE FROM '.MAFIA_PLAYERS_TABLE.' WHERE user_id = '.$this->db->sql_escape($userID).' AND game_id = '.$this->db->sql_escape($gameID);
				$this->db->sql_query($del);
				if($row['slot_id']){
					$del = 'DELETE FROM '.MAFIA_SLOTS_TABLE.' WHERE slot_id = '. $row['slot_id'] .' AND game_id = '.$this->db->sql_escape($gameID);
					$this->db->sql_query($del);
				}
			}
		
			//Check what type of signup it is so we can change proper totals.
			switch($row['type'])
			{
				case 0:
				case 1:
					//Delete player entry.
					//Update player totals for the game.
					if($row['slot_id'] == 0)
					{
						$upd = 'UPDATE '.MAFIA_GAMES_TABLE.' SET requested_players = ('.((int)($game['requested_players']) - 1).')
								WHERE game_id = '.$this->db->sql_escape($game['game_id']);
						$this->db->sql_query($upd);
					}
					else
					{
						$upd = 'UPDATE '.MAFIA_GAMES_TABLE.' SET entered_players = ('.((int)($game['entered_players']) - 1).')
								WHERE game_id = '.$this->db->sql_escape($game['game_id']);
						$this->db->sql_query($upd);
					}
					break;
				case 2:
					//Delete player entry.
					//Update player totals for the game.
					$upd = 'UPDATE '.MAFIA_GAMES_TABLE.' SET replacements = ('.((int)($game['replacements']) - 1).')
							WHERE game_id = '.$this->db->sql_escape($game['game_id']);
					$this->db->sql_query($upd);
					break;
				default:
					trigger_error('NO_SIGNUP_TYPE');
					break;
			}
		}
		else
		{
			trigger_error('NOT_SIGNED_UP');
		}
	}
}
?>