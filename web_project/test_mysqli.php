
<?php
if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

require "db_mysqli.php";

echo "MySQLi database connected successfully!";
?>
