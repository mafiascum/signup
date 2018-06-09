<?php

namespace mafiascum\signup\migrations;

class signup extends \phpbb\db\migration\migration
{

    public function effectively_installed()
    {
        return $this->db_tools->sql_table_exists($this->table_prefix . 'mafia_games');
    }
    
    static public function depends_on()
    {
        return array('\phpbb\db\migration\data\v31x\v314');
    }
    public function update_data(){
    	return array(
        	array('permission.add', array('m_queue_1')),
        	array('permission.add', array('m_queue_2')),
        	array('permission.add', array('m_queue_3')),
        	array('permission.add', array('m_queue_4')),
        	array('permission.add', array('m_queue_5')),
        	array('permission.add', array('m_queue_6')),
        	array('permission.add', array('m_queue_7')),
     	);
	}
	public function revert_data(){
		return array(
			array('permission.remove', array('m_queue_1')),
			array('permission.remove', array('m_queue_2')),
			array('permission.remove', array('m_queue_3')),
			array('permission.remove', array('m_queue_4')),
			array('permission.remove', array('m_queue_5')),
			array('permission.remove', array('m_queue_6')),
			array('permission.remove', array('m_queue_7')),
		);
	}
    public function update_schema()
    {
        return array(
            'add_tables'    => array(
                $this->table_prefix . 'mafia_games' => array(
                    'COLUMNS' => array(
						'game_id'				=> array('UINT', NULL, 'auto_increment'),
						'name'					=> array('VCHAR:255', ''),
						'description'			=> array('TEXT', ''),
						'numbering'				=> array ('UINT', 0),
						'topic_id'				=> array('UINT', NULL),
						'requested_players'		=> array('UINT', 0),
						'entered_players'		=> array('UINT', 0),
						'maximum_players'		=> array('UINT', 0),
						'replacements'			=> array('UINT', 0),
						'game_type'				=> array('UINT', NULL),
						'status'				=> array('UINT', 0),
						'status_alternate'		=> array('VCHAR:80', ''),
						'created_time'			=> array('UINT', 0),
						'created_by_user_id'	=> array('UINT', NULL),
						'main_mod_id'			=> array('UINT', NULL),
						'approved_by_user_id'	=> array('UINT', NULL),
						'approved_time'			=> array('UINT', NULL),
						'started_time'			=> array('UINT', NULL),
						'completed_time'		=> array('UINT', NULL),
						'bbcode_uid'			=> array('VCHAR:255', ''),
						'bbcode_bitfield'		=> array('VCHAR:8', ''),
                    ),
					'PRIMARY_KEY' => 'game_id',
                    'KEYS' => array(
                        'approved_by_user_id' => array('INDEX', 'approved_by_user_id'),
						'status' => array('INDEX', 'status'),
						'moderator_user_id' => array('INDEX', 'main_mod_id'),
						'created_by_user_id' => array('INDEX', 'created_by_user_id'),
                    ),
                ),
				$this->table_prefix . 'mafia_moderators' => array(
                    'COLUMNS' => array(
						'user_id'	=> array('UINT', NULL),
						'game_id'	=> array('UINT', NULL),
						'type'		=> array('UINT', NULL),
						'approved'	=> array('UINT', 0),
                    ),
					'PRIMARY_KEY' => array('game_id', 'user_id'),
                ),
				$this->table_prefix . 'mafia_players' => array(
                    'COLUMNS' => array(
						'player_id'				=> array('UINT', NULL, 'auto_increment'),
						'game_id'				=> array('UINT', NULL),
						'user_id'				=> array('UINT', NULL),
						'slot_id'				=> array('UINT', NULL),
						'type'					=> array('UINT', NULL),
						'replaced_player_id'	=> array('UINT', NULL),
                    ),
					'PRIMARY_KEY' => 'player_id',
					'KEYS' => array(
                        'user_id' => array('INDEX', 'user_id'),
						'game_id' => array('INDEX', 'game_id'),
                    ),
                ),
				$this->table_prefix . 'mafia_slots' => array(
                    'COLUMNS' => array(
						'slot_id'	=> array('UINT', NULL),
						'game_id'	=> array('UINT', NULL),
						'status'	=> array('UINT', NULL),
                    ),
					'PRIMARY_KEY' => array('slot_id','game_id'),
                ),
				$this->table_prefix . 'mafia_factions' => array(
                    'COLUMNS' => array(
						'faction_id'	=> array('UINT', NULL),
						'game_id'		=> array('UINT', NULL),
						'name'			=> array('VCHAR:32', ''),
						'alignment_id'	=> array('UINT', NULL),
                    ),
					'PRIMARY_KEY' => 'faction_id',
					'KEYS' => array(
						'game_id' => array('INDEX', 'game_id'),
                    ),
                ),
				$this->table_prefix . 'mafia_game_types' => array(
                    'COLUMNS' => array(
						'type_id'				=> array('UINT', NULL, 'auto_increment'),
						'type_name'				=> array('VCHAR:80', ''),
						'group_id'				=> array('UINT', NULL),
						'forum_id'				=> array('UINT', NULL),
						'archive_forum_id'		=> array('UINT', NULL),
						'max_players'			=> array('UINT', 13),
						'min_players'			=> array('UINT', NULL),
						'signup_limit'			=> array('UINT', NULL),
						'prein_percentage'		=> array('UINT', 50),
						'is_locked'				=> array('UINT', 0),
                    ),
					'PRIMARY_KEY' => 'type_id',
                ),
            ),
        );
    }

    public function revert_schema()
    {
        return array(
            'drop_tables'    => array(
                $this->table_prefix . 'mafia_games',
                $this->table_prefix . 'mafia_moderators',
				$this->table_prefix . 'mafia_players',
				$this->table_prefix . 'mafia_slots',
                $this->table_prefix . 'mafia_factions',
				$this->table_prefix . 'mafia_game_types',
            ),
        );
    }
}
?>