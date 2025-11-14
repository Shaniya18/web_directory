<?php
// Alternative config approach
$config = [
    'db_host' => 'localhost',
    'db_user' => 'root', 
    'db_pass' => '',
    'db_name' => 'web_directory',
    'site_name' => 'Fiji Web Directory',
    'site_url' => 'http://localhost'
];

// Set constants from array
foreach ($config as $key => $value) {
    $constant_name = strtoupper($key);
    if (!defined($constant_name)) {
        define($constant_name, $value);
    }
}

date_default_timezone_set('Pacific/Fiji');
?>