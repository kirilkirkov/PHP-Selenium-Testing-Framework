<?php

namespace Src;

use Src\ResultsContainer;

/**
 * Class HtmlReport
 */
class HtmlReport
{
    private const REPORT_FILE = __DIR__ . '/../results/report.html';

    /**
     * Generate HTML report
     */
    public function generate(): void
    {
        $results = ResultsContainer::getResults();
        ob_start();
    ?>
        <!doctype html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
            <body>
                <h1 class="text-3xl font-bold underline text-center mb-20">
                    Tests Report!
                </h1>

                <div class="container mx-auto">
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                            <tbody>
                                <?php 
                                    if(in_array(RESULT_TYPE, ['json', 'xml'])) {
                                        foreach ($results as $result) {
                                ?>
                                        <tr class="bg-white border-b">
                                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                <?php echo $result['status']; ?>
                                            </th>
                                            <td class="px-6 py-4">
                                                <?php echo $result['error_type']; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php echo $result['error_message'] ?? ''; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php echo $result['test_class']; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php echo $result['test_method']; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php echo $result['trace'] ?? ''; ?>
                                            </td>
                                        </tr>
                                <?php } 
                                } else {
                                    foreach ($results as $result) {
                                ?>
                                        <tr class="bg-white border-b">
                                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                <?php echo $result; ?>
                                            </th>
                                        </tr>
                                <?php  } 
                                   }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </body>
        </html>
    <?php
        $htmlContent = ob_get_clean();
        file_put_contents(self::REPORT_FILE, $htmlContent);
    }

    /**
     * Get HTML report as string
     */
    public function getReport(): string
    {
        return file_get_contents(self::REPORT_FILE);
    }
}