<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $order_id = $_POST['order_id'] ?? '';
    $employees = $_POST['employees'] ?? [];

    if (empty($order_id) || empty($employees)) {
        throw new Exception('必須項目を入力してください');
    }

    $stmt = $conn->prepare(
        "INSERT INTO sales_points (order_id, employee_id, points, rewrite_date, points_granted_month, memo)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    foreach ($employees as $emp) {
        $employee_id = $emp['employee_id'] ?? '';
        $points = $emp['points'] ?? '';
        $rewrite_date = !empty($emp['rewrite_date']) ? $emp['rewrite_date'] : null;
        $points_granted_month = !empty($emp['points_granted_month']) ? $emp['points_granted_month'] : null;
        $memo = !empty($emp['memo']) ? $emp['memo'] : null;

        if (empty($employee_id) || $points === '') {
            throw new Exception('担当者またはポイントが未入力です');
        }
        if (!is_numeric($points) || $points < 0) throw new Exception('ポイントは0以上の整数を入力してください');
        if ($points_granted_month && !preg_match('/^\d{4}-\d{2}$/', $points_granted_month)) {
            throw new Exception('ポイント付与月はYYYY-MM形式で入力してください');
        }

        $stmt->execute([
            $order_id, $employee_id, $points, $rewrite_date, $points_granted_month, $memo
        ]);
    }

    header('Location: sales_points_list.php?status=success&message=ポイントが追加されました');
    exit;
} catch (Exception $e) {
    header('Location: add_sales_points.php?order_id=' . urlencode($order_id) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>