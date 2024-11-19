<?php
if (!function_exists('log_message')) {
    function log_message($message, $log_file = 'app.log') {
        error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, $log_file);
    }
}
?>
