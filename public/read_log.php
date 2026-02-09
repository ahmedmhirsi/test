<?php
// public/read_log.php
$logFile = '../var/log/dev.log';
if (!file_exists($logFile)) {
    echo "Log file not found.";
    exit;
}

$lines = file($logFile);
$found = [];
foreach ($lines as $line) {
    if (strpos($line, 'CSRF') !== false) {
        $found[] = $line;
    }
}
$lastThree = array_slice($found, -3);

foreach ($lastThree as $line) {
    echo htmlspecialchars($line) . "<br>";
}
