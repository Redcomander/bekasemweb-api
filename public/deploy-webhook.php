<?php
/**
 * BEKASEMWEB - GitHub Actions Deployment Webhook
 * ================================================
 */

// Show all errors in response for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON response header
header('Content-Type: application/json');

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
    echo json_encode(['error' => 'Unauthorized', 'detail' => empty($secret) ? 'No secret configured on server' : 'Secret mismatch']);
    exit;
}

// Set deployment directory
$deployDir = '/home/ibnuhafi/api.yazidtest.my.id';

// Check which execution functions are available
$availableFunctions = [];
foreach (['exec', 'shell_exec', 'system', 'passthru', 'proc_open'] as $func) {
    if (function_exists($func) && !in_array($func, array_map('trim', explode(',', ini_get('disable_functions'))))) {
        $availableFunctions[] = $func;
    }
}

if (empty($availableFunctions)) {
    http_response_code(500);
    echo json_encode([
        'error' => 'No execution functions available',
        'detail' => 'exec, shell_exec, system, passthru, proc_open are all disabled',
        'disabled_functions' => ini_get('disable_functions'),
    ]);
    exit;
}

// Build the deployment script
$script = "export HOME=/home/ibnuhafi && " .
    "cd {$deployDir} && " .
    "git pull origin main 2>&1 && " .
    "/usr/local/bin/php /home/ibnuhafi/composer.phar install --no-dev --optimize-autoloader --no-interaction 2>&1 && " .
    "/usr/local/bin/php artisan migrate --force 2>&1 && " .
    "/usr/local/bin/php artisan config:cache 2>&1 && " .
    "/usr/local/bin/php artisan route:cache 2>&1 && " .
    "/usr/local/bin/php artisan view:cache 2>&1 && " .
    "/usr/local/bin/php artisan storage:link 2>&1 && " .
    "chmod -R 755 storage bootstrap/cache 2>&1 && " .
    "chmod -R 775 storage/logs 2>&1";

$timestamp = date('Y-m-d H:i:s');
$output = '';
$returnCode = -1;

// Try execution methods in order of preference
if (in_array('proc_open', $availableFunctions)) {
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($script, $descriptors, $pipes, $deployDir, null);
    if (is_resource($process)) {
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $returnCode = proc_close($process);
        if ($errors)
            $output .= "\nSTDERR: " . $errors;
    }
} elseif (in_array('exec', $availableFunctions)) {
    $outputLines = [];
    exec($script, $outputLines, $returnCode);
    $output = implode("\n", $outputLines);
} elseif (in_array('shell_exec', $availableFunctions)) {
    $output = shell_exec($script . '; echo "EXIT_CODE:$?"');
    if (preg_match('/EXIT_CODE:(\d+)$/', $output, $matches)) {
        $returnCode = (int) $matches[1];
        $output = preg_replace('/EXIT_CODE:\d+$/', '', $output);
    }
}

// Log the deployment
$logFile = $deployDir . '/storage/logs/deploy.log';
$logEntry = "[{$timestamp}] Status: " . ($returnCode === 0 ? 'SUCCESS' : "FAILED (code: {$returnCode})") .
    "\nMethod: " . $availableFunctions[0] .
    "\nOutput: {$output}\n" .
    str_repeat('-', 50) . "\n";
@file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// Return response
$response = [
    'status' => $returnCode === 0 ? 'success' : 'failed',
    'message' => $returnCode === 0 ? 'Deployment completed successfully' : 'Deployment failed',
    'return_code' => $returnCode,
    'exec_method' => $availableFunctions[0],
    'output' => $output,
    'timestamp' => $timestamp,
];

http_response_code($returnCode === 0 ? 200 : 500);
echo json_encode($response);
