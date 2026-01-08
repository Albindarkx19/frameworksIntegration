<?php

/**
 * MessageController
 * Handles all messenger-related actions
 */
class MessageController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();

        // Only logged-in users can use the messenger
        Auth::checkAuthentication();
    }

    /**
     * Main messenger page - shows all conversations
     */
    public function index()
    {
        $this->View->render('message/index', array(
            'conversations' => MessageModel::getAllConversations(),
            'groups' => GroupModel::getUserGroups(),
            'unread_count' => MessageModel::getUnreadCount()
        ));
    }

    /**
     * Show new chat form (user selection)
     */
    public function newChat()
    {
        // Get all users except current user
        $all_users = UserModel::getPublicProfilesOfAllUsers();
        $current_user_id = Session::get('user_id');

        // Filter out current user
        $available_users = array_filter($all_users, function($user) use ($current_user_id) {
            return $user->user_id != $current_user_id;
        });

        $this->View->render('message/newChat', array(
            'users' => $available_users
        ));
    }

    /**
     * Show create group form
     */
    public function createGroupForm()
    {
        // Get all users except current user
        $all_users = UserModel::getPublicProfilesOfAllUsers();
        $current_user_id = Session::get('user_id');

        // Filter out current user
        $available_users = array_filter($all_users, function($user) use ($current_user_id) {
            return $user->user_id != $current_user_id;
        });

        $this->View->render('message/createGroup', array(
            'users' => $available_users
        ));
    }

    /**
     * Create a new group (POST action)
     * Expects: group_name, members[] (array of user IDs)
     */
    public function createGroup()
    {
        $group_name = Request::post('group_name');
        $members = Request::post('members');

        if (!$group_name) {
            Session::add('feedback_negative', 'Group name is required');
            Redirect::to('message/createGroupForm');
            return;
        }

        // Ensure members is an array
        if (!is_array($members)) {
            $members = array();
        }

        $group_id = GroupModel::createGroup($group_name, $members);

        if ($group_id) {
            Redirect::to('message/groupConversation/' . $group_id);
        } else {
            Redirect::to('message/createGroupForm');
        }
    }

    /**
     * Show group conversation
     * @param int $group_id Group ID
     */
    public function groupConversation($group_id)
    {
        if (!$group_id || !is_numeric($group_id)) {
            Redirect::to('message');
            return;
        }

        // Get group info
        $group = GroupModel::getGroup($group_id);

        if (!$group) {
            Session::add('feedback_negative', 'Group not found or access denied');
            Redirect::to('message');
            return;
        }

        $this->View->render('message/groupConversation', array(
            'group' => $group,
            'messages' => MessageModel::getGroupConversation($group_id),
            'members' => GroupModel::getGroupMembers($group_id),
            'conversations' => MessageModel::getAllConversations(),
            'groups' => GroupModel::getUserGroups()
        ));
    }

    /**
     * Send a message to a custom group (POST action)
     * Expects: group_id, message_text
     */
    public function sendToGroup()
    {
        $group_id = Request::post('group_id');
        $message_text = Request::post('message_text');

        if ($group_id && $message_text) {
            MessageModel::sendCustomGroupMessage($group_id, $message_text);
        }

        // Redirect back to the group conversation
        if ($group_id) {
            Redirect::to('message/groupConversation/' . $group_id);
        } else {
            Redirect::to('message');
        }
    }

    /**
     * Show conversation with a specific user
     * @param int $user_id ID of the user to chat with
     */
    public function conversation($user_id = null)
    {
        if (!$user_id || !is_numeric($user_id)) {
            Session::add('feedback_negative', 'Invalid user ID provided');
            Redirect::to('message');
            return;
        }

        // Don't allow chatting with yourself
        if ($user_id == Session::get('user_id')) {
            Session::add('feedback_negative', 'You cannot chat with yourself');
            Redirect::to('message');
            return;
        }

        // Get the other user's info
        $other_user = UserModel::getPublicProfileOfUser($user_id);

        // Check if user exists
        if (!$other_user) {
            Session::add('feedback_negative', 'User not found. Please try again.');
            Redirect::to('message');
            return;
        }

        // Check if user is active and not deleted
        if ($other_user->user_deleted == 1) {
            Session::add('feedback_negative', 'This user account has been deleted');
            Redirect::to('message');
            return;
        }

        if ($other_user->user_active != 1) {
            Session::add('feedback_negative', 'This user account is not active');
            Redirect::to('message');
            return;
        }

        // Mark all messages in this conversation as read
        MessageModel::markConversationAsRead($user_id);

        $this->View->render('message/conversation', array(
            'messages' => MessageModel::getConversation($user_id),
            'other_user' => $other_user,
            'conversations' => MessageModel::getAllConversations()
        ));
    }

    /**
     * Send a message (POST action)
     * Expects: receiver_id, message_text
     */
    public function send()
    {
        $receiver_id = Request::post('receiver_id');
        $message_text = Request::post('message_text');

        if ($receiver_id && $message_text) {
            MessageModel::sendMessage($receiver_id, $message_text);
        }

        // Redirect back to the conversation
        if ($receiver_id) {
            Redirect::to('message/conversation/' . $receiver_id);
        } else {
            Redirect::to('message');
        }
    }

    /**
     * Send a message via URL (for testing purposes)
     * URL format: /message/sendToUser/{user_id}?text=Hello
     * @param int $user_id ID of the user to send message to
     */
    public function sendToUser($user_id)
    {
        if (!$user_id || !is_numeric($user_id)) {
            Session::add('feedback_negative', 'Invalid user ID');
            Redirect::to('message');
            return;
        }

        $message_text = Request::get('text');

        if (!$message_text) {
            Session::add('feedback_negative', 'No message text provided. Use ?text=YourMessage');
            Redirect::to('message');
            return;
        }

        MessageModel::sendMessage($user_id, $message_text);
        Redirect::to('message/conversation/' . $user_id);
    }

    /**
     * Show group messages (admin only)
     */
    public function group()
    {
        if (Session::get('user_account_type') != 7) {
            Session::add('feedback_negative', 'Access denied. Admin only.');
            Redirect::to('message');
            return;
        }

        $this->View->render('message/group', array(
            'group_messages' => MessageModel::getGroupMessages(),
            'conversations' => MessageModel::getAllConversations()
        ));
    }

    /**
     * Send a group message (POST action)
     * Expects: receiver_group, message_text
     */
    public function sendGroup()
    {
        if (Session::get('user_account_type') != 7) {
            Session::add('feedback_negative', 'Access denied. Admin only.');
            Redirect::to('message');
            return;
        }

        $receiver_group = Request::post('receiver_group');
        $message_text = Request::post('message_text');

        if ($receiver_group && $message_text) {
            MessageModel::sendGroupMessage($receiver_group, $message_text);
        }

        Redirect::to('message/group');
    }
}
