<?php
// api/leaderboard.php - Export leaderboard as JSON
require_once '../includes/leaderboard.class.php';
header('Content-Type: application/json');
$leaderboardObj = new Leaderboard();
$top = $leaderboardObj->getTop(10);
echo json_encode($top);
