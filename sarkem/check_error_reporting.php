<?php
// Skrip untuk memeriksa konfigurasi error reporting PHP saat ini

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PHP Error Reporting Configuration</h2>";
echo "<pre>";
echo "error_reporting: " . error_reporting() . PHP_EOL;
echo "display_errors: " . ini_get('display_errors') . PHP_EOL;
echo "display_startup_errors: " . ini_get('display_startup_errors') . PHP_EOL;
echo "log_errors: " . ini_get('log_errors') . PHP_EOL;
echo "error_log: " . ini_get('error_log') . PHP_EOL;
echo "</pre>";

// Trigger error untuk testing
// trigger_error("Test error for debugging purposes", E_USER_NOTICE);
?>
