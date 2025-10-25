<?php
/**
 * site_config.php
 *
 * Helper that loads a simple .env file (project root) and exposes
 * APP_ENV and BASE_URL (SITE_DOMAIN) constants for use in PHP pages.
 *
 * Usage: include_once __DIR__ . '/site_config.php'; then use BASE_URL or APP_ENV
 */

// Parse a simple .env file into an associative array
function parse_dotenv(string $path): array {
    if (!is_readable($path)) {
        return [];
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $data = [];

    foreach ($lines as $line) {
        $line = trim($line);
        // skip comments and invalid lines
        if ($line === '' || $line[0] === '#') {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        list($key, $val) = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val);

        // remove surrounding quotes if present
        if ((strpos($val, '"') === 0 && strrpos($val, '"') === strlen($val)-1) ||
            (strpos($val, "'") === 0 && strrpos($val, "'") === strlen($val)-1)) {
            $val = substr($val, 1, -1);
        }

        $data[$key] = $val;
    }

    return $data;
}

$envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
$env = parse_dotenv($envPath);

// Helper to read from parsed env or getenv or fallback default
function env_get(string $key, $default = null) {
    global $env;
    if (isset($env[$key]) && $env[$key] !== '') return $env[$key];
    $val = getenv($key);
    return $val !== false ? $val : $default;
}

// APP_ENV: production | development
define('APP_ENV', env_get('APP_ENV', 'development'));

// Domain definitions (defaults provided)
$liveDomain = rtrim(env_get('LIVE_DOMAIN', 'https://gamuchiraikundhlande.eagletechafrica.com/'), '/') . '/';
$localDomain = rtrim(env_get('LOCAL_DOMAIN', 'http://localhost:8000/'), '/') . '/';

// If ACTIVE_DOMAIN provided, use it (must be a full URL)
$activeOverride = env_get('ACTIVE_DOMAIN', '');

if (!empty($activeOverride)) {
    $base = rtrim($activeOverride, '/') . '/';
} else {
    $base = (strtolower(APP_ENV) === 'production') ? $liveDomain : $localDomain;
}

// Expose constants for application usage
if (!defined('BASE_URL')) {
    define('BASE_URL', $base);
}

if (!defined('SITE_DOMAIN')) {
    // Same as BASE_URL but kept for backwards compatibility if used elsewhere
    define('SITE_DOMAIN', BASE_URL);
}

// Optionally expose the raw values too
if (!defined('LIVE_DOMAIN')) define('LIVE_DOMAIN', $liveDomain);
if (!defined('LOCAL_DOMAIN')) define('LOCAL_DOMAIN', $localDomain);

// For debugging you can uncomment the lines below (do NOT enable on production)
// error_log('APP_ENV=' . APP_ENV);
// error_log('BASE_URL=' . BASE_URL);

?>
