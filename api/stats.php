<?php
// api/stats.php - Export user stats as JSON
require_once '../includes/auth.class.php';
require_once '../includes/stats.class.php';
header('Content-Type: application/json');
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$user = $auth->currentUser();
$statsObj = new Stats();
$stats = $statsObj->getByUser($user['id']);
echo json_encode($stats);
