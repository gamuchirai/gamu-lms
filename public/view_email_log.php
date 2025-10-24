<?php
// Simple page to view email log for local testing
// Since mail() may not work locally, this shows all verification tokens

$logFile = __DIR__ . '/../logs/email_log.txt';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Log - Dzidza LMS (Dev Tool)</title>
    <link rel="stylesheet" href="/assets/css/login.css?v=4.2">
    <style>
        .log-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .log-content {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            max-height: 600px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        h1 {
            color: #5D215F;
            margin-bottom: 1rem;
            text-align: center;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            color: #856404;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #5D215F;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="log-container">
        <h1>📧 Email Log (Development Tool)</h1>
        <div class="warning">
            <strong>⚠️ Development Only:</strong> This page shows verification emails that were logged because mail() is not configured. In production, real emails would be sent.
        </div>
        
        <div class="log-content">
<?php
if (file_exists($logFile)) {
    echo htmlspecialchars(file_get_contents($logFile));
} else {
    echo "No email log found. Emails will appear here after registration.";
}
?>
        </div>
        
        <a href="/login.html" class="back-link">← Back to Login</a>
    </div>
</body>
</html>
