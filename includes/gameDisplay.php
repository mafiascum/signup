<?php
/**
* @package phpBB Extension - Mafiascum Signup
* @copyright (c) 2018 mafiascum.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace mafiascum\signup\includes;

use phpbb\db\driver\factory as database;

class gameDisplay{

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
	public function createGameTypeSelect($gtype = 0){
		$html = '';
		$sql = 'SELECT type_id, type_name from ' . MAFIA_GAME_TYPES_TABLE . '
				ORDER BY type_id ASC';
		$result = $this->db->sql_query($sql);
		while($type = $this->db->sql_fetchrow($result))
		{
			$html .= '<option value="'.$type['type_id'].'"'.(($type['type_id'] == $gtype) ? ' selected="selected"' : '').'>'.$type['type_name'].'</option>';
		}
		$this->db->sql_freeresult($result);
		return $html;
	}
	public function createStatusOptions($status = 0, $available = false){
		$select = '';
		$select .= '<option value="' . GAME_PROGRESS_PENDING . '"' . (($status == GAME_PROGRESS_PENDING) ? ' selected="selected"' : '') . '>Pending</option>';
		$select .= '<option value="' . GAME_PROGRESS_QUEUED . '"' . (($status == GAME_PROGRESS_QUEUED) ? ' selected="selected"' : '') . '>Queued</option>';
		$select .= '<option value="' . GAME_PROGRESS_SIGNUPS . '"' . (($status == GAME_PROGRESS_SIGNUPS) ? ' selected="selected"' : '') . '>Signups</option>';
		$select .= '<option value="' . GAME_PROGRESS_SETUP . '"' . (($status == GAME_PROGRESS_SETUP) ? ' selected="selected"' : '') . '>Setup</option>';
		$select .= '<option value="' . GAME_PROGRESS_ONGOING . '"' . (($status == GAME_PROGRESS_ONGOING) ? ' selected="selected"' : '') . '>Ongoing</option>';
		$select .= '<option value="' . GAME_PROGRESS_COMPLETED . '"' . (($status == GAME_PROGRESS_COMPLETED) ? ' selected="selected"' : '') . '>Completed</option>';
		return $select;
	}
	public function createStatusDetailOptions($status = 0){
		$html = '';

		/*$sql = 'SELECT status_id, status_name FROM ' . MAFIA_GAME_STATUS_TABLE . '
				ORDER BY status_id ASC';

		$result = $this->db->sql_query($sql);
		while($type = $this->db->sql_fetchrow($result))
		{
			$html .= '<option value="'.$type['status_id'].'"'.(($type['status_id'] == $status) ? ' selected="selected"' : '').'>'.$type['status_name'].'</option>';
		}
		$this->db->sql_freeresult($result);*/
		return $html;
	}
	public function createPlayerStatusOptions($status = 0){
		$select = '';
		$select .= '<option value="' . SLOT_ALIVE . '"' . (($status == SLOT_ALIVE) ? ' selected="selected"' : '') . '>Alive</option>';
		$select .= '<option value="' . SLOT_DEAD . '"' . (($status == SLOT_DEAD) ? ' selected="selected"' : '') . '>Dead</option>';
		$select .= '<option value="' . SLOT_OTHER . '"' . (($status == SLOT_OTHER) ? ' selected="selected"' : '') . '>Other</option>';
		$select .= '<option value="' . SLOT_STATUS_PENDING . '"' . (($status == SLOT_STATUS_PENDING) ? ' selected="selected"' : '') . '>Pending</option>';
		return $select;
	}
	public function createOutcomeOptions($outcome = 0){
		$select = '';
		$select .= '<option value="' . SLOT_LOSS . '"' . (($outcome == SLOT_LOSS) ? ' selected="selected"' : '') . '>Lost</option>';
		$select .= '<option value="' . SLOT_WIN . '"' . (($outcome == SLOT_WIN) ? ' selected="selected"' : '') . '>Won</option>';
		$select .= '<option value="' . SLOT_DRAW . '"' . (($outcome == SLOT_DRAW) ? ' selected="selected"' : '') . '>Draw</option>';
		$select .= '<option value="' . SLOT_OUTCOME_PENDING . '"' . (($outcome == SLOT_OUTCOME_PENDING) ? ' selected="selected"' : '') . '>Pending</option>';
		return $select;
	}
	public function createModTypeOptions($type = 0){
		$select = '';
		$select .= '<option value="' . MODERATOR_TYPE_MAIN. '"' . (($type == MODERATOR_TYPE_MAIN) ? ' selected="selected"' : '') . '>Primary</option>';
		$select .= '<option value="' . MODERATOR_TYPE_COMOD . '"' . (($type == MODERATOR_TYPE_COMOD) ? ' selected="selected"' : '') . '>CoMod</option>';
		$select .= '<option value="' . MODERATOR_TYPE_BACKUP . '"' . (($type == MODERATOR_TYPE_BACKUP) ? ' selected="selected"' : '') . '>Backup</option>';
		return $select;
	}
	public function createGameTypeName($id){
		$name = '';
		$sql = 'SELECT type_name from ' . MAFIA_GAME_TYPES_TABLE . '
				WHERE type_id = '.$this->db->sql_escape($id);
		$result = $this->db->sql_query($sql);
		if($type = $this->db->sql_fetchrow($result))
		{
			$name = $type['type_name'];
		}

		$this->db->sql_freeresult($result);
		return $name;
	}
	public function getModTypeName($mafiaModeratorType){
		switch($mafiaModeratorType)
		{
		case MODERATOR_TYPE_MAIN: return "Main Moderator";
		case MODERATOR_TYPE_COMOD: return "Co-Mod";
		case MODERATOR_TYPE_BACKUP: return "Backup";
		}
	}
	public function getAlignmentName($alignment_id){
		switch($alignment_id)
		{
			case MAFIA_ALIGNMENT_TOWN: return "Town";
			case MAFIA_ALIGNMENT_MAFIA: return "Mafia";
			case MAFIA_ALIGNMENT_THIRD_PARTY: return "Third Party";
			return "Other";
		}
	}
	public function getModInfo($gameID){
		$sql_ary = array(
		'SELECT'	=> 'm.*, u.*',
		'FROM'		=> array(MAFIA_MODERATORS_TABLE => 'm'),
		'LEFT_JOIN' => array(		  
			array(
				'FROM'	=> array(USERS_TABLE => 'u'),
				'ON'	=> 'm.user_id = u.user_id')
			),
		'WHERE'		=> 'm.game_id = '.(int)$this->db->sql_escape($gameID),
		'ORDER_BY'	=> 'm.type',
		);
		
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);

		$res = $this->db->sql_query($sql);
		
		while($mod = $this->db->sql_fetchrow($res))
		{
			$this->template->assign_block_vars('mods', array(
			'MODERATOR' 	=> get_username_string('full', $mod['user_id'], $mod['username'], $mod['user_colour']),
			'USER_ID'		=> $mod['user_id'],
			'MOD_TYPE'		=> getModTypeName($mod['type']),
			'MOD_TYPE_OPTIONS'	=> createModTypeOptions($mod['type']),
			'U_REMOVE_MOD' => append_sid("{$this->root_path}viewgame.$this->php_ext?g=$gameID&deletemod=true&mod_id=" . $mod['user_id']),
			));
		}
		$this->db->sql_freeresult($res);
	}
	public function displayModerators($gameID, $prev_data = false){
		$db_result = ($prev_data) ? $prev_data : load_moderators($gameID);
		while($moderator_row = $this->db->sql_fetchrow($db_result))
		{
			$moderatorId = $moderator_row['moderator_id'];
			$this->template->assign_block_vars('moderators', array(
			
				
			
			));
		}
	}
	public function getPlayerInfo($gameID, $approved_only = false, $exclude_rejected = true){
		$sql_ary = array(
		'SELECT'	=> 'p.*, s.*, r.*, f.*, m.*, u.user_id, u.username, g.status',
		'FROM'		=> array(MAFIA_PLAYERS_TABLE => 'p'),
		'LEFT_JOIN'	=> array(
			array(
				'FROM'	=> array(MAFIA_SLOTS_TABLE => 's'),
				'ON'	=> 'p.slot_id = s.slot_id AND s.game_id =' .$gameID),
			array(
				'FROM'	=> array(MAFIA_GAMES_TABLE => 'g'),
				'ON'	=> 'p.game_id = g.game_id'),
			array(
				'FROM'	=> array(MAFIA_ROLES_TABLE => 'r'),
				'ON'	=> 's.slot_role_id = r.role_id' ),
			array(
				'FROM'	=> array(USERS_TABLE => 'u'),
				'ON'	=> 'p.user_id = u.user_id'),
			array(
				'FROM'	=> array(MAFIA_FACTIONS_TABLE => 'f'),
				'ON'	=> 's.faction_id = f.id'),
			array(
				'FROM'	=> array(MAFIA_MODIFIERS_TABLE => 'm'),
				'ON'	=> 'm.modifier_id = r.role_modifier')),
		'WHERE'		=> 'p.game_id = '.$this->db->sql_escape($gameID) . ' AND ' . ($approved_only ? 'NOT s.slot_id = 0 ': '1=1') . ' AND ' . ($exclude_rejected ? 'NOT p.type =' . REJECTED_IN : '1=1'),
		'ORDER_BY'	=> 'p.slot_id DESC',
		);
		
		//Specifically select only players that have associated slots.
		if($approved_only)
		{
			$sql_ary['WHERE'] = 'p.game_id = '.$db->sql_escape($gameID).' AND p.slot_id != 0';
		}
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$res = $this->db->sql_query($sql);
			//setup for faction select element
			$factionSelectStart = '<select id="editPlayerFaction" name="editPlayerFaction"  class="addPlayerInput">';
			$factionSelectStart .= '<option value="0"></option>';
			$factionOptionStart = array();
			$factionOptionEnd = array();
			$factions = array();
			$sql = " SELECT *"
				 . " FROM " . MAFIA_FACTIONS_TABLE
				 . " WHERE game_id=" . $this->db->sql_escape($gameID);

			$result = $this->db->sql_query($sql);
			$count = 0;
			while($row = $this->db->sql_fetchrow($result)){
				$factions[$count] = $row['id'];
				$factionOptionStart[$row['id']] = '<option value="';
				$factionOptionStart[$row['id']] .= $row['id'];
				$factionOptionStart[$row['id']] .= '"';
				$factionOptionEnd[$row['id']] = '>';
				$factionOptionEnd[$row['id']] .= $row['name'] . "(" . getAlignmentName($row['alignment_id']) . ")";
				$factionOptionEnd[$row['id']] .= '</option>';
				$count++;
			}
			$this->db->sql_freeresult($result);
			
			$factionSelectEnd  = '</select>';

			//setup for modifier select element
			$modifierSelectStart = '<select id="editRoleModifier" name="editRoleModifier" class="addPlayerInput"';
			$modifierSelectStart .='>';
			$modifierSelectStart .= '<option value="0"></option>';
			$modifierOptionStart = array();
			$modifierOptionEnd = array();
			$modifiers = array();

			$sql = " SELECT *"
				 . " FROM " . MAFIA_MODIFIERS_TABLE;

			$result = $this->db->sql_query($sql);
			$count = 0;

			while($row = $this->db->sql_fetchrow($result)){
				$modifiers[$count]=$row['modifier_id'];
				$modifierOptionStart[$row['modifier_id']] = '<option value="';
				$modifierOptionStart[$row['modifier_id']] .= $row['modifier_id'];
				$modifierOptionStart[$row['modifier_id']] .= '"';
				$modifierOptionEnd[$row['modifier_id']] = '>';
				$modifierOptionEnd[$row['modifier_id']] .= $row['modifier_name'];
				$modifierOptionEnd[$row['modifier_id']] .= '</option>';
				$count++;
			}

			$this->db->sql_freeresult($result);
			$modifierSelectEnd  .= '</select>';

			//setup for basicRole select element
			$basicRoleSelectStart = '<select id="editBasicRole" name="editBasicRole" class="addPlayerInput"';
			$basicRoleSelectStart .='>';
			$basicRoleSelectStart .= '<option value="0"></option>';
			$basicRoleOptionStart = array();
			$basicRoleOptionEnd = array();
			$basicRoles = array();

			$sql = " SELECT *"
				 . " FROM " . MAFIA_ROLES_TABLE;

			$result = $this->db->sql_query($sql);
			$count = 0;

			while($row = $this->db->sql_fetchrow($result)){
				$basicRoles[$count]=$row['role_id'];
				$basicRoleOptionStart[$row['role_id']] = '<option value="';
				$basicRoleOptionStart[$row['role_id']]  .= $row['role_id'];
				$basicRoleOptionStart[$row['role_id']]  .= '"';
				$basicRoleOptionEnd[$row['role_id']]  = '>';
				$basicRoleOptionEnd[$row['role_id']]  .= $row['role_name'];
				$basicRoleOptionEnd[$row['role_id']]  .= '</option>';
				$count++;
			}

			$this->db->sql_freeresult($result);
			$basicRoleSelectEnd  .= '</select>';
		while($player = $this->db->sql_fetchrow($res))
		{
			//build factionSelect
				$factionSelect = $factionSelectStart;
				for ($i = 0; $i< sizeof($factions); $i++){
					$factionSelect .= $factionOptionStart[$factions[$i]];
					if ($factions[$i] == $player['id']){
						$factionSelect .= 'selected="selected"';
					}
					$factionSelect .= $factionOptionEnd[$factions[$i]];
				}
				$factionSelect .= $factionSelectEnd;

				//build modifierSelect
				$modifierSelect = $modifierSelectStart;
				for ($i = 0; $i< sizeof($modifiers); $i++){
					$modifierSelect .= $modifierOptionStart[$modifiers[$i]];
					if ($modifiers[$i] == $player['modifier_id']){
						$modifierSelect .= 'selected="selected"';
					}
					$modifierSelect .= $modifierOptionEnd[$modifiers[$i]];
				}
				$modifierSelect .= $modifierSelectEnd;

				//build basicRoleSelect
				$basicRoleSelect = $basicRoleSelectStart;
				for ($i = 0; $i< sizeof($basicRoles); $i++){
					$basicRoleSelect .= $basicRoleOptionStart[$basicRoles[$i]];
					if ($basicRoles[$i] == $player['role_id']){
						$basicRoleSelect .= 'selected="selected"';
					}
					$basicRoleSelect .= $basicRoleOptionEnd[$basicRoles[$i]];
				}
				$basicRoleSelect .= $basicRoleSelectEnd;
				
				//build statusSelect
				$playerStatusSelect = "<select name='editStatus' class='addPlayerInput'> <option value='" . SLOT_ALIVE . "'";
				if ($player['stauts'] == SLOT_ALIVE){
					$playerStatusSelect .='select="selected"';
				}
				$playerStatusSelect	.= ">Alive</option> <option value='" . SLOT_DEAD . "'";
				if ($player['stauts'] == SLOT_DEAD){
					$playerStatusSelect .='select="selected"';
				}
				$playerStatusSelect	.= ">Dead</option> </select>";
				
			$this->template->assign_block_vars('players', array(
				'USER'		=> get_username_string('full', $player['user_id'], $player['username']),
				'USER_ID'	=> $player['user_id'],
				'ROLE_NAME'	=> $player['role_name'], //Actual Role name - //TODO -- Add Role name lookup.
				'PRESENTED_ROLE' => ($player['g.slot_status'] == SLOT_DEAD) ? $player['role_flavour_name'] . '('.$player['role_name'] . ')' : $user->lang['PENDING'], //Role name to show regular players.
				'FULL_ROLE'	=> generateFullRoleName($player['role_name'], $player['role_flavour_name'], $player['modifiers']),
				'FLAVOUR_NAME'	=> $player['role_flavour_name'],
				'STATUS'	=>  $player['type']== REPLACED_OUT ? 'Replaced' : getSlotStatusName($player['slot_status']),
				'STATUS_OPTIONS'	=> createPlayerStatusOptions($player['slot_status']),
				'ROLE_OPTIONS'	=> createRoleOptions($player['role_id']),
				'MODIFIER_OPTIONS'		=> createModifierOptions(explode(',', $player['modifier_name'])),
				'SLOT_ID'	=> $player['slot_id'] ? $player['slot_id'] : 'Pending Approval' ,
				'FACTION_NAME' => generateFullFactionName($player['name'], $player['alignment_id']), //Actual Factional name. //TODO -- Add faction name.
				/*'PRESENTED_FACTION' => ($player['g.slot_status'] == SLOT_DEAD) ? 'ROLE_NAME' : $user->lang['PENDING'],*/ //For now only add faction on slot reveal -- Faction to show regular players.
				'OUTCOME'	=> getSlotOutcomeName($player['slot_outcome']),
				'OUTCOME_OPTIONS'	=> createOutcomeOptions($player['slot_outcome']),
				'IS_ACCEPTED'	=> ($player['slot_id'] == 0) ? false : true,
				'IS_REJECTED' 	=> ($player['type'] == 4) ? true : false,
				'APPROVAL_LINK'	=> append_sid($this->root_path.'viewgame.'.$this->php_ext.'?mode=approve_player&amp;g='.$gameID.'&amp;pid='.$player['player_id']),
				'REMOVE_LINK'	=> append_sid($this->root_path.'viewgame.'.$this->php_ext.'?mode=remove_player&amp;g='.$gameID.'&amp;u='.$player['user_id']),
				'REJECT_LINK'	=> append_sid($this->root_path.'viewgame.'.$this->php_ext.'?mode=disapprove_player&amp;g='.$gameID.'&amp;pid='.$player['player_id']),
				'FACTION_SELECT'		=> $factionSelect,
				'MODIFIER_SELECT'		=> $modifierSelect,
				'BASICROLES_SELECT'		=> $basicRoleSelect,
				'PLAYER_STATUS_SELECT'	=> $playerStatusSelect,
			));
		}
		$this->db->sql_freeresult($res);
	}
	public function generateFullFactionName($factionName, $alignment_id){
		if($alignment_id) { 
			return $factionName . "(" .  getAlignmentName($alignment_id) . ")";
		}
		return $factionName;
	}
	public function getSlotStatusName($status, $user){
		//Define the proper status label.
		switch($status)
		{
			case SLOT_STATUS_PENDING:
				$stat = $user->lang['PENDING'];
				break;
			case SLOT_ALIVE:
				$stat = $user->lang['SLOT_ALIVE'];
				break;
			case SLOT_DEAD:
				$stat = $user->lang['SLOT_DEAD'];
				break;
			case SLOT_OTHER:
				$stat = $user->lang['SLOT_OTHER'];
				break;
			default:
				$stat = $user->lang['PENDING'];
				break;
		}
		return $stat;
	}
	public function getSlotOutcomeName($out, $user){
		switch($out)
		{
			case SLOT_OUTCOME_PENDING:
				$outcome = $user->lang['PENDING'];
				break;
			case SLOT_WIN:
				$outcome = $user->lang['SLOT_WIN'];
				break;
			case SLOT_LOSS:
				$outcome = $user->lang['SLOT_LOSS'];
				break;
			case SLOT_DRAW:
				$outcome = $user->lang['SLOT_DRAW'];
				break;
			default:
				$outcome = $user->lang['PENDING'];
				break;
		}
		return $outcome;
	}
	public function getPlayerTypeName($type){
		switch($type){
			case STANDARD_IN : return 'In';
			case PREIN : return 'PreIn';
			case REPLACEMENT : return 'Replacement';
		}
	}
}
?>