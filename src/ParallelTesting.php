<?php

namespace Src;

use Src\TestsRunner;
use Src\CmdMessages;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Class ParallelTesting
 */
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

        if (!function_exists('pcntl_async_signals') || !function_exists('pcntl_signal')) {
            throw new \Exception('PCNTL functions are not available. Ensure PHP is compiled with --enable-pcntl');
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

        CmdMessages::printMessage("Running tests in parallel with a maximum of {$this->maxProcesses} processes..");
     }

     /**
     * Run the tests in parallel.
     *
     * @param array $testFiles List of test files to run
     */
    public function run(array $testFiles): void
    {
        foreach ($testFiles as $file) {
            // Wait until the count of running processes is less than the maximum
            while (count($this->processes) >= $this->maxProcesses) {
                $this->checkProcesses();
                usleep(100000); // Wait a bit before the next check to reduce overhead
            }

            CmdMessages::printMessage("Running parallel test: {$file}");

            $process = new Process(['php', 'run-tests.php', '--parallel', $file]);
            $process->start();

            $this->processes[] = $process; // Store the process for later reference
        }

        // Ensure all processes complete
        while (!empty($this->processes)) {
            $this->checkProcesses();
            usleep(100000); // Wait a bit before the next check to reduce overhead
        }
    }

    /**
     * Check the status of processes and remove completed ones.
     */
    private function checkProcesses(): void
    {
        foreach ($this->processes as $key => $process) {
            if ($process->isRunning()) {
                continue;
            }

            if ($process->getExitCode() !== 0) {
                throw new ProcessFailedException($process);
            }
            unset($this->processes[$key]);

            CmdMessages::printMessage("Parallel test completed: {$process->getCommandLine()}");
        }
    }
}