<?php

/**
 * Landing/Index Page
 * Redirects logged-in users to dashboard, others to login
 */
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
