<?php
header('Content-Type: text/plain');
$stmt = $pdo->query("SELECT COUNT(*) as count FROM visits WHERE DATE(created_at) = CURDATE()";
echo $stmt->fetch()['count'];
