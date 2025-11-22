<?php
/**
 * mPDF Installation Helper
 *
 * Run this file in your browser to check if mPDF is installed correctly
 * or to download and install it automatically.
 *
 * URL: http://yoursite.com/wp-content/plugins/30-60-90-project-journey/install-mpdf.php
 */

// Security check
$plugin_dir = dirname(__FILE__);
$vendor_dir = $plugin_dir . '/vendor';
$mpdf_dir = $vendor_dir . '/mpdf';
$autoload_file = $mpdf_dir . '/autoload.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>mPDF Installation Helper</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #c16107;
            border-bottom: 3px solid #c16107;
            padding-bottom: 10px;
        }
        .status {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .success {
            background: #e5f5d9;
            border-left: 5px solid #95c93d;
            color: #5a8a2d;
        }
        .error {
            background: #ffe5d9;
            border-left: 5px solid #c16107;
            color: #c16107;
        }
        .info {
            background: #d9e8ff;
            border-left: 5px solid #4a90e2;
            color: #4a90e2;
        }
        .step {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-left: 3px solid #ccc;
        }
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #c16107;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .button:hover {
            background: #a04f06;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>mPDF Installation Helper</h1>
        <p>This helper will guide you through installing mPDF for the 30-60-90 Project Journey plugin.</p>

        <?php
        // Check if mPDF is installed
        if (file_exists($autoload_file)) {
            ?>
            <div class="status success">
                <strong>✓ mPDF is installed correctly!</strong>
                <p>The mPDF library was found at: <code><?php echo htmlspecialchars($autoload_file); ?></code></p>
                <p>Your plugin is ready to generate PDF reports.</p>
            </div>

            <h2>Test mPDF</h2>
            <p>
                <a href="test-mpdf.php" class="button">Test PDF Generation</a>
            </p>
            <?php
        } else {
            ?>
            <div class="status error">
                <strong>✗ mPDF is not installed</strong>
                <p>The mPDF library was not found. Please follow the installation instructions below.</p>
            </div>

            <h2>Installation Options</h2>

            <div class="step">
                <h3>Option 1: Install via Composer (Recommended)</h3>
                <p>If you have Composer installed on your server:</p>
                <pre>cd <?php echo htmlspecialchars($plugin_dir); ?>
composer install</pre>
                <p>This will automatically download and install mPDF and all dependencies.</p>
            </div>

            <div class="step">
                <h3>Option 2: Manual Download</h3>
                <ol>
                    <li>Download mPDF from: <a href="https://github.com/mpdf/mpdf/releases/latest" target="_blank">GitHub Releases</a></li>
                    <li>Extract the downloaded ZIP file</li>
                    <li>Upload the <code>mpdf</code> folder to:<br>
                        <code><?php echo htmlspecialchars($vendor_dir); ?>/mpdf/</code>
                    </li>
                    <li>Ensure this file exists:<br>
                        <code><?php echo htmlspecialchars($autoload_file); ?></code>
                    </li>
                    <li>Refresh this page to verify installation</li>
                </ol>
            </div>

            <div class="step">
                <h3>Option 3: Using WP-CLI</h3>
                <p>If you have WP-CLI and Composer installed:</p>
                <pre>wp plugin path 30-60-90-project-journey | xargs -I {} sh -c 'cd {} && composer install'</pre>
            </div>

            <h2>Directory Structure</h2>
            <div class="info">
                <p>After installation, your directory structure should look like this:</p>
                <pre>30-60-90-project-journey/
├── vendor/
│   ├── mpdf/
│   │   ├── autoload.php
│   │   └── ... (other mPDF files)
│   └── composer/
└── ... (other plugin files)</pre>
            </div>

            <h2>Need Help?</h2>
            <p>If you're having trouble installing mPDF, please contact:</p>
            <ul>
                <li>Email: <a href="mailto:support@prometheanlink.com">support@prometheanlink.com</a></li>
                <li>Documentation: <a href="https://mpdf.github.io/" target="_blank">mPDF Documentation</a></li>
            </ul>
            <?php
        }
        ?>

        <h2>System Information</h2>
        <div class="info">
            <table style="width: 100%;">
                <tr>
                    <td><strong>PHP Version:</strong></td>
                    <td><?php echo phpversion(); ?></td>
                </tr>
                <tr>
                    <td><strong>Plugin Directory:</strong></td>
                    <td><code><?php echo htmlspecialchars($plugin_dir); ?></code></td>
                </tr>
                <tr>
                    <td><strong>Vendor Directory:</strong></td>
                    <td><code><?php echo htmlspecialchars($vendor_dir); ?></code></td>
                </tr>
                <tr>
                    <td><strong>Vendor Directory Exists:</strong></td>
                    <td><?php echo is_dir($vendor_dir) ? '✓ Yes' : '✗ No'; ?></td>
                </tr>
                <tr>
                    <td><strong>Vendor Directory Writable:</strong></td>
                    <td><?php echo is_writable($vendor_dir) ? '✓ Yes' : '✗ No'; ?></td>
                </tr>
            </table>
        </div>

        <p style="margin-top: 30px; text-align: center; color: #666;">
            <small>30-60-90 Project Journey Plugin v1.0.0 | PrometheanLink</small>
        </p>
    </div>
</body>
</html>
