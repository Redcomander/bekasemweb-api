<?php

/**
 * GitHub Webhook Auto-Deploy Script
 * 
 * This script receives GitHub push events and triggers
 * git pull + deployment commands on the server.
 * 
 * URL: https://api.yazidtest.my.id/webhook-deploy.php
 */

// ============================================
// CONFIGURATION - UPDATE THIS!
// ============================================
$secret = getenv('DEPLOY_WEBHOOK_SECRET') ?: 'YOUR_WEBHOOK_SECRET_HERE';
$repo_path = '/home/ibnuhafi/api.yazidtest.my.id';
$branch = 'main';
$log_file = $repo_path . '/storage/logs/deploy.log';

// ============================================
// VERIFY REQUEST
// ============================================

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get payload
$payload = file_get_contents('php://input');

// Verify GitHub signature
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if ($secret !== 'YOUR_WEBHOOK_SECRET_HERE') {
    $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);
    if (!hash_equals($expected, $signature)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid signature']);
        exit;
    }
}

// Parse payload
$data = json_decode($payload, true);

// Only deploy on push to main branch
$ref = $data['ref'] ?? '';
if ($ref !== 'refs/heads/' . $branch) {
    echo json_encode(['message' => 'Ignored: not the main branch', 'ref' => $ref]);
    exit;
}

// ============================================
// DEPLOY
// ============================================

$timestamp = date('Y-m-d H:i:s');
$output = [];
$output[] = "=== Deploy started at {$timestamp} ===";

// Commands to run
$commands = [
    "cd {$repo_path} && git pull origin {$branch} 2>&1",
    "cd {$repo_path} && /usr/local/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction 2>&1",
    "cd {$repo_path} && /usr/local/bin/php artisan migrate --force 2>&1",
    "cd {$repo_path} && /usr/local/bin/php artisan config:cache 2>&1",
    "cd {$repo_path} && /usr/local/bin/php artisan route:cache 2>&1",
    "cd {$repo_path} && /usr/local/bin/php artisan view:cache 2>&1",
];

foreach ($commands as $cmd) {
    $result = shell_exec($cmd);
    $output[] = "$ {$cmd}";
    $output[] = $result;
}

$output[] = "=== Deploy completed at " . date('Y-m-d H:i:s') . " ===\n";

// Write to log
$log_content = implode("\n", $output);
file_put_contents($log_file, $log_content, FILE_APPEND);

// Respond
http_response_code(200);
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'Deployment completed',
    'timestamp' => $timestamp,
]);
