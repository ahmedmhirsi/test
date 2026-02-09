<?php
// public/test_session.php
session_start();

if (!isset($_SESSION['count'])) {
    $_SESSION['count'] = 0;
}
$_SESSION['count']++;

echo "Session ID: " . session_id() . "<br>";
echo "Count: " . $_SESSION['count'] . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";
if (is_writable(session_save_path())) {
    echo "Session path is writable.<br>";
} else {
    echo "Session path is NOT writable.<br>";
}
echo "Session Data File: " . session_save_path() . '/sess_' . session_id() . "<br>";

