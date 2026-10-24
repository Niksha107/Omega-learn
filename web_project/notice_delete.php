<?php
require "admin_access.php";
require_admin_access();

header("Location: notice_ajax.html");
exit;
