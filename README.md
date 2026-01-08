# HUGE - PHP User Authentication & Messaging System

Ein vollständiges User Authentication System mit integriertem Messaging-System und Gruppenchat-Funktionalität.

## Übersicht

HUGE ist eine erweiterte PHP-Webanwendung, die auf dem beliebten "php-login" Framework basiert und um umfangreiche Kommunikationsfunktionen erweitert wurde. Die Anwendung bietet neben einer sicheren Benutzerverwaltung ein vollständiges Nachrichtensystem mit Einzel- und Gruppenchats.

## Hauptfunktionen

### Authentifizierung & Benutzerverwaltung
* Sichere Benutzerregistrierung und Login mit bcrypt Passwort-Hashing
* Email-Verifizierung für neue Accounts
* Passwort-zurücksetzen Funktion
* Remember-Me Funktion (Login via Cookie)
* Captcha-Schutz gegen Spam-Registrierungen
* CSRF-Schutz für sensible Formulare
* Benutzerprofile mit Avatar-Support (lokal oder Gravatar)
* Admin-Panel zur Benutzerverwaltung
* Verschiedene Benutzerrollen (Normal, Premium, Admin)

### Messaging-System

#### 1. Direkte Nachrichten (1-zu-1 Chat)
Das Messaging-System ermöglicht private Konversationen zwischen zwei Benutzern:

**Features:**
* Echtzeit-ähnliche Chat-Oberfläche mit modernem Design
* Automatisches Laden aller Nachrichten in chronologischer Reihenfolge
* Unterscheidung zwischen eigenen und fremden Nachrichten:
  - **Eigene Nachrichten**: Erscheinen rechts, blauer Hintergrund mit weißem Text
  - **Nachrichten des Gesprächspartners**: Erscheinen links, weißer Hintergrund mit dunklem Text
* Zeitstempel für jede Nachricht (z.B. "8:21 AM")
* Datum-Trennlinien (z.B. "Today", "Yesterday", oder vollständiges Datum)
* Ungelesene Nachrichten-Zähler in der Seitenleiste
* Responsive Design - funktioniert auf Desktop und Mobile

**Wie es funktioniert:**
1. Gehe zu "Messages" im Menü
2. Klicke auf einen Benutzer oder auf "+ New" um einen neuen Chat zu starten
3. Schreibe deine Nachricht im Eingabefeld
4. Drücke "Send" oder Enter zum Absenden
5. Shift+Enter erstellt eine neue Zeile ohne zu senden

**Technische Details:**
* Nachrichten werden in der `messages` Tabelle gespeichert
* Jede Nachricht hat: sender_id, receiver_id, message_text, created_at, is_read
* Automatisches Scrollen zum neuesten Nachricht beim Laden
* Auto-resize des Textfeldes während der Eingabe

#### 2. Gruppenchats
Das System unterstützt Gruppenchats für Kommunikation zwischen mehreren Benutzern:

**Features:**
* Erstellen von Gruppen mit beliebig vielen Mitgliedern
* Gruppen-Name und Gruppen-Icon (👥)
* Anzeige aller Gruppenmitglieder im Header
* Sender-Namen bei jeder Nachricht in Gruppen
* Farbliche Unterscheidung von Gruppen in der Seitenleiste (Lila-Gradient)
* Ungelesene Nachrichten-Zähler für Gruppen
* Gleiche Chat-Oberfläche wie bei Direktnachrichten

**Wie es funktioniert:**
1. Klicke auf "Messages" und dann auf "+ New"
2. Wähle "Create Group"
3. Gebe einen Gruppennamen ein
4. Wähle die Mitglieder aus der Liste
5. Klicke auf "Create Group"
6. Die Gruppe erscheint nun in der Seitenleiste
7. Klicke auf die Gruppe um Nachrichten zu senden

**Technische Details:**
* Gruppen werden in der `groups` Tabelle gespeichert
* Gruppenmitglieder in der `group_members` Tabelle
* Gruppennachrichten in der `group_messages` Tabelle
* Jede Nachricht enthält: group_id, sender_id, message_text, created_at, is_read

#### 3. Chat-Oberfläche Features

**Seitenleiste:**
* Zeigt alle aktiven Konversationen und Gruppen
* Gruppenchats haben ein spezielles Gruppen-Icon
* Ungelesene Nachrichten werden mit Badge angezeigt
* Aktive Konversation ist farblich hervorgehoben
* Schnelle Navigation zwischen Chats

