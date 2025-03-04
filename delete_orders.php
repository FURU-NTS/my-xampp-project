<?php
include_once 'db_connection.php';
$page_title = "受注削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $id = $_GET['id'] ?? '';
    if (empty($id)) throw new Exception('受注IDが指定されていません');

    $stmt = $conn->prepare("SELECT o.*, 
                            e1.full_name AS sales_rep_1, 
                            e2.full_name AS sales_rep_2, 
                            e3.full_name AS sales_rep_3, 
                            e4.full_name AS sales_rep_4, 
                            a1.full_name AS appointment_rep_1, 
                            a2.full_name AS appointment_rep_2,
                            r.full_name AS rewriting_person
                            FROM orders o 
                            LEFT JOIN employees e1 ON o.sales_rep_id = e1.employee_id 
                            LEFT JOIN employees e2 ON o.sales_rep_id_2 = e2.employee_id 
                            LEFT JOIN employees e3 ON o.sales_rep_id_3 = e3.employee_id 
                            LEFT JOIN employees e4 ON o.sales_rep_id_4 = e4.employee_id 
                            LEFT JOIN employees a1 ON o.appointment_rep_id_1 = a1.employee_id 
                            LEFT JOIN employees a2 ON o.appointment_rep_id_2 = a2.employee_id 
                            LEFT JOIN employees r ON o.rewriting_person_id = r.employee_id 
                            WHERE o.id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('受注が見つかりません');
?>
<p>以下の受注を削除してもよろしいですか？</p>
<p>顧客名: <?php echo htmlspecialchars($item['customer_name'] ?? '未設定'); ?></p>
<p>客層: <?php echo htmlspecialchars($item['customer_type'] ?? '未設定'); ?></p>
<p>受注日: <?php echo htmlspecialchars($item['order_date'] ?? '未設定'); ?></p>
<p>月額 (税抜): <?php echo number_format($item['monthly_fee'], 0); ?> 円</p>
<p>回数: <?php echo htmlspecialchars($item['total_payments'] ?? '未設定'); ?></p>
<p>商談ステータス: <?php echo htmlspecialchars($item['negotiation_status'] ?? '未設定'); ?></p>
<p>工事ステータス: <?php echo htmlspecialchars($item['construction_status'] ?? '未設定'); ?></p>
<p>与信ステータス: <?php echo htmlspecialchars($item['credit_status'] ?? '未設定'); ?></p>
<p>書類ステータス: <?php echo htmlspecialchars($item['document_status'] ?? '未設定'); ?></p>
<p>書換ステータス: <?php echo htmlspecialchars($item['rewrite_status'] ?? '未設定'); ?></p>
<p>印鑑証明ステータス: <?php echo htmlspecialchars($item['seal_certificate_status'] ?? '未設定'); ?></p>
<p>メモ: <?php echo htmlspecialchars($item['memo'] ?? '未設定'); ?></p>
<p>担当者1: <?php echo htmlspecialchars($item['sales_rep_1'] ?? '未設定'); ?></p>
<p>担当者2: <?php echo htmlspecialchars($item['sales_rep_2'] ?? '未設定'); ?></p>
<p>担当者3: <?php echo htmlspecialchars($item['sales_rep_3'] ?? '未設定'); ?></p>
<p>担当者4: <?php echo htmlspecialchars($item['sales_rep_4'] ?? '未設定'); ?></p>
<p>アポイント者1: <?php echo htmlspecialchars($item['appointment_rep_1'] ?? '未設定'); ?></p>
<p>アポイント者2: <?php echo htmlspecialchars($item['appointment_rep_2'] ?? '未設定'); ?></p>
<p>書換担当: <?php echo htmlspecialchars($item['rewriting_person'] ?? '未設定'); ?></p>
<form method="POST" action="process_delete_orders.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
    <input type="submit" value="削除">
    <a href="orders_list.php">キャンセル</a>
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>