<?php

// Start the session if it hasn't already been started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Attempt auto-login if not already logged in
if (!isLoggedIn()) {  // Only check the cookie if not already logged in
  if(checkRememberMeCookie($pdo)){
   }
}
?>