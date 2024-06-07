<?php

namespace Src;

use Src\TestsRunner;
use Symfony\Component\Process\Process;

class ParallelTesting
{
    private int $maxProcesses;
    private array $processes = [];

    public function __construct()
    {
        $this->maxProcesses = (int)MAX_PARALLEL_PROCESSES;
        if ($this->maxProcesses < 1) {
            throw new \Exception('Max parallel processes must be greater than 0');
        }

        // Handle Ctrl+C or process termination
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, function () {
            echo "Exiting..." . PHP_EOL;
            foreach ($this->processes as $process) {
                $process->stop();
            }
            exit;
        });
     }

    public function run(array $testFiles): void
     {
        foreach ($testFiles as $file) {
            $process = new Process(['php', 'run-tests.php', '--parallel', $file]);
            $process->start();
            $this->processes[] = $process; // Store the process for later management
        }

        // Make a while loop and show the output of each process until all are finished
        while (!empty($this->processes)) {
            foreach ($this->processes as $key => $process) {
                if (!$process->isRunning()) {
                    echo $process->getOutput();
                    unset($this->processes[$key]);
                }
            }
        }
     }
}