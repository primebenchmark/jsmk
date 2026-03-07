<?php
require_once __DIR__ . '/../config.php';
requireAdmin();

$testId = $_GET['id'] ?? '';
if (!$testId) {
    header('Location: tests.php');
    exit;
}

// Redirect to the test page with preview flag
header('Location: ../quiz.php?id=' . urlencode($testId) . '&preview=1');
exit;
