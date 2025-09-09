<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class GitDeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan git:deploy "komentar commit"
     */
    protected $signature = 'git:deploy {message=update}';

    protected $description = 'Auto git add, commit, push ke production';

    public function handle()
    {
        $message = $this->argument('message');

        $commands = [
            ['git', 'add', '.'],
            ['git', 'commit', '-m', $message],
            ['git', 'push', 'origin', 'HEAD:production'],
        ];

        foreach ($commands as $cmd) {
            $process = new Process($cmd, base_path()); // base_path() = root project laravel
            $process->run(function ($type, $buffer) {
                echo $buffer;
            });

            if (!$process->isSuccessful()) {
                $this->error("Error: " . $process->getErrorOutput());
                return Command::FAILURE;
            }
        }

        $this->info("✅ Git deploy berhasil dengan message: {$message}");
        
        return Command::SUCCESS;
    }
}