<?php
/**
 *
 * @package phpBB Extension - Mafiascum Signup
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace mafiascum\signup\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface
{
    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\request\request */
    protected $request;

    /* @var \phpbb\db\driver\driver */
	protected $db;

    /* @var \phpbb\user */
    protected $user;

    /* @var \phpbb\user_loader */
    protected $user_loader;

    /* @var \phpbb\auth\auth */
    protected $auth;

    /* phpbb\language\language */
    protected $language;

    static public function getSubscribedEvents()
    {
        return array(
            'core.permissions' => 'wire_up_permissions',
        );
    }

    public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\request\request $request, \phpbb\db\driver\driver_interface $db,  \phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\language\language $language, \phpbb\auth\auth $auth, $table_prefix)
    {
        $this->helper = $helper;
        $this->template = $template;
        $this->request = $request;
        $this->db = $db;
        $this->user = $user;
        $this->user_loader = $user_loader;
        $this->language = $language;
        $this->auth = $auth;
        $this->table_prefix = $table_prefix;
    }

    public functon wire_up_permissions($event){
        $permissions = $event['permissions'];
        $permissions['m_queue_1'] = array('lang' => 'ACL_M_QUEUE_1', 'cat' => 'post');
        $permissions['m_queue_2'] = array('lang' => 'ACL_M_QUEUE_2', 'cat' => 'post');
        $permissions['m_queue_3'] = array('lang' => 'ACL_M_QUEUE_3', 'cat' => 'post');
        $permissions['m_queue_4'] = array('lang' => 'ACL_M_QUEUE_4', 'cat' => 'post');
        $permissions['m_queue_5'] = array('lang' => 'ACL_M_QUEUE_5', 'cat' => 'post');
        $permissions['m_queue_6'] = array('lang' => 'ACL_M_QUEUE_6', 'cat' => 'post');
        $permissions['m_queue_7'] = array('lang' => 'ACL_M_QUEUE_7', 'cat' => 'post');
        $event['permissions'] = $permissions;
    }
}
?>