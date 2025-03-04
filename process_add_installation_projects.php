<?php
include_once 'db_connection.php';

try {
    $conn = getDBConnection();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('無効なリクエストです');
    }

    $order_id = $_POST['order_id'] ?? '';
    $order_date = $_POST['order_date'] ?? '';
    $new_schedule_date = $_POST['new_schedule_date'] ?? '';
    $status = $_POST['status'] ?? '';

    if (empty($order_id) || empty($order_date) || empty($new_schedule_date) || empty($status)) {
        throw new Exception('必須項目が入力されていません');
    }

    // order_id の存在確認
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND negotiation_status IN ('進行中', '与信怪しい', '書換完了')");
    $stmt->execute([$order_id]);
    if (!$stmt->fetch()) {
        throw new Exception('指定された受注が見つかりません');
    }

    $stmt = $conn->prepare("INSERT INTO installation_projects (order_id, order_date, new_schedule_date, status) 
                            VALUES (?, ?, ?, ?)");
    $stmt->execute([$order_id, $order_date, $new_schedule_date, $status]);

    header("Location: installation_projects_list.php?status=success&message=新規工事プロジェクトが追加されました");
    exit;
} catch (Exception $e) {
    header("Location: installation_projects_list.php?status=error&message=" . urlencode($e->getMessage()));
    exit;
}
?>