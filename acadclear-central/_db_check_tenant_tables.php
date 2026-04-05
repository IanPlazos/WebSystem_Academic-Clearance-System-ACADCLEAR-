<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3306", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbs = ["christianU_acadclear", "christianu_acadclear"];
foreach ($dbs as $db) {
  $exists = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$db."'")->fetchAll(PDO::FETCH_ASSOC);
  echo "DB {$db} exists: ".(!empty($exists)?"yes":"no")."\n";
  if (!empty($exists)) {
    $tables = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".$db."' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables (".count($tables)."): ".implode(', ', $tables)."\n";
  }
}
?>
