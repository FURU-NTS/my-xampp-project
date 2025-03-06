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

    $conn->beginTransaction();

    // 既存データを削除
    $delete_stmt = $conn->prepare("DELETE FROM sales_points WHERE order_id = ?");
    $delete_stmt->execute([$order_id]);

    // 新データを挿入
    $insert_stmt = $conn->prepare(
        "INSERT INTO sales_points (order_id, employee_id, points, rewrite_date, removal_points, 
                                   points_revision, points_granted_month, points_changed_month, memo)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    foreach ($employees as $emp) {
        $employee_id = $emp['employee_id'] ?? '';
        $points = $emp['points'] ?? '';
        $rewrite_date = !empty($emp['rewrite_date']) ? $emp['rewrite_date'] : null;
        $removal_points = !empty($emp['removal_points']) ? $emp['removal_points'] : null;
        $points_revision = !empty($emp['points_revision']) ? $emp['points_revision'] : null;
        $points_granted_month = !empty($emp['points_granted_month']) ? $emp['points_granted_month'] : null;
        $points_changed_month = !empty($emp['points_changed_month']) ? $emp['points_changed_month'] : null;
        $memo = !empty($emp['memo']) ? $emp['memo'] : null;

        if (empty($employee_id) || $points === '') {
            throw new Exception('担当者またはポイントが未入力です');
        }
        if (!is_numeric($points) || $points < 0) throw new Exception('ポイントは0以上の整数を入力してください');
        if ($removal_points !== null && (!is_numeric($removal_points) || $removal_points < 0)) {
            throw new Exception('撤去ポイントは0以上の整数を入力してください');
        }
        if ($points_revision !== null && !is_numeric($points_revision)) {
            throw new Exception('ポイント修正は整数を入力してください');
        }
        if ($points_granted_month && !preg_match('/^\d{4}-\d{2}$/', $points_granted_month)) {
            throw new Exception('ポイント付与月はYYYY-MM形式で入力してください');
        }
        if ($points_changed_month && !preg_match('/^\d{4}-\d{2}$/', $points_changed_month)) {
            throw new Exception('ポイント変更月はYYYY-MM形式で入力してください');
        }

        $insert_stmt->execute([
            $order_id, $employee_id, $points, $rewrite_date, $removal_points, $points_revision,
            $points_granted_month, $points_changed_month, $memo
        ]);
    }

    $conn->commit();
    header('Location: sales_points_list.php?status=success&message=ポイントが更新されました');
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    header('Location: edit_sales_points.php?order_id=' . urlencode($order_id) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>