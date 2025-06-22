<?php
session_start();
require_once 'db_connect.php';

// 모든 장바구니 아이템 가져오기 (userid 구분 없이)
$stmt = $conn->prepare("
  SELECT sb.id, sb.itemname, sb.itemnum, sb.totalprice, it.image_url 
  FROM sbtable sb
  LEFT JOIN itemtable it ON sb.itemname = it.itemname
");
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
  $items[] = $row;
}

echo json_encode($items);
?>
