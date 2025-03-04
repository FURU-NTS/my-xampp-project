<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $equipment_name = trim($_POST['equipment_name'] ?? '');
    $equipment_type = trim($_POST['equipment_type'] ?? '');
    $manufacturer = trim($_POST['manufacturer'] ?? '');
    $model_number = trim($_POST['model_number'] ?? '');
    $price = $_POST['price'] ?? '';

    if (empty($equipment_name) || empty($equipment_type) || empty($price)) throw new Exception('必須項目を入力してください');
    if (!is_numeric($price) || $price < 0) throw new Exception('価格は0以上の数値を入力してください');

    $stmt = $conn->prepare(
        "INSERT INTO equipment_master (equipment_name, equipment_type, manufacturer, model_number, price) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$equipment_name, $equipment_type, $manufacturer, $model_number, $price]);

    header('Location: equipment_master_list.php?status=success&message=機器マスターが追加されました');
    exit;
} catch (Exception $e) {
    header('Location: add_equipment_master.php?status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>