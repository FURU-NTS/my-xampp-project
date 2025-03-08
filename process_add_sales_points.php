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
        "INSERT INTO sales_points (order_id, employee_id, points, referral_points, vehicle_points, new_customer_bonus_no_appt, bonus, rewrite_date, points_granted_month, memo)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    foreach ($employees as $emp) {
        $employee_id = $emp['employee_id'] ?? '';
        $points = $emp['points'] ?? '';
        $referral_points = $emp['referral_points'] ?? '';
        $vehicle_points = $emp['vehicle_points'] ?? 0;
        $new_customer_bonus_no_appt = $emp['new_customer_bonus_no_appt'] ?? ''; // 追加
        $bonus = $emp['bonus'] ?? ''; // 追加
        $rewrite_date = !empty($emp['rewrite_date']) ? $emp['rewrite_date'] : null;
        $points_granted_month = !empty($emp['points_granted_month']) ? $emp['points_granted_month'] : null;
        $memo = !empty($emp['memo']) ? $emp['memo'] : null;

        if (empty($employee_id)) {
            throw new Exception('担当者が未入力です');
        }
        if ($points === '' && $referral_points === '') {
            throw new Exception('ポイントまたは紹介ポイントのいずれかを入力してください');
        }
        if ($points !== '' && (!is_numeric($points) || $points < 0 || floor($points) != $points)) {
            throw new Exception('ポイントは0以上の整数を入力してください');
        }
        if ($referral_points !== '' && (!is_numeric($referral_points) || $referral_points < 0 || floor($referral_points) != $referral_points)) {
            throw new Exception('紹介ポイントは0以上の整数を入力してください');
        }
        if ($vehicle_points !== '' && (!is_numeric($vehicle_points) || $vehicle_points < 0 || floor($vehicle_points) != $vehicle_points)) {
            throw new Exception('車輛ポイントは0以上の整数を入力してください');
        }
        if ($new_customer_bonus_no_appt !== '' && (!is_numeric($new_customer_bonus_no_appt) || $new_customer_bonus_no_appt < 0 || floor($new_customer_bonus_no_appt) != $new_customer_bonus_no_appt)) { // 追加
            throw new Exception('新規ボーナス（アポ無）は0以上の整数を入力してください');
        }
        if ($bonus !== '' && (!is_numeric($bonus) || $bonus < 0 || floor($bonus) != $bonus)) { // 追加
            throw new Exception('報奨金は0以上の整数を入力してください');
        }
        if ($points_granted_month && !preg_match('/^\d{4}-\d{2}$/', $points_granted_month)) {
            throw new Exception('ポイント付与月はYYYY-MM形式で入力してください');
        }

        $points = $points === '' ? 0 : $points;
        $referral_points = $referral_points === '' ? 0 : $referral_points;
        $new_customer_bonus_no_appt = $new_customer_bonus_no_appt === '' ? null : $new_customer_bonus_no_appt; // 追加
        $bonus = $bonus === '' ? null : $bonus; // 追加

        $stmt->execute([
            $order_id, $employee_id, $points, $referral_points, $vehicle_points, $new_customer_bonus_no_appt, $bonus, $rewrite_date, $points_granted_month, $memo
        ]);
    }

    header('Location: sales_points_list.php?status=success&message=ポイントが追加されました');
    exit;
} catch (Exception $e) {
    header('Location: add_sales_points.php?order_id=' . urlencode($order_id) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>