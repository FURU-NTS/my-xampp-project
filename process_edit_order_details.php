<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $id = $_POST['id'] ?? '';
    $order_id = $_POST['order_id'] ?? '';
    $sales_rep = trim($_POST['sales_rep'] ?? '');
    $mobile_revision = $_POST['mobile_revision'] ?? '';
    $mobile_content = trim($_POST['mobile_content'] ?? '');
    $mobile_monitor_fee_a = $_POST['mobile_monitor_fee_a'] ?? '';
    $monitor_content_a = trim($_POST['monitor_content_a'] ?? '');
    $monitor_fee_b = $_POST['monitor_fee_b'] ?? '';
    $monitor_content_b = trim($_POST['monitor_content_b'] ?? '');
    $monitor_fee_c = $_POST['monitor_fee_c'] ?? '';
    $monitor_content_c = trim($_POST['monitor_content_c'] ?? '');
    $monitor_total = $_POST['monitor_total'] ?? '';
    $service_item_1 = $_POST['service_item_1'] ?? '';
    $service_content_1 = trim($_POST['service_content_1'] ?? '');
    $service_item_2 = $_POST['service_item_2'] ?? '';
    $service_content_2 = trim($_POST['service_content_2'] ?? '');
    $service_item_3 = $_POST['service_item_3'] ?? '';
    $service_content_3 = trim($_POST['service_content_3'] ?? '');
    $service_total = $_POST['service_total'] ?? '';
    $others = trim($_POST['others'] ?? '');

    if (empty($id) || empty($order_id)) throw new Exception('IDと受注は必須です');
    if ($mobile_revision !== '' && (!is_numeric($mobile_revision) || $mobile_revision < 0)) throw new Exception('携帯見直し金額は0以上の整数を入力してください');
    if ($mobile_monitor_fee_a !== '' && (!is_numeric($mobile_monitor_fee_a) || $mobile_monitor_fee_a < 0)) throw new Exception('モニター費Aは0以上の整数を入力してください');
    if ($monitor_fee_b !== '' && (!is_numeric($monitor_fee_b) || $monitor_fee_b < 0)) throw new Exception('モニター費Bは0以上の整数を入力してください');
    if ($monitor_fee_c !== '' && (!is_numeric($monitor_fee_c) || $monitor_fee_c < 0)) throw new Exception('モニター費Cは0以上の整数を入力してください');
    if ($monitor_total !== '' && (!is_numeric($monitor_total) || $monitor_total < 0)) throw new Exception('モニター合計は0以上の整数を入力してください');
    if ($service_item_1 !== '' && (!is_numeric($service_item_1) || $service_item_1 < 0)) throw new Exception('サービス品1金額は0以上の整数を入力してください');
    if ($service_item_2 !== '' && (!is_numeric($service_item_2) || $service_item_2 < 0)) throw new Exception('サービス品2金額は0以上の整数を入力してください');
    if ($service_item_3 !== '' && (!is_numeric($service_item_3) || $service_item_3 < 0)) throw new Exception('サービス品3金額は0以上の整数を入力してください');
    if ($service_total !== '' && (!is_numeric($service_total) || $service_total < 0)) throw new Exception('サービス合計は0以上の整数を入力してください');

    $stmt = $conn->prepare(
        "UPDATE order_details SET 
            order_id = ?, sales_rep = ?, mobile_revision = ?, mobile_content = ?, 
            mobile_monitor_fee_a = ?, monitor_content_a = ?, monitor_fee_b = ?, monitor_content_b = ?, 
            monitor_fee_c = ?, monitor_content_c = ?, monitor_total = ?, 
            service_item_1 = ?, service_content_1 = ?, service_item_2 = ?, service_content_2 = ?, 
            service_item_3 = ?, service_content_3 = ?, service_total = ?, others = ?
         WHERE id = ?"
    );
    $stmt->execute([
        $order_id, $sales_rep ?: null, $mobile_revision ?: null, $mobile_content ?: null,
        $mobile_monitor_fee_a ?: null, $monitor_content_a ?: null, $monitor_fee_b ?: null, $monitor_content_b ?: null,
        $monitor_fee_c ?: null, $monitor_content_c ?: null, $monitor_total ?: null,
        $service_item_1 ?: null, $service_content_1 ?: null, $service_item_2 ?: null, $service_content_2 ?: null,
        $service_item_3 ?: null, $service_content_3 ?: null, $service_total ?: null, $others ?: null,
        $id
    ]);

    header('Location: orders_list.php?status=success&message=受注詳細が更新されました'); // 変更: order_details_list.php → orders_list.php
    exit;
} catch (Exception $e) {
    header('Location: edit_order_details.php?id=' . urlencode($_POST['id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>