<?php

/**
 * MessageController
 * Verwaltet alle Messenger-Funktionen der Anwendung
 *
 * Dieser Controller ist zuständig für:
 * - Anzeige von Nachrichten
 * - Senden von Nachrichten
 * - Erstellen und Verwalten von Gruppenchats
 */
class MessageController extends Controller
{
    /**
     * Konstruktor - wird beim Erstellen des Controllers aufgerufen
     *
     * Prüft ob der Benutzer eingeloggt ist, bevor er Zugriff erhält
     */
    public function __construct()
    {
        parent::__construct();

        // Nur eingeloggte Benutzer dürfen den Messenger verwenden
        Auth::checkAuthentication();
    }

    /**
     * Hauptseite des Messengers
     *
     * Zeigt eine Übersicht aller Konversationen (1-zu-1 Chats) und Gruppen
     * Dies ist die erste Seite, die man sieht wenn man auf "Messages" klickt
     */
    public function index()
    {
        // Daten für die View vorbereiten und an die View übergeben
        $this->View->render('message/index', array(
            'conversations' => MessageModel::getAllConversations(),  // Alle 1-zu-1 Chats
            'groups' => GroupModel::getUserGroups(),                 // Alle Gruppen des Benutzers
            'unread_count' => MessageModel::getUnreadCount()         // Anzahl ungelesener Nachrichten
        ));
    }

    /**
     * Seite zum Starten eines neuen Chats
     *
     * Zeigt eine Liste aller Benutzer an, mit denen man chatten kann
     */
    public function newChat()
    {
        // Hole alle Benutzer aus der Datenbank
        $all_users = UserModel::getPublicProfilesOfAllUsers();
        $current_user_id = Session::get('user_id');

        // Filtere den aktuellen Benutzer aus der Liste raus
        // (man kann nicht mit sich selbst chatten)
        $available_users = array_filter($all_users, function($user) use ($current_user_id) {
            return $user->user_id != $current_user_id;
        });

        // Zeige die Benutzerliste an
        $this->View->render('message/newChat', array(
            'users' => $available_users
        ));
    }

    /**
     * Formular zum Erstellen einer neuen Gruppe
     *
     * Zeigt eine Liste aller Benutzer, die man zur Gruppe hinzufügen kann
     */
    public function createGroupForm()
    {
        // Hole alle Benutzer aus der Datenbank
        $all_users = UserModel::getPublicProfilesOfAllUsers();
        $current_user_id = Session::get('user_id');

        // Filtere den aktuellen Benutzer aus der Liste
        $available_users = array_filter($all_users, function($user) use ($current_user_id) {
            return $user->user_id != $current_user_id;
        });

        // Zeige das Formular zum Erstellen einer Gruppe
        $this->View->render('message/createGroup', array(
            'users' => $available_users
        ));
    }

    /**
     * Erstellt eine neue Gruppe (nach Formular-Absendung)
     *
     * Erwartet:
     * - group_name: Name der Gruppe
     * - members[]: Array mit User-IDs der Mitglieder
     */
    public function createGroup()
    {
        // Hole die Daten aus dem Formular
        $group_name = Request::post('group_name');
        $members = Request::post('members');

        // Prüfe ob ein Gruppenname eingegeben wurde
        if (!$group_name) {
            Session::add('feedback_negative', 'Group name is required');
            Redirect::to('message/createGroupForm');
            return;
        }

        // Stelle sicher, dass members ein Array ist (falls nichts ausgewählt wurde)
        if (!is_array($members)) {
            $members = array();
        }

        // Erstelle die Gruppe in der Datenbank
        $group_id = GroupModel::createGroup($group_name, $members);

        // Wenn erfolgreich, leite zur Gruppen-Konversation weiter
        if ($group_id) {
            Redirect::to('message/groupConversation/' . $group_id);
        } else {
            // Bei Fehler, zurück zum Formular
            Redirect::to('message/createGroupForm');
        }
    }

    /**
     * Zeigt die Chat-Oberfläche einer Gruppe
     *
     * @param int $group_id ID der Gruppe
     *
     * Hier sieht man alle Nachrichten in der Gruppe und kann neue schreiben
     */
    public function groupConversation($group_id)
    {
        // Prüfe ob eine gültige Gruppen-ID übergeben wurde
        if (!$group_id || !is_numeric($group_id)) {
            Redirect::to('message');
            return;
        }

        // Hole Gruppen-Informationen aus der Datenbank
        $group = GroupModel::getGroup($group_id);

        // Prüfe ob die Gruppe existiert und der Benutzer Zugriff hat
        if (!$group) {
            Session::add('feedback_negative', 'Group not found or access denied');
            Redirect::to('message');
            return;
        }

        // Zeige die Gruppen-Chat-Oberfläche mit allen Nachrichten
        $this->View->render('message/groupConversation', array(
            'group' => $group,                                        // Gruppen-Informationen
            'messages' => MessageModel::getGroupConversation($group_id), // Alle Nachrichten
            'members' => GroupModel::getGroupMembers($group_id),      // Alle Mitglieder
            'conversations' => MessageModel::getAllConversations(),   // Für die Seitenleiste
            'groups' => GroupModel::getUserGroups()                   // Für die Seitenleiste
        ));
    }

    /**
     * Sendet eine Nachricht an eine Gruppe (nach Formular-Absendung)
     *
     * Erwartet:
     * - group_id: ID der Gruppe
     * - message_text: Text der Nachricht
     */
    public function sendToGroup()
    {
        // Hole die Daten aus dem Formular
        $group_id = Request::post('group_id');
        $message_text = Request::post('message_text');

        // Wenn beide Werte vorhanden sind, sende die Nachricht
        if ($group_id && $message_text) {
            MessageModel::sendCustomGroupMessage($group_id, $message_text);
        }

        // Leite zurück zur Gruppen-Konversation
        if ($group_id) {
            Redirect::to('message/groupConversation/' . $group_id);
        } else {
            Redirect::to('message');
        }
    }

