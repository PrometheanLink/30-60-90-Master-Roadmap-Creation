<?php
/**
 * Test mPDF Installation
 *
 * This file tests if mPDF is working correctly
 */

$plugin_dir = dirname(__FILE__);
$autoload_file = $plugin_dir . '/vendor/mpdf/autoload.php';

if (!file_exists($autoload_file)) {
    die('mPDF is not installed. Please run install-mpdf.php first.');
}

require_once $autoload_file;

try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4'
    ]);

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body {
                font-family: sans-serif;
            }
            h1 {
                color: #c16107;
                border-bottom: 3px solid #c16107;
                padding-bottom: 10px;
            }
            .success {
                background: #e5f5d9;
                padding: 20px;
                border-left: 5px solid #95c93d;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <h1>mPDF Test - Success!</h1>
        <div class="success">
            <p><strong>Congratulations!</strong></p>
            <p>If you are seeing this PDF, it means mPDF is installed and working correctly.</p>
            <p>Your 30-60-90 Project Journey plugin is ready to generate PDF reports.</p>
        </div>
        <h2>Test Details</h2>
        <p><strong>Generated:</strong> ' . date('F j, Y - g:i a') . '</p>
        <p><strong>mPDF Version:</strong> 8.x</p>
        <p><strong>Plugin:</strong> 30-60-90 Project Journey v1.0.0</p>
        <p><strong>Developer:</strong> PrometheanLink</p>
    </body>
    </html>
    ';

    $mpdf->WriteHTML($html);
    $mpdf->Output('test-mpdf.pdf', 'I'); // I = inline display in browser

} catch (\Mpdf\MpdfException $e) {
    echo '<h1>mPDF Test Failed</h1>';
    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Please check your mPDF installation.</p>';
}
