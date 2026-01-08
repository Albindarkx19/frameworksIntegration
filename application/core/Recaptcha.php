<?php

/**
 * Recaptcha
 * Helper class for Google reCAPTCHA v2 integration
 */
class Recaptcha
{
    /**
     * Verify the reCAPTCHA response
     * @param string $response The response token from the reCAPTCHA widget
     * @return bool True if verification successful, false otherwise
     */
    public static function verify($response)
    {
        // Check if reCAPTCHA is enabled
        if (!Config::get('RECAPTCHA_ENABLED')) {
            return true; // Skip verification if disabled
        }

        // Check if response token exists
        if (empty($response)) {
            Session::add('feedback_negative', 'Bitte bestätige, dass du kein Roboter bist.');
            return false;
        }

        // Prepare verification request
        $secret_key = Config::get('RECAPTCHA_SECRET_KEY');
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';

        // Build POST request
        $data = array(
            'secret' => $secret_key,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        );

        // Initialize cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $verify_url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        // Execute request
        $response_json = curl_exec($curl);
        $curl_error = curl_error($curl);
        curl_close($curl);

        // Check for cURL errors
        if ($curl_error) {
            Session::add('feedback_negative', 'reCAPTCHA-Verbindungsfehler. Bitte versuche es erneut.');
            return false;
        }

        // Decode JSON response
        $response_data = json_decode($response_json, true);

        // Verify the response
        if (isset($response_data['success']) && $response_data['success'] === true) {
            return true;
        } else {
            Session::add('feedback_negative', 'reCAPTCHA-Verifizierung fehlgeschlagen. Bitte versuche es erneut.');
            return false;
        }
    }

    /**
     * Get the reCAPTCHA site key from config
     * @return string|null The site key or null if not configured
     */
    public static function getSiteKey()
    {
        return Config::get('RECAPTCHA_SITE_KEY');
    }

    /**
     * Check if reCAPTCHA is enabled
     * @return bool True if enabled, false otherwise
     */
    public static function isEnabled()
    {
        return Config::get('RECAPTCHA_ENABLED') === true;
    }

    /**
     * Render the reCAPTCHA widget HTML
     * @return string HTML code for the reCAPTCHA widget
     */
    public static function render()
    {
        if (!self::isEnabled()) {
            return '';
        }

        $site_key = self::getSiteKey();
        return '<div class="g-recaptcha" data-sitekey="' . htmlspecialchars($site_key) . '"></div>';
    }

    /**
     * Get the reCAPTCHA script URL
     * @return string The Google reCAPTCHA API script URL
     */
    public static function getScriptUrl()
    {
        return 'https://www.google.com/recaptcha/api.js';
    }
}
