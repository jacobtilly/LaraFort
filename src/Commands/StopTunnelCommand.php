<?php

namespace JacobTilly\LaraFort\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class StopTunnelCommand extends Command
{
    protected $signature = 'larafort:stoptunnel';
    protected $description = 'Forcefully stop the Expose tunnel';

    public function handle()
    {
        $this->info('Attempting to stop the Expose tunnel...');

        // Find the process ID of the Expose tunnel
        $process = Process::fromShellCommandline('pgrep -f "herd share"');
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('No Expose tunnel process found.');
            return 1;
        }

        $pid = trim($process->getOutput());

        // Forcefully stop the process
        $stopProcess = Process::fromShellCommandline("kill -9 $pid");
        $stopProcess->run();

        if ($stopProcess->isSuccessful()) {
            $this->info('Expose tunnel stopped successfully.');
            return 0;
        }

        $this->error('Failed to stop the Expose tunnel.');
        return 1;
    }
}