    /**
     * Zeigt die Chat-Oberfläche mit einem bestimmten Benutzer (1-zu-1 Chat)
     *
     * @param int $user_id ID des Benutzers, mit dem man chattet
     *
     * Dies ist die Haupt-Chat-Funktion für private Nachrichten
     */
    public function conversation($user_id = null)
    {
        // Prüfe ob eine gültige User-ID übergeben wurde
        if (!$user_id || !is_numeric($user_id)) {
            Session::add('feedback_negative', 'Invalid user ID provided');
            Redirect::to('message');
            return;
        }

        // Verhindere, dass man mit sich selbst chattet
        if ($user_id == Session::get('user_id')) {
            Session::add('feedback_negative', 'You cannot chat with yourself');
            Redirect::to('message');
            return;
        }

        // Hole Informationen über den anderen Benutzer
        $other_user = UserModel::getPublicProfileOfUser($user_id);

        // Prüfe ob der Benutzer existiert
        if (!$other_user) {
            Session::add('feedback_negative', 'User not found. Please try again.');
            Redirect::to('message');
            return;
        }

        // Prüfe ob das Benutzerkonto gelöscht wurde
        if ($other_user->user_deleted == 1) {
            Session::add('feedback_negative', 'This user account has been deleted');
            Redirect::to('message');
            return;
        }

        // Prüfe ob das Benutzerkonto aktiv ist
        if ($other_user->user_active != 1) {
            Session::add('feedback_negative', 'This user account is not active');
            Redirect::to('message');
            return;
        }

        // Markiere alle Nachrichten in dieser Konversation als gelesen
        MessageModel::markConversationAsRead($user_id);

        // Zeige die Chat-Oberfläche mit allen Nachrichten
        $this->View->render('message/conversation', array(
            'messages' => MessageModel::getConversation($user_id),    // Alle Nachrichten zwischen beiden Benutzern
            'other_user' => $other_user,                              // Info über den Gesprächspartner
            'conversations' => MessageModel::getAllConversations()    // Alle Konversationen für Seitenleiste
        ));
    }

    /**
     * Sendet eine private Nachricht (nach Formular-Absendung)
     *
     * Erwartet:
     * - receiver_id: ID des Empfängers
     * - message_text: Text der Nachricht
     *
     * Dies wird aufgerufen wenn man auf "Send" klickt
     */
    public function send()
    {
        // Hole die Daten aus dem Formular
        $receiver_id = Request::post('receiver_id');
        $message_text = Request::post('message_text');

        // Wenn beide Werte vorhanden sind, sende die Nachricht
        if ($receiver_id && $message_text) {
            MessageModel::sendMessage($receiver_id, $message_text);
        }

        // Leite zurück zur Konversation
        if ($receiver_id) {
            Redirect::to('message/conversation/' . $receiver_id);
        } else {
            Redirect::to('message');
        }
    }

    /**
     * Sendet eine Nachricht über die URL (für Test-Zwecke)
     *
     * URL-Format: /message/sendToUser/2?text=Hallo
     *
     * @param int $user_id ID des Empfängers
     *
     * Diese Funktion ist nützlich zum Testen der Nachrichtenfunktion
     */
    public function sendToUser($user_id)
    {
        // Prüfe ob eine gültige User-ID übergeben wurde
        if (!$user_id || !is_numeric($user_id)) {
            Session::add('feedback_negative', 'Invalid user ID');
            Redirect::to('message');
            return;
        }

        // Hole den Nachrichtentext aus der URL
        $message_text = Request::get('text');

        // Prüfe ob ein Text vorhanden ist
        if (!$message_text) {
            Session::add('feedback_negative', 'No message text provided. Use ?text=YourMessage');
            Redirect::to('message');
            return;
        }

        // Sende die Nachricht
        MessageModel::sendMessage($user_id, $message_text);

        // Leite zur Konversation weiter
        Redirect::to('message/conversation/' . $user_id);
    }

    /**
     * Gruppennachrichten-Seite (nur für Admins)
     *
     * Diese Funktion ist für eine ältere Gruppennachrichten-Funktion
     * und wird nur von Admins verwendet
     */
    public function group()
    {
        // Prüfe ob der Benutzer Admin ist (Account-Typ 7)
        if (Session::get('user_account_type') != 7) {
            Session::add('feedback_negative', 'Access denied. Admin only.');
            Redirect::to('message');
            return;
        }

        // Zeige Admin-Gruppennachrichten
        $this->View->render('message/group', array(
            'group_messages' => MessageModel::getGroupMessages(),
            'conversations' => MessageModel::getAllConversations()
        ));
    }

    /**
     * Sendet eine Admin-Gruppennachricht (nur für Admins)
     *
     * Erwartet:
     * - receiver_group: Empfänger-Gruppe
     * - message_text: Nachrichtentext
     */
    public function sendGroup()
    {
        // Prüfe ob der Benutzer Admin ist
        if (Session::get('user_account_type') != 7) {
            Session::add('feedback_negative', 'Access denied. Admin only.');
            Redirect::to('message');
            return;
        }

        // Hole die Daten aus dem Formular
        $receiver_group = Request::post('receiver_group');
        $message_text = Request::post('message_text');

        // Wenn beide Werte vorhanden, sende die Nachricht
        if ($receiver_group && $message_text) {
            MessageModel::sendGroupMessage($receiver_group, $message_text);
        }

        // Leite zurück zur Gruppennachrichten-Seite
        Redirect::to('message/group');
    }
}
