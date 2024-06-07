<?php

namespace Src;

/**
 * Class ResultsContainer
 * 
 * Stores results from test runs in tmp file, then combines them into a single file
 * (mostly used for parallel testing)
 */
class ResultsContainer
{
    private const RESULTS_TMP_FILE_NAME = 'results.tmp';
    public const RESULTS_FILE_NAME = 'test-results';
    public const RESULTS_DIRECTORY = __DIR__ . '/../results/';

    public static function initialize(): void
    {
        self::createResultsDirectory();
        self::clearResultsDirectory();
        self::createTmpFile();
    }

    /**
     * Create results directory
     */
    private static function createResultsDirectory(): void
    {
        if (!is_dir(__DIR__ . '/../results')) {
            mkdir(__DIR__ . '/../results');

            if (!is_dir(__DIR__ . '/../results')) {
                throw new \Exception('Results directory could not be created.');
            }
        }

        if (!is_writable(__DIR__ . '/../results')) {
            throw new \Exception('Results directory is not writable.');
        }
    }

    /**
     * Clear results directory
     */
    private static function clearResultsDirectory(): void
    {
        $files = glob(self::RESULTS_DIRECTORY . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private static function getTmpFileName(): string
    {
        return self::RESULTS_DIRECTORY . self::RESULTS_TMP_FILE_NAME;
    }

    /**
     * Create tmp file to store results
     */
    private static function createTmpFile(): void
    {
        $oldFile = self::getTmpFileName();
        if (file_exists($oldFile)) {
            unlink($oldFile);

            if (file_exists($oldFile)) {
                throw new \Exception('Old results.tmp file cannot be removed.');
            }
        }

        $tempFile = self::getTmpFileName();
        if (!file_exists($tempFile)) {
            file_put_contents($tempFile, '');
        }

        if (!file_exists($tempFile)) {
            throw new \Exception('Results tmp file cannot be created.');
        }

        if (!is_writable($tempFile)) {
            throw new \Exception('Results tmp file is not writable.');
        }
    }

    public static function setFailed(
        string $exceptionType,
        string $exceptionMessage,
        string $className,
        string $method,
        array $trace
    ) {
        $traceString = '';
        if (SHOW_FAILED_TRACE && is_array($trace) && isset($trace[0])) {
            $traceString = PHP_EOL . ' Trace: ' . PHP_EOL;
            foreach ($trace as $item) {
                $traceString .= 'File: ' . $item['file'] . ' Line: ' . $item['line'] . PHP_EOL;
            }
        }

        switch(RESULT_TYPE) {
            case 'json':
            case 'xml':
                self::saveResult([
                    'status' => 'failed',
                    'error_type' => $exceptionType,
                    'error_message' => $exceptionMessage,
                    'test_class' => $className,
                    'test_method' => $method,
                    'trace' => $traceString
                ]);
                break;
            default:
                self::saveResult("\033[0;31m Test failed." . PHP_EOL . " Error type: $exceptionType " . PHP_EOL . " Error message: " . $exceptionMessage . PHP_EOL . " Test Class: " . $className . PHP_EOL . " Test Method: " . $method . " $traceString \033[0m");
        }
    }

    public static function setSuccess(string $className, string $method)
    {
        switch(RESULT_TYPE) {
            case 'json':
            case 'xml':
                self::saveResult([
                    'status' => 'passed',
                    'test_class' => $className,
                    'test_method' => $method
                ]);
                break;
            default:
                self::saveResult("\033[0;32m Test passed. " . $className . "::" . $method . "\033[0m");
        }
    }

    public static function getResults(): array
    {
        $results = [];
        $tempFileName = self::getTmpFileName();
        $fileContents = file_get_contents($tempFileName);
    
        $lines = explode(PHP_EOL, $fileContents);
        foreach ($lines as $line) {
            if (!empty($line)) {
                if (RESULT_TYPE === 'json' || RESULT_TYPE === 'xml') {
                    $results[] = json_decode($line, true);
                } else {
                    $results[] = base64_decode($line);
                }
            }
        }
    
        return $results;
    }

    /**
     * Save results to tmp file
     */
    private static function saveResult($data)
    {
        if (RESULT_TYPE === 'json' || RESULT_TYPE === 'xml') {
            file_put_contents(self::getTmpFileName(), json_encode($data) . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents(self::getTmpFileName(), base64_encode($data) . PHP_EOL, FILE_APPEND);
        }
    }
}