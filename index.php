<?php
// Root router - redirects requests to the public directory

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Routes that should redirect to public/
$routes = [
    '/verify_email.php' => '/public/verify_email.php',
    '/login.html' => '/public/login.html',
    '/register.php' => '/public/register.php',
    '/dashboard.php' => '/public/dashboard.php',
    '/view_email_log.php' => '/public/view_email_log.php',
    '/resend_verification.php' => '/public/resend_verification.php',
    '/login.php' => '/public/login.php',
    '/logout.php' => '/public/logout.php',
];

// Check if route exists and redirect with query string preserved
if (isset($routes[$path])) {
    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    $redirect = $routes[$path] . ($queryString ? '?' . $queryString : '');
    header("Location: $redirect");
    exit;
}

// Default: redirect to public/index.html
if ($path === '/' || $path === '') {
    header("Location: /public/index.html");
    exit;
}

// Let the built-in server handle other requests (assets, etc.)
?>