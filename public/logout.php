<?php

/**
 * Logout Page
 */
require_once '../includes/session.php';
require_once '../includes/functions.php';

logout_user();
set_flash('success', 'You have been logged out successfully');
redirect('login.php');
