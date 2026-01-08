<?php

/**
 * MessageModel
 * Verwaltet alle Datenbank-Operationen für das Messenger-System
 *
 * Dieses Model ist zuständig für:
 * - Senden von Nachrichten
 * - Abrufen von Konversationen
 * - Markieren von Nachrichten als gelesen
 * - Zählen von ungelesenen Nachrichten
 */
class MessageModel
{
    /**
     * Sendet eine private Nachricht an einen bestimmten Benutzer (1-zu-1 Chat)
     *
     * @param int $receiver_id ID des Empfängers
     * @param string $message_text Text der Nachricht
     * @return bool true bei Erfolg, false bei Fehler
     *
     * Beispiel: MessageModel::sendMessage(5, "Hallo, wie geht es dir?");
     * Sendet eine Nachricht an den Benutzer mit der ID 5
     */
    public static function sendMessage($receiver_id, $message_text)
    {
        // Eingabe-Validierung: Prüfe ob alle Daten vorhanden sind
        if (!$receiver_id || !$message_text || strlen($message_text) == 0) {
            Session::add('feedback_negative', 'Message sending failed: Invalid data');
            return false;
        }

        // Datenbankverbindung herstellen
        $database = DatabaseFactory::getFactory()->getConnection();

        // SQL-Befehl zum Einfügen einer neuen Nachricht
        // INSERT INTO fügt einen neuen Datensatz in die "messages" Tabelle ein
        $sql = "INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (:sender_id, :receiver_id, :message_text)";

        // Prepared Statement vorbereiten (schützt vor SQL-Injection)
        $query = $database->prepare($sql);

        // Werte einsetzen und SQL ausführen
        $query->execute(array(
            ':sender_id' => Session::get('user_id'),    // Aktueller eingeloggter Benutzer
            ':receiver_id' => $receiver_id,             // Empfänger der Nachricht
            ':message_text' => $message_text            // Text der Nachricht
        ));

        // Prüfe ob genau 1 Zeile eingefügt wurde (= Erfolg)
        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', 'Message sent successfully');
            return true;
        }

