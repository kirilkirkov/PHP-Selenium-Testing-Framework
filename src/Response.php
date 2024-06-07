<?php

namespace Src;

use Src\ResultsContainer;

class Response
{
    private const RESULTS_FILE_NAME = 'test-results';
    private const RESULTS_DIRECTORY = __DIR__ . '/../results/';

    private int $startTime;

    public function checkResultsDirectory(): void
    {
        $this->createResultsDirectory();
        $this->clearResultsDirectory();
    }

    public function setStartTime(int $startTime): Response
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function handle()
    {
        switch (RESULT_TYPE) {
            case 'json':
                $this->asJson();
                break;
            case 'xml':
                $this->asXml();
                break;
            default:
                $this->printInTerminal();
        }
    }
    
    /**
     * Print results in terminal
     */
    public function printInTerminal(): void
    {
        echo PHP_EOL;
        foreach (ResultsContainer::getResults() as $result) {
            echo '====================' . PHP_EOL;
            echo $result . PHP_EOL;
        }
        echo '====================' . PHP_EOL;
        echo PHP_EOL;
        echo "\033[0;34m Time taken: " . (time() - $this->startTime) . " seconds \033[0m" . PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * Save results as JSON file
     */
    public function asJson(): void
    {
        $fileName = self::RESULTS_DIRECTORY . self::RESULTS_FILE_NAME . '.json';

        $counter = 0;
        $newFileName = $fileName;
        while (file_exists($newFileName)) {
            $counter++;
            $newFileName = self::RESULTS_DIRECTORY . self::RESULTS_FILE_NAME . '-' . $counter . '.json';
        }

        file_put_contents(
            $newFileName,
            json_encode(ResultsContainer::getResults(), JSON_PRETTY_PRINT)
        );
    }

    /**
     * Save results as XML file
     */
    public function asXml(): void
    {
        $xml = new \SimpleXMLElement('<results/>');
        foreach (ResultsContainer::getResults() as $result) {
            $test = $xml->addChild('test');
            $test->addChild('status', $result['status']);
            $test->addChild('test_class', $result['test_class']);
            $test->addChild('test_method', $result['test_method']);
            if (isset($result['error_type'])) {
                $test->addChild('error_type', $result['error_type']);
            }
            if (isset($result['error_message'])) {
                $test->addChild('error_message', $result['error_message']);
            }
            if (isset($result['trace'])) {
                $test->addChild('trace', $result['trace']);
            }
        }
        
        $fileName = self::RESULTS_DIRECTORY . self::RESULTS_FILE_NAME . '.xml';

        $counter = 0;
        $newFileName = $fileName;
        while (file_exists($newFileName)) {
            $counter++;
            $newFileName = self::RESULTS_DIRECTORY . self::RESULTS_FILE_NAME . '-' . $counter . '.xml';

        }

        $xml->asXML($newFileName); 
    }

    private function createResultsDirectory(): void
    {
        if (!is_dir(__DIR__ . '/../results')) {
            mkdir(__DIR__ . '/../results');
        }

        if (!is_writable(__DIR__ . '/../results')) {
            throw new \Exception('Results directory is not writable');
        }
    }

    private function clearResultsDirectory(): void
    {
        $files = glob(self::RESULTS_DIRECTORY . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}