**Nachrichtenbereich:**
* Moderner Gradient-Hintergrund (Lila-Töne)
* Nachrichtenblasen mit abgerundeten Ecken
* Unterschiedliche Farben für eigene/fremde Nachrichten
* Zeitstempel bei jeder Nachricht
* Datum-Trennlinien für bessere Übersicht
* Auto-Scroll zu neuen Nachrichten

**Eingabebereich:**
* Flexibles Textarea-Feld
* Auto-Resize während der Eingabe
* Enter zum Senden, Shift+Enter für neue Zeile
* "Send" Button mit Hover-Effekt

### Notizen-System
* Erstellen, Bearbeiten und Löschen von persönlichen Notizen
* Jeder Benutzer sieht nur seine eigenen Notizen
* CRUD-Funktionalität vollständig implementiert

## Installation

### Voraussetzungen
* PHP 5.5 oder höher
* MySQL 5.5 oder höher
* Apache Webserver mit mod_rewrite aktiviert (oder NGINX)
* Composer für Dependency Management
* PHP-Erweiterungen: PDO, GD, OpenSSL

### Schnell-Installation

1. **Repository klonen:**
```bash
git clone https://github.com/yourusername/huge-app.git
cd huge-app
```

2. **Composer Abhängigkeiten installieren:**
```bash
composer install
```

3. **Datenbank erstellen:**
```bash
mysql -u root -p < application/_installation/01-create-database.sql
mysql -u root -p < application/_installation/02-create-table-users.sql
mysql -u root -p < application/_installation/03-create-table-notes.sql
```

4. **Messaging-Tabellen installieren:**
```bash
php public/install-messages-table.php
php public/install-messenger-complete.php
```

5. **Konfiguration anpassen:**
Bearbeite `application/config/config.development.php` und setze deine Datenbank-Zugangsdaten:
```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'huge');
define('DB_USER', 'root');
define('DB_PASS', 'dein_passwort');
```

6. **Avatar-Ordner beschreibbar machen:**
```bash
chmod 0777 public/avatars
```

7. **Fertig!** Öffne die Anwendung im Browser.

### Demo-Benutzer

**Admin-Benutzer:**
* Username: `demo`
* Passwort: `12345678`
* Rechte: Volle Admin-Rechte, kann Benutzer verwalten

**Normal-Benutzer:**
* Username: `demo2`
* Passwort: `12345678`
* Rechte: Standard-Benutzer

## Datenbank-Struktur

### Messaging-Tabellen

**messages:**
* `message_id` - Eindeutige ID
* `sender_id` - User ID des Absenders
* `receiver_id` - User ID des Empfängers
* `message_text` - Nachrichteninhalt
* `created_at` - Zeitstempel
* `is_read` - Gelesen-Status (0 oder 1)

**groups:**
* `group_id` - Eindeutige Gruppen-ID
* `group_name` - Name der Gruppe
* `created_by` - User ID des Erstellers
* `created_at` - Zeitstempel

**group_members:**
* `id` - Eindeutige ID
* `group_id` - Referenz zur Gruppe
* `user_id` - Referenz zum Benutzer
* `joined_at` - Zeitstempel

**group_messages:**
* `message_id` - Eindeutige ID
* `group_id` - Referenz zur Gruppe
* `sender_id` - User ID des Absenders
* `message_text` - Nachrichteninhalt
* `created_at` - Zeitstempel
* `is_read` - Gelesen-Status

## Projekt-Struktur

```
huge-app/
├── application/
│   ├── controller/          # Controller-Klassen (MVC)
│   │   ├── MessageController.php  # Messaging-Logik
│   │   ├── UserController.php
│   │   ├── LoginController.php
│   │   └── ...
│   ├── model/              # Model-Klassen
│   │   ├── MessageModel.php      # Messaging-Datenbank-Logik
│   │   ├── UserModel.php
│   │   └── ...
│   ├── view/               # View-Dateien (Templates)
│   │   ├── message/
│   │   │   ├── index.php           # Nachrichten-Übersicht
│   │   │   ├── conversation.php    # 1-zu-1 Chat
│   │   │   ├── groupConversation.php # Gruppenchat
│   │   │   ├── newChat.php         # Neuen Chat starten
│   │   │   ├── createGroup.php     # Gruppe erstellen
│   │   │   └── group.php           # Gruppen-Übersicht
│   │   └── ...
│   ├── core/               # Kern-Klassen
│   └── config/             # Konfigurationsdateien
├── public/                 # Öffentlicher Webroot
│   ├── index.php          # Einstiegspunkt
│   ├── avatars/           # Benutzer-Avatare
│   └── ...
└── vendor/                # Composer-Abhängigkeiten
```

