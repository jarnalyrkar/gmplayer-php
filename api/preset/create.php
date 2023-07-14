<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DB.php');

$data = null;
$db = new DB();
if (isset($_GET['name'])) {
  $data = $db->create_preset(htmlspecialchars($_GET['name']), htmlspecialchars($_GET['theme_id']));
}

header("Content-Type: application/json");
echo json_encode($data);
exit();
