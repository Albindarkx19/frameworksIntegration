<?php
// Test script to diagnose reCAPTCHA connection issues
// This is a standalone script that doesn't require the framework

?>
<!DOCTYPE html>
<html>
<head>
    <title>reCAPTCHA Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2 { color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>

<h1>reCAPTCHA Connection Test</h1>

<?php
echo "<h2>PHP cURL Test</h2>";

// Check if cURL is enabled
if (function_exists('curl_version')) {
    echo "<p class='success'>✓ cURL is enabled</p>";
    $version = curl_version();
    echo "<p>cURL version: " . $version['version'] . "</p>";
    echo "<p>SSL version: " . $version['ssl_version'] . "</p>";
} else {
    echo "<p class='error'>✗ cURL is NOT enabled</p>";
    echo "<p>You need to enable cURL in php.ini</p>";
    exit;
}

// Test connection to Google reCAPTCHA API
echo "<h2>Connection Test</h2>";

$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = array(
    'secret' => '6LfDXkksAAAAAIPbdv9lX0vCzgfEXMsmzs4JcLq8',
    'response' => 'test',
    'remoteip' => '127.0.0.1'
);

echo "<p><strong>Testing with SSL verification DISABLED:</strong></p>";

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for testing
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_TIMEOUT, 10);

$response = curl_exec($curl);
$error = curl_error($curl);
$info = curl_getinfo($curl);
curl_close($curl);

if ($error) {
    echo "<p class='error'>✗ Connection failed</p>";
    echo "<p class='error'>Error: " . htmlspecialchars($error) . "</p>";
} else {
    echo "<p class='success'>✓ Connection successful</p>";
    echo "<p>HTTP Code: " . $info['http_code'] . "</p>";
    echo "<p>Response:</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

echo "<h2>PHP Configuration</h2>";
echo "<p>allow_url_fopen: " . (ini_get('allow_url_fopen') ? '<span class="success">enabled</span>' : '<span class="error">disabled</span>') . "</p>";
echo "<p>OpenSSL: " . (extension_loaded('openssl') ? '<span class="success">enabled</span>' : '<span class="error">disabled</span>') . "</p>";
echo "<p>cURL extension: " . (extension_loaded('curl') ? '<span class="success">enabled</span>' : '<span class="error">disabled</span>') . "</p>";

?>

</body>
</html>