## Verwendung

### Neue Nachricht senden
1. Navigiere zu "Messages"
2. Klicke auf "+ New Chat"
3. Wähle einen Empfänger aus
4. Schreibe deine Nachricht und klicke "Send"

### Gruppe erstellen
1. Navigiere zu "Messages"
2. Klicke auf "+ New Chat"
3. Wähle "Create Group"
4. Gebe einen Gruppennamen ein
5. Wähle Mitglieder aus
6. Klicke "Create Group"

### Nachrichten lesen
* Ungelesene Nachrichten werden mit einem roten Badge angezeigt
* Klicke auf eine Konversation um sie zu öffnen
* Nachrichten werden automatisch als gelesen markiert

## Technologie-Stack

* **Backend:** PHP 5.5+ mit MVC-Architektur
* **Datenbank:** MySQL mit PDO
* **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
* **Security:**
  - Bcrypt Password Hashing
  - CSRF-Tokens
  - XSS-Protection durch htmlspecialchars()
  - Prepared Statements (SQL-Injection Schutz)
* **Dependencies:**
  - PHPMailer (Email-Versand)
  - Gregwar Captcha (Captcha-Generierung)
  - PHPUnit (Testing)

## Sicherheitsfeatures

* **Password Hashing:** Verwendet PHP's password_hash() mit bcrypt
* **CSRF-Schutz:** Token-basierter Schutz für sensible Formulare
* **SQL-Injection Schutz:** Ausschließlich Prepared Statements
* **XSS-Schutz:** Alle Ausgaben werden escaped
* **Session-Sicherheit:** Sichere Session-Konfiguration
* **Remember-Me:** Verschlüsselte Cookie-Inhalte

## Email-Konfiguration

Für professionellen Email-Versand (Registrierung, Passwort-Reset) SMTP konfigurieren:

In `application/config/config.development.php`:
```php
define('EMAIL_USE_SMTP', true);
define('EMAIL_SMTP_HOST', 'smtp.deinanbieter.de');
define('EMAIL_SMTP_USERNAME', 'deine@email.de');
define('EMAIL_SMTP_PASSWORD', 'dein_passwort');
define('EMAIL_SMTP_PORT', 587);
define('EMAIL_SMTP_ENCRYPTION', 'tls');
```

## Troubleshooting

### Nachrichten werden nicht angezeigt
* Prüfe ob die Messaging-Tabellen installiert sind
* Führe `install-messages-table.php` und `install-messenger-complete.php` aus
* Überprüfe die Datenbank-Verbindung in der Config

### Avatar-Upload funktioniert nicht
* Stelle sicher dass `public/avatars` beschreibbar ist
* Setze Rechte: `chmod 0777 public/avatars`

### Emails werden nicht versendet
* Aktiviere SMTP in der Config
* Verwende einen SMTP-Dienst (Gmail, SendGrid, etc.)
* Native PHP mail() funktioniert meist nicht (Spam-Filter)

### Login funktioniert nicht
* Überprüfe Session-Konfiguration
* Stelle sicher dass Cookies aktiviert sind
* Prüfe die Datenbank-Verbindung

## Weiterentwicklung

### Geplante Features
* Live-Benachrichtigungen für neue Nachrichten
* Datei-Upload in Chats
* Emojis und Rich-Text Formatting
* Video/Voice-Chat Integration
* Mobile App

### Mitwirken
Pull Requests sind willkommen! Bitte committe in den `develop` Branch.

## Lizenz

MIT License - Frei für private und kommerzielle Nutzung.

## Credits

Basiert auf dem HUGE Framework von [panique](https://github.com/panique/huge).
Erweitert um Messaging-Funktionalität und moderne UI-Verbesserungen.

## Support

Bei Problemen oder Fragen:
1. Überprüfe diese README
2. Schaue in die Code-Kommentare
3. Erstelle ein GitHub Issue

## Changelog

### Version 1.0 (2026)
* Vollständiges Messaging-System hinzugefügt
* Gruppenchat-Funktionalität implementiert
* Moderne Chat-UI mit Gradient-Design
* Ungelesene Nachrichten-Zähler
* Verbesserte Benutzeroberfläche
* Bugfixes und Performance-Optimierungen

---

**Viel Erfolg mit deinem Projekt!** 🚀
