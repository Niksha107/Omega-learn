 <!-- temporary file -->
<?php
if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

require "db.php";

echo "Database connected successfully!";

?>