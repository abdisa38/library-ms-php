<?php
/**
 * Logout
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

$user = new User();
$user->logout();

setFlash('success', 'You have been logged out successfully');
redirect('../../index.php');
?>
