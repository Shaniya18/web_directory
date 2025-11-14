<?php
/* =====================================================
   FIJI WEB DIRECTORY - CONFIGURATION FILE (FIXED)
   ===================================================== */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // ← CHANGED
define('DB_PASS', '');               // ← CHANGED (empty for XAMPP default)
define('DB_NAME', 'web_directory');

define('SITE_NAME', 'Fiji Web Directory');
define('SITE_URL', 'http://localhost');

date_default_timezone_set('Pacific/Fiji');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
?>
