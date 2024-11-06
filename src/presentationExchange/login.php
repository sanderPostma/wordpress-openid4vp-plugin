<?php

defined('ABSPATH') or die('No script kiddies please!');

// Redirect the user back to the home page if already logged in.
if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

// do a session a start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


