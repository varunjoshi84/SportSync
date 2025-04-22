<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../backend/news.php';

$sport = isset($_GET['sport']) ? $_GET['sport'] : null;
$news = getLatestNews($sport);

echo json_encode($news); 