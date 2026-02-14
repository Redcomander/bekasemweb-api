<?php
/**
 * BEKASEMWEB - GitHub Actions Deployment Webhook
 * ================================================
 * 
 * This script receives a POST request from GitHub Actions
 * and triggers deployment on the cPanel server.
 * 
 * Security: Validates X-Deploy-Secret header against
 * the DEPLOY_WEBHOOK_SECRET in .env
 */

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Load the .env file to get the webhook secret
$envFile = __DIR__ . '/../.env';
$secret = null;

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0)
            continue;
        if (strpos($line, 'DEPLOY_WEBHOOK_SECRET=') === 0) {
            $secret = trim(substr($line, strlen('DEPLOY_WEBHOOK_SECRET=')));
            break;
        }
    }
}

// Verify the secret
$providedSecret = $_SERVER['HTTP_X_DEPLOY_SECRET'] ?? '';

if (empty($secret) || $providedSecret !== $secret) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Set deployment directory
$deployDir = '/home/ibnuhafi/api.yazidtest.my.id';

// Log file
$logFile = $deployDir . '/storage/logs/deploy.log';

// Build the deployment commands
$commands = [
    "cd {$deployDir}",
    'git pull origin main 2>&1',
    'composer install --no-dev --optimize-autoloader --no-interaction 2>&1',
    'php artisan migrate --force 2>&1',
    'php artisan config:cache 2>&1',
    'php artisan route:cache 2>&1',
    'php artisan view:cache 2>&1',
    'php artisan storage:link 2>&1',
    'chmod -R 755 storage bootstrap/cache 2>&1',
    'chmod -R 775 storage/logs 2>&1',
];

$fullCommand = implode(' && ', $commands);

// Execute deployment
$timestamp = date('Y-m-d H:i:s');
$output = [];
$returnCode = 0;

exec($fullCommand, $output, $returnCode);

$outputStr = implode("\n", $output);

// Log the deployment
$logEntry = "
========================================
Deployment: {$timestamp}
Status: " . ($returnCode === 0 ? 'SUCCESS' : 'FAILED') . "
Return Code: {$returnCode}
Output:
{$outputStr}
========================================
";

file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// Return response
if ($returnCode === 0) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Deployment completed successfully',
        'timestamp' => $timestamp,
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'failed',
        'message' => 'Deployment failed',
        'output' => $outputStr,
        'timestamp' => $timestamp,
    ]);
}
