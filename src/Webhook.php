<?php

namespace Src;

use Src\ResultsContainer;

/**
 * Class Webhook
 */
class Webhook
{
    public function send()
    {
        if (!filter_var(WEBHOOK_URL, FILTER_VALIDATE_URL)) {
            return;
        }

        if (!function_exists('curl_init')) {
            echo "\033[0;31m !! cURL is not installed. To receive webhooks, install cURL for PHP. !! \033[0m" . PHP_EOL;
            return;
        }

        $results = ResultsContainer::getResults();

        if(WEBHOOK_FORMAT === 'html') {
            $contentType = 'Content-Type: text/html';
            $htmlReport = new HtmlReport();
            $htmlReport->generate();
            $data = base64_encode($htmlReport->getReport());
        } else {
            $contentType = 'Content-Type: application/json';
            $data = json_encode($results);
        }

        $ch = curl_init(WEBHOOK_URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            $contentType,
            'Content-Length: ' . strlen($data)
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            echo "\033[0;31m !! Error sending results to webhook !! \033[0m" . PHP_EOL;
        }
    }
}