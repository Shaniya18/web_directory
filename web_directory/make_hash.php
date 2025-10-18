<?php
$new_password = 'MySuperSecureNewPassword123'; // <-- CHOOSE YOUR NEW PASSWORD HERE
$hash = password_hash($new_password, PASSWORD_DEFAULT);
echo "<h1>Copy this entire hash:</h1>";
echo "<textarea rows='3' cols='80'>" . $hash . "</textarea>";
?>