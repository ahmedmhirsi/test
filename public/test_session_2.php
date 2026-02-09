<?php
// public/test_session_2.php
session_start();

if (!isset($_SESSION['count'])) {
    $_SESSION['count'] = 0;
}
$_SESSION['count']++;

echo "Session ID: " . session_id() . "<br>\n";
echo "Count: " . $_SESSION['count'] . "<br>\n";

$savePath = session_save_path();
if (empty($savePath)) {
    $savePath = sys_get_temp_dir();
}
echo "Session Save Path: " . $savePath . "<br>\n";

if (is_writable($savePath)) {
    echo "Session path is writable.<br>\n";
} else {
    echo "Session path is NOT writable.<br>\n";
}

$sessFile = $savePath . '/sess_' . session_id();
echo "Session Data File: " . $sessFile . "<br>\n";

if (file_exists($sessFile)) {
    echo "Session file exists. Size: " . filesize($sessFile) . " bytes.<br>\n";
    echo "Content: " . htmlspecialchars(file_get_contents($sessFile)) . "<br>\n";
} else {
    echo "Session file does NOT exist.<br>\n";
}
