<?php
include_once 'db_connection.php';
include_once 'header.php';
$page_title = "リース審査削除";

try {
    $conn = getDBConnection();
    $application_id = $_GET['application_id'] ?? '';
    if (empty($application_id)) throw new Exception('申請IDが指定されていません');

    $stmt = $conn->prepare("SELECT ca.*, c.company_name AS customer_name, lp.provider_name, o.order_date 
                            FROM credit_applications ca 
                            LEFT JOIN companies c ON ca.company_id = c.company_id 
                            LEFT JOIN orders o ON ca.order_id = o.id 
                            LEFT JOIN lease_providers lp ON ca.provider_id = lp.provider_id 
                            WHERE ca.application_id = ?");
    $stmt->execute([$application_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('申請が見つかりません');
?>
<p>以下のリース審査を削除してもよろしいですか？</p>
<p>顧客企業: <?php echo htmlspecialchars($item['customer_name'] ?? ''); ?></p>
<p>リース会社: <?php echo htmlspecialchars($item['provider_name'] ?? ''); ?></p>
<p>受注日: <?php echo htmlspecialchars($item['order_date'] ?? ''); ?></p>
<p>申請日: <?php echo htmlspecialchars($item['application_date'] ?? ''); ?></p>
<p>月額 (税抜): <?php echo number_format($item['monthly_fee'], 0); ?> 円</p>
<p>回数: <?php echo htmlspecialchars($item['total_payments']); ?></p>
<p>メモ: <?php echo htmlspecialchars($item['memo'] ?? ''); ?></p>
<?php if ($_SESSION['is_admin']) { ?>
    <p>見積金額 (税込): <?php echo number_format($item['expected_payment'], 0); ?> 円</p>
<?php } ?>
<p>ステータス: <?php echo htmlspecialchars($item['status'] ?? ''); ?></p>
<p>特案: <?php echo htmlspecialchars($item['special_case'] === '補償' ? '補償' : ''); ?></p>
<form method="POST" action="process_delete_credit_applications.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($item['application_id']); ?>">
    <input type="submit" value="削除">
    <a href="credit_applications_list.php">キャンセル</a>
</form>
</body>
</html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>