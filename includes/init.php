<?php
/**
 * Initialization File
 * This file should be included at the beginning of every page
 * It loads all necessary configuration, database connection, and functions
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Initialize session
require_once __DIR__ . '/session.php';

// Load database connection
require_once __DIR__ . '/db.php';

// Load helper functions
require_once __DIR__ . '/functions.php';

// Set timezone
date_default_timezone_set('America/New_York');