        // Wenn nichts eingefügt wurde, ist ein Fehler aufgetreten
        Session::add('feedback_negative', 'Message sending failed');
        return false;
    }

    /**
     * Sendet eine Nachricht an eine Admin-Gruppe
     * (Dies ist eine ältere Funktion für Admin-Nachrichten)
     *
     * @param string $receiver_group Name der Gruppe (z.B. 'admin')
     * @param string $message_text Text der Nachricht
     * @return bool true bei Erfolg, false bei Fehler
     */
    public static function sendGroupMessage($receiver_group, $message_text)
    {
        // Validierung der Eingabedaten
        if (!$receiver_group || !$message_text || strlen($message_text) == 0) {
            Session::add('feedback_negative', 'Group message sending failed: Invalid data');
            return false;
        }

        // Datenbankverbindung herstellen
        $database = DatabaseFactory::getFactory()->getConnection();

        // SQL zum Einfügen einer Gruppen-Nachricht
        $sql = "INSERT INTO messages (sender_id, receiver_group, message_text) VALUES (:sender_id, :receiver_group, :message_text)";

        // Prepared Statement vorbereiten und ausführen
        $query = $database->prepare($sql);
        $query->execute(array(
            ':sender_id' => Session::get('user_id'),
            ':receiver_group' => $receiver_group,
            ':message_text' => $message_text
        ));

        // Erfolgsmeldung oder Fehlermeldung
        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', 'Group message sent successfully');
            return true;
        }

        Session::add('feedback_negative', 'Group message sending failed');
        return false;
    }

    /**
     * Sendet eine Nachricht an eine benutzerdefinierte Gruppe (Gruppenchat)
     *
     * @param int $group_id ID der Gruppe
     * @param string $message_text Text der Nachricht
     * @return bool true bei Erfolg, false bei Fehler
     *
     * Dies wird verwendet wenn man in einem Gruppenchat eine Nachricht schreibt
     */
    public static function sendCustomGroupMessage($group_id, $message_text)
    {
        // Validierung der Eingabedaten
        if (!$group_id || !$message_text || strlen($message_text) == 0) {
            Session::add('feedback_negative', 'Message sending failed: Invalid data');
            return false;
        }

        // Sicherheitsprüfung: Ist der Benutzer überhaupt Mitglied dieser Gruppe?
        if (!GroupModel::isMember($group_id, Session::get('user_id'))) {
            Session::add('feedback_negative', 'You are not a member of this group');
            return false;
        }

        // Datenbankverbindung herstellen
        $database = DatabaseFactory::getFactory()->getConnection();

        // SQL zum Einfügen der Gruppen-Nachricht
        $sql = "INSERT INTO messages (sender_id, group_id, message_text) VALUES (:sender_id, :group_id, :message_text)";

        // Prepared Statement vorbereiten und ausführen
        $query = $database->prepare($sql);
        $query->execute(array(
            ':sender_id' => Session::get('user_id'),
            ':group_id' => $group_id,
            ':message_text' => $message_text
        ));

        // Erfolgsmeldung oder Fehlermeldung
        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', 'Message sent successfully');
            return true;
        }

        Session::add('feedback_negative', 'Message sending failed');
        return false;
    }

    /**
     * Holt alle Nachrichten einer Gruppe (Gruppenchat)
     *
     * @param int $group_id ID der Gruppe
     * @return array Array mit allen Nachrichten der Gruppe
     *
     * Die Nachrichten werden chronologisch sortiert (älteste zuerst)
     */
    public static function getGroupConversation($group_id)
    {
        // Datenbankverbindung herstellen
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        // Sicherheitsprüfung: Nur Mitglieder dürfen Nachrichten sehen
        if (!GroupModel::isMember($group_id, $current_user_id)) {
            return array(); // Leeres Array zurückgeben wenn kein Zugriff
        }

        // SQL zum Abrufen aller Nachrichten
        // LEFT JOIN verbindet die messages-Tabelle mit der users-Tabelle
        // um den Namen des Absenders zu bekommen
        $sql = "SELECT m.*, sender.user_name as sender_name, sender.user_has_avatar as sender_has_avatar
                FROM messages m
                LEFT JOIN users sender ON m.sender_id = sender.user_id
                WHERE m.group_id = :group_id
                ORDER BY m.created_at ASC";  // ASC = aufsteigend (älteste zuerst)

        // SQL ausführen
        $query = $database->prepare($sql);
        $query->execute(array(':group_id' => $group_id));

        // Alle Nachrichten als Array zurückgeben
        return $query->fetchAll();
    }

    /**
     * Holt alle Nachrichten einer 1-zu-1 Konversation
     *
     * @param int $user_id ID des Gesprächspartners
     * @return array Array mit allen Nachrichten zwischen beiden Benutzern
     *
     * Beispiel: getConversation(5) holt alle Nachrichten zwischen
     * dem aktuellen Benutzer und Benutzer mit ID 5
     */
    public static function getConversation($user_id)
    {
        // Datenbankverbindung herstellen
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        // SQL zum Abrufen aller Nachrichten zwischen zwei Benutzern
        // Diese Abfrage holt Nachrichten in beide Richtungen:
        // - Nachrichten die ich gesendet habe
        // - Nachrichten die ich empfangen habe
        $sql = "SELECT m.*,
                       sender.user_name as sender_name,
                       sender.user_has_avatar as sender_has_avatar,
                       receiver.user_name as receiver_name,
                       receiver.user_has_avatar as receiver_has_avatar
                FROM messages m
                LEFT JOIN users sender ON m.sender_id = sender.user_id
                LEFT JOIN users receiver ON m.receiver_id = receiver.user_id
                WHERE (m.sender_id = :current_user_id AND m.receiver_id = :user_id)
                   OR (m.sender_id = :user_id AND m.receiver_id = :current_user_id)
                ORDER BY m.created_at ASC";  // Chronologisch sortieren

        // SQL ausführen
        $query = $database->prepare($sql);
        $query->execute(array(
            ':current_user_id' => $current_user_id,
            ':user_id' => $user_id
        ));

        // Alle Nachrichten als Array zurückgeben
        return $query->fetchAll();
    }

    /**
     * Holt alle Konversationen des aktuellen Benutzers
     *
     * @return array Liste aller Benutzer mit denen man Konversationen hat
     *
     * Für jeden Benutzer wird angezeigt:
     * - Name
     * - Avatar
     * - Letzte Nachricht
     * - Anzahl ungelesener Nachrichten
     *
     * Diese Funktion wird für die Seitenleiste verwendet
     */
    public static function getAllConversations()
    {
        // Datenbankverbindung herstellen
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        // Komplexe SQL-Abfrage die folgendes macht:
        // 1. Findet alle Benutzer mit denen man Nachrichten ausgetauscht hat
        // 2. Holt die letzte Nachricht jeder Konversation
        // 3. Zählt die ungelesenen Nachrichten pro Konversation
        $sql = "SELECT u.user_id, u.user_name, u.user_has_avatar,
                       latest.message_text as last_message,
                       latest.created_at as last_message_time,
                       latest.sender_id as last_sender_id,
                       COUNT(CASE WHEN m.is_read = 0 AND m.receiver_id = :current_user_id THEN 1 END) as unread_count
                FROM users u
                INNER JOIN messages latest ON (
                    (latest.sender_id = u.user_id AND latest.receiver_id = :current_user_id)
                    OR (latest.receiver_id = u.user_id AND latest.sender_id = :current_user_id)
                )
                LEFT JOIN messages m ON (
                    (m.sender_id = u.user_id AND m.receiver_id = :current_user_id)
                    OR (m.receiver_id = u.user_id AND m.sender_id = :current_user_id)
                )
                WHERE latest.message_id = (
                    SELECT MAX(m2.message_id)
                    FROM messages m2
                    WHERE (m2.sender_id = u.user_id AND m2.receiver_id = :current_user_id)
                       OR (m2.receiver_id = u.user_id AND m2.sender_id = :current_user_id)
                )
                GROUP BY u.user_id, u.user_name, u.user_has_avatar, latest.message_text, latest.created_at, latest.sender_id
                ORDER BY latest.created_at DESC";  // Neueste Konversationen zuerst

        // SQL ausführen
        $query = $database->prepare($sql);
        $query->execute(array(':current_user_id' => $current_user_id));

        // Alle Konversationen als Array zurückgeben
        return $query->fetchAll();
    }

    /**
     * Zählt alle ungelesenen Nachrichten des aktuellen Benutzers
     *
     * @return int Anzahl der ungelesenen Nachrichten
     *
     * Diese Funktion wird verwendet um den roten Badge mit der Zahl
     * im Navigationsmenü anzuzeigen
     */
    public static function getUnreadCount()
    {
        // Datenbankverbindung herstellen
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        // SQL zum Zählen ungelesener privater Nachrichten
        // is_read = 0 bedeutet: Nachricht wurde noch nicht gelesen
        $sql = "SELECT COUNT(*) as unread_count
                FROM messages
                WHERE receiver_id = :user_id AND is_read = 0";

        // SQL ausführen
        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => $current_user_id));
        $result = $query->fetch();

        // Anzahl ungelesener privater Nachrichten
        $individual_count = $result ? $result->unread_count : 0;

        // Zähle auch ungelesene Admin-Gruppennachrichten (falls Admin)
        $user_type = Session::get('user_account_type');
        $group_count = 0;

        // Prüfe ob Benutzer ein Admin ist (Account-Typ 7)
        if ($user_type == 7) {
            $sql_group = "SELECT COUNT(*) as unread_count
                          FROM messages
                          WHERE receiver_group = 'admin' AND is_read = 0 AND sender_id != :user_id";

            $query_group = $database->prepare($sql_group);
            $query_group->execute(array(':user_id' => $current_user_id));
            $result_group = $query_group->fetch();

            $group_count = $result_group ? $result_group->unread_count : 0;
        }

        // Addiere beide Zahlen und gebe Gesamtzahl zurück
        return $individual_count + $group_count;
    }

    /**
     * Markiert eine einzelne Nachricht als gelesen
     *
     * @param int $message_id ID der Nachricht
     * @return bool true bei Erfolg, false bei Fehler
     *
     * Dies wird verwendet wenn eine Nachricht geöffnet wird
     */
    public static function markAsRead($message_id)
    {
        // Datenbankverbindung herstellen
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        // SQL zum Aktualisieren des is_read Feldes
        // UPDATE ändert einen bestehenden Datensatz
        $sql = "UPDATE messages SET is_read = 1
                WHERE message_id = :message_id
                AND receiver_id = :user_id
                LIMIT 1";  // Nur eine Nachricht aktualisieren

        // SQL ausführen
        $query = $database->prepare($sql);
        $query->execute(array(
            ':message_id' => $message_id,
            ':user_id' => $current_user_id
        ));

        // Prüfe ob genau 1 Zeile aktualisiert wurde
        return $query->rowCount() == 1;
    }

    /**
     * Markiert alle Nachrichten einer Konversation als gelesen
     *
     * @param int $user_id ID des Gesprächspartners
     * @return bool true (immer erfolgreich)
     *
     * Dies wird automatisch aufgerufen wenn man eine Konversation öffnet
     * Alle ungelesenen Nachrichten von diesem Benutzer werden als gelesen markiert
     */
    public static function markConversationAsRead($user_id)
    {
        // Datenbankverbindung herstellen
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        // SQL zum Markieren aller Nachrichten als gelesen
        // UPDATE alle Nachrichten die:
        // - Von $user_id gesendet wurden
        // - An mich adressiert sind
        // - Noch ungelesen sind (is_read = 0)
        $sql = "UPDATE messages SET is_read = 1
                WHERE sender_id = :user_id
                AND receiver_id = :current_user_id
                AND is_read = 0";

        // SQL ausführen
        $query = $database->prepare($sql);
        $query->execute(array(
            ':user_id' => $user_id,
            ':current_user_id' => $current_user_id
        ));

        return true;
    }

    /**
     * Holt alle Admin-Gruppennachrichten (nur für Admins)
     *
     * @return array Array mit allen Admin-Gruppennachrichten
     *
     * Dies ist für die ältere Admin-Gruppennachrichten-Funktion
     */
    public static function getGroupMessages()
    {
        // Datenbankverbindung herstellen
        $database = DatabaseFactory::getFactory()->getConnection();
        $user_type = Session::get('user_account_type');

        // Nur Admins (Account-Typ 7) dürfen Admin-Nachrichten sehen
        if ($user_type != 7) {
            return array(); // Leeres Array wenn kein Admin
        }

        // SQL zum Abrufen aller Admin-Gruppennachrichten
        $sql = "SELECT m.*, sender.user_name as sender_name, sender.user_has_avatar as sender_has_avatar
                FROM messages m
                LEFT JOIN users sender ON m.sender_id = sender.user_id
                WHERE m.receiver_group = 'admin'
                ORDER BY m.created_at DESC";  // Neueste zuerst

        // SQL ausführen
        $query = $database->prepare($sql);
        $query->execute();

        // Alle Nachrichten als Array zurückgeben
        return $query->fetchAll();
    }
}
