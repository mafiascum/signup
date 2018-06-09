<?php

namespace mafiascum\signup\controller;

class viewQueue
{
    /* @var \phpbb\request\request */
    protected $request;

    /* @var \phpbb\user */
    protected $user;

    /* phpbb\language\language */
    protected $language;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\auth $auth*/
    protected $auth;

    /* @var \phpbb\template\template $template*/
    protected $template;
    
    public function __construct(\phpbb\request\request $request, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\controller\helper $helper, \phpbb\auth $auth, \phpbb\template\template $template)
    {
        $this->request = $request;
        $this->user = $user;
        $this->db = $db;
        $this->language = $language;
        $this->template = $template;
        $this->helper = $helper;
        $this->auth = $auth;
        $this->template = $template
    }

    public function handle()
    {
        global $table_prefix, $phpbb_root_path, $phpEx;

        define('MAFIA_GAMES_TABLE', $table_prefix . 'mafia_games');
        define('MAFIA_MODERATORS_TABLE', $table_prefix . 'mafia_moderators');
        define('MAFIA_PLAYERS_TABLE', $table_prefix . 'mafia_players');
        define('MAFIA_SLOTS_TABLE', $table_prefix . 'mafia_slots');
        define('MAFIA_FACTIONS_TABLE', $table_prefix . 'mafia_factions');
        define('MAFIA_GAME_TYPES_TABLE', $table_prefix . 'mafia_game_types');

        //Display variables.
        $queue = $this->request->variable('q', 0);
        $gameID = $this->request->variable('g', 0);
        $mode = $this->request->variable('mode', '');
        $type = $this->request->variable('type', '');
        $d_approval = $this->request->variable('appr', 0);
        $d_status = $this->request->variable('sta', -1);

        //Pagination variables.
        $start   = $this->request->variable('start', 0);
        $limit   = $this->request->variable('limit', (int) 25);

        $main_user_id = $this->user->data['user_id'];
        
        $gameManager = new \mafiascum\signup\includes\gameManager($this->db, $phpbb_root_path, $phpEx);
        $gameDisplay = new \mafiascum\signup\includes\gameDisplay($this->db, $phpbb_root_path, $phpEx);
        $queueManager = new \mafiascum\signup\includes\queueManager($this->db, $phpbb_root_path, $phpEx);
        $queueDisplay = new \mafiascum\signup\includes\queueDisplay($this->db, $phpbb_root_path, $phpEx);


        if ($main_user_id == ANONYMOUS){
            $loc = append_sid($phpbb_root_path . 'ucp.' . $phpEx . '?mode=login');
            $message = $this->user->lang['LOGIN_ERROR_QUEUE'] . '<br /><br />' . sprintf($this->user->lang['LOGIN_QUEUE'], '<a href="' . $loc . '">', '</a>');
            meta_refresh(3, $loc);
            trigger_error($message);
        }
        switch($mode){
            case 'submit':
                $submit = $this->request->variable('submit_queue', false);
                if(confirm_box(true))
                {
                    //Check and add the game to queue.
                    $data = array();
                    $data['main_mod'] = $this->request->variable('main_moderator', '');
                    $data['game_name'] = $this->request->variable('game_name', '');
                    $data['game_type'] = (int)$this->request->variable('game_type', 0);
                    $data['requested_slots'] = (int)$this->request->variable('requested_slots', 0);
                    $data['game_description'] = $this->request->variable('game_description', '');
                    
                    //Double check in case they editted the variables manually. 
                    $errors = $gameManager->errorsInGameData($data, $user);
                    if(sizeof($errors))
                    {
                        trigger_error('CANT_EDIT_CONFIRMATION');
                    }
                    $newID = $gameManager->createGame($data['game_name'], $data['game_type'], $gameManager->checkModerator($data['main_mod'], true), $data['requested_slots'], $data['game_description']);
                    $loc = append_sid($phpbb_root_path . 'viewgame.' . $phpEx . '?g=' . $newID);
                    $message = 'Game submitted successfully.' . '<br /><br />' . sprintf($user->lang['RETURN_GAME_VIEW'], '<a href="' . $loc . '">', '</a>');
                    meta_refresh(3, $loc);
                    trigger_error($message);
                }
                elseif($submit)
                {
                    $data = array();
                    $data['main_mod'] = $this->request->variable('main_moderator', '');
                    $data['game_name'] = $this->request->variable('game_name', '');
                    $data['game_type'] = (int)$this->request->variable('game_type', 0);
                    $data['requested_slots'] = (int)$this->request->variable('requested_slots', 0);
                    $data['game_description'] = $this->request->variable('game_description', '');
                    
                    $errors = $gameManager->errorsInGameData($data, $this->user);
                    if(sizeof($errors))
                    {
                        $loc = append_sid($phpbb_root_path . 'viewqueue.' . $phpEx);
                        $message = $this->user->lang['ERROR_GAME_SUBMISSION'] . '<br /><br />'
                        . implode($errors, '<br/>') . '<br/><br/>'
                        . sprintf($user->lang['RETURN_MAIN_QUEUE'], '<a href="' . $loc . '">', '</a>');
                        meta_refresh(3, $loc);
                        trigger_error($message);
                    }
                    
                    $hiddenFields = build_hidden_fields(array(
                        'main_moderator'    => $data['main_mod'],
                        'game_name'         => $data['game_name'],
                        'game_type'         => $data['game_type'],
                        'requested_slots'   => $data['requested_slots'],
                        'game_description'  => $data['game_description'],
                        
                        
                    ));
                    
                    $template->assign_vars(array(   
                            'MAIN_MODERATOR'    => $data['main_mod'],
                            'GAME_NAME'         => $data['game_name'],
                            'GAME_TYPE'         => $gameDisplay->getGameTypeName($data['game_type']),
                            'REQUESTED_SLOTS'   => $data['requested_slots'],
                            'GAME_DESCRIPTION'  => $data['game_description'],
                    ));
                
                    confirm_box(false, 'APPROVE_SUBMISSION', $hiddenFields, 'game_moderate_approve.html');
                }
                
                $template->assign_vars(array(
                    'U_FIND_USERNAME'   => append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=ucp&amp;field=main_moderator&amp;select_single=true'),
                    'U_USERNAME'        => $this->user->data['username'],
                    'GAME_TYPE_SELECT'  => $gameDisplay->createGameTypeSelect($queue),
                    'CAN_MODERATE'      => $gameManager->checkModLimits($this->user->data['user_id'], 0),
                ));
                
                page_header($this->user->lang['SUBMIT_GAME'], true, 0, 'forum', '', 'NOINDEX, FOLLOW');
                $templateFile = 'game_request.html';
            break;
            case 'replacement':
                $moderate = $this->auth->acl_get('u_queue_'.$queue);
                $sql = 'Select s.game_id FROM ' . MAFIA_SLOTS_TABLE . ' s LEFT JOIN ' . MAFIA_PLAYERS_TABLE . ' p ON p.slot_id=s.slot_id AND p.game_id = s.game_id AND p.type<>5 WHERE p.player_id IS NULL';
                $res = $this->db->sql_query($sql);
                $temp_game_ids = $this->db->sql_fetchrowset($res);
                $game_ids = array();
                foreach ($temp_game_ids as $game_id) {
                    $game_ids[] = $game_id['game_id'];
                }
                $game = $gameManager->loadGame($this->template,0, 0, 0, 0, 0, $start, $limit, 'recent_games', $moderate, $game_ids);
                if (is_array($game[0])){
                    $queue_info = $game[0];
                } else{
                    $queue_info = $game;
                }
                $queueManager->generateQueueList($this->template,'queues', $queue);
                $template->assign_vars(array(
                    'QUEUE_NAME'    => $queue_info['type_name'],
                    'QUEUE_ID'      => $queue,
                    'MODERATION' => $moderate, //Show mod actions or not.
                    'REPLACEMENT'   => true,
                ));
                
                page_header(sprintf($this->user->lang['SINGLE_QUEUE'], $queue_info['type_name'], ''), true, 0, 'forum', '', 'NOINDEX, FOLLOW');
                $this->template->assign_vars(array(
                    'S_STATUS_OPTIONS'  => $gameDisplay->createStatusOptions($d_status),
                    'S_SORT_ACTION'     => append_sid('viewqueue.'.$phpEx.'?mode=view&amp;q='.$queue),
                    'APPROVAL_STATUS'   => $d_approval,
                ));
                $templateFile = 'game_queue.html';
            break;
            case 'users_games':
                $user_id = (int)$this->request->variable('u', 0);
                $sql = 'Select game_id FROM  ' . MAFIA_PLAYERS_TABLE . ' WHERE user_id =' . $user_id;
                $res = $this->db->sql_query($sql);
                $temp_game_ids = $this->db->sql_fetchrowset($res);
                $game_ids = array();
                foreach ($temp_game_ids as $game_id) {
                    $game_ids[] = $game_id['game_id'];
                }
                $gameManager->loadGame($this->template,0, 0, 0, 0, 0, $start, $limit, 'player_games',false, $game_ids);
                $sql = 'Select game_id FROM  ' . MAFIA_MODERATORS_TABLE . ' WHERE user_id =' . $user_id;
                $res = $this->db->sql_query($sql);
                $temp_game_ids = $this->db->sql_fetchrowset($res);
                $game_ids = array();
                foreach ($temp_game_ids as $game_id) {
                    $game_ids[] = $game_id['game_id'];
                }
                $gameManager->loadGame($this->template,0, 0, 0, 0, 0, $start, $limit, 'mod_games',false, $game_ids);
                page_header('Your Games', true, 0, 'forum', '', 'NOINDEX, FOLLOW');
                $templateFile = 'users_games.html';
            break;
            case 'view':
            default:
                if($queue){
                    if ($d_status === -1){
                        $d_status = Array(GAME_PROGRESS_SIGNUPS, GAME_PROGRESS_QUEUED);
                    }
                    $moderate = $this->auth->acl_get('m_queue_'.$queue);

                    $game = $gameManager->loadGame($this->template, $queue, 0, 0, $d_approval, $d_status, $start, $limit, 'recent_games', $moderate, array());
                    if (is_array($game[0])){
                        $queue_info = $game[0];
                    } else{
                        $queue_info = $game;
                    }
                    $queueManager->generateQueueList($this->template,'queues', $queue);
                    $this->template->assign_vars(array(
                        'QUEUE_NAME'    => $queue_info['type_name'],
                        'QUEUE_ID'      => $queue,
                        'MODERATION' => $moderate, //Show mod actions or not.
                    ));
                    page_header(sprintf($this->user->lang['SINGLE_QUEUE'], $queue_info['type_name'], ''), true, 0, 'forum', '', 'NOINDEX, FOLLOW');
                    $this->template->assign_vars(array(
                        'S_STATUS_OPTIONS'  => $gameDisplay->createStatusOptions($d_status),
                        'S_SORT_ACTION'     => append_sid('viewqueue.'.$phpEx.'?mode=view&amp;q='.$queue),
                        'APPROVAL_STATUS'   => $d_approval,
                    ));
                    $templateFile = 'game_queue.html';
                } else {
                    if ($d_status === -1){
                        $d_status = GAME_PROGRESS_SIGNUPS;
                    }
                    $gameManager->loadGame(0, 0, 0, $d_approval, $d_status, $start, $limit, 'recent_games', false, array(), true);
                    $queueManager->generateQueueList('queues');
                    page_header($this->user->lang['QUEUES'], true, 0, 'forum', '', 'NOINDEX, FOLLOW');
                    $this->template->assign_vars(array(
                        'S_STATUS_OPTIONS' => $gameDisplay->createStatusOptions($d_status),
                        'S_SORT_ACTION'     => append_sid('viewqueue.'.$phpEx),
                        'APPROVAL_STATUS'   => $d_approval,
                    ));
                    $this->templateFile = 'game_queue.html';
                }
            break;
        }
    }
}
?>