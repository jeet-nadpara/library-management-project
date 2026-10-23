<?php
/**
 * Logout - destroys the session for whichever role was logged in.
 */
require_once 'config/db.php';
session_unset();
session_destroy();
header("Location: index.php");
exit();
