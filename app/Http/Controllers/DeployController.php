<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeployController extends Controller
{
    public function webhook(Request $request)
    {
        // Only deploy on push to main branch
        $ref = $request->input('ref', '');
        if ($ref !== 'refs/heads/main') {
            return response()->json([
                'message' => 'Ignored: not the main branch',
                'ref' => $ref,
            ]);
        }

        $repoPath = '/home/ibnuhafi/repositories/api.yazidtest.my.id';
        $deployPath = '/home/ibnuhafi/api.yazidtest.my.id';
        $timestamp = now()->toDateTimeString();
        $output = [];
        $output[] = "=== Deploy started at {$timestamp} ===";

        // Commands to run
        $commands = [
            // Pull latest code in repo directory
            "cd {$repoPath} && git pull origin main 2>&1",
            // Copy files to deploy directory
            "/bin/cp -R {$repoPath}/. {$deployPath}/ 2>&1",
            // Run commands in deploy directory
            "cd {$deployPath} && /usr/local/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction 2>&1",
            "cd {$deployPath} && /usr/local/bin/php artisan migrate --force 2>&1",
            "cd {$deployPath} && /usr/local/bin/php artisan config:cache 2>&1",
            "cd {$deployPath} && /usr/local/bin/php artisan route:cache 2>&1",
            "cd {$deployPath} && /usr/local/bin/php artisan view:cache 2>&1",
        ];

        foreach ($commands as $cmd) {
            $result = shell_exec($cmd);
            $output[] = "$ {$cmd}";
            $output[] = $result;
        }

        $output[] = "=== Deploy completed at " . now()->toDateTimeString() . " ===\n";

        // Write to log
        $logContent = implode("\n", $output);
        file_put_contents(storage_path('logs/deploy.log'), $logContent, FILE_APPEND);

        return response()->json([
            'status' => 'success',
            'message' => 'Deployment completed',
            'timestamp' => $timestamp,
        ]);
    }
}
