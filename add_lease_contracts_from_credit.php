<?php
include_once 'db_connection.php';
include_once 'header.php';
$page_title = "リース契約追加 (審査データから)";

try {
    $conn = getDBConnection();
    $application_id = $_GET['application_id'] ?? '';
    if (empty($application_id)) throw new Exception('申請IDが指定されていません');

    $stmt = $conn->prepare("SELECT ca.*, c.company_name, lp.provider_name 
                            FROM credit_applications ca 
                            LEFT JOIN companies c ON ca.company_id = c.company_id 
                            LEFT JOIN lease_providers lp ON ca.provider_id = lp.provider_id 
                            WHERE ca.application_id = ? AND ca.status = '入金完了'");
    $stmt->execute([$application_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('有効な入金完了データが見つかりません');
?>
<form method="POST" action="process_add_lease_contracts_from_credit.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($item['application_id']); ?>">
    <div class="form-group">
        <label for="company_id">顧客企業:</label>
        <input type="text" id="company_id" value="<?php echo htmlspecialchars($item['company_name']); ?>" disabled>
        <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($item['company_id']); ?>">
    </div>
    <div class="form-group">
        <label for="provider_id">リース会社:</label>
        <input type="text" id="provider_id" value="<?php echo htmlspecialchars($item['provider_name']); ?>" disabled>
        <input type="hidden" name="provider_id" value="<?php echo htmlspecialchars($item['provider_id']); ?>">
    </div>
    <div class="form-group">
        <label for="start_date" class="required">開始日:</label>
        <input type="date" id="start_date" name="start_date" required>
    </div>
    <div class="form-group">
        <label for="end_date" class="required">終了日:</label>
        <input type="date" id="end_date" name="end_date" required>
    </div>
    <div class="form-group">
        <label for="monthly_fee">月額 (税抜):</label>
        <input type="number" id="monthly_fee" name="monthly_fee" step="1" min="0" value="<?php echo htmlspecialchars(round($item['monthly_fee'])); ?>" required>
    </div>
    <div class="form-group">
        <label for="total_payments">回数:</label>
        <input type="number" id="total_payments" name="total_payments" min="1" value="<?php echo htmlspecialchars($item['total_payments']); ?>" required>
    </div>
    <div class="form-group">
        <label for="status" class="required">ステータス:</label>
        <select id="status" name="status" required>
            <option value="contract_active">契約中</option>
            <option value="offsetting">相殺中</option>
            <option value="early_termination">中途解約</option>
            <option value="expired">満了</option>
            <option value="lost_to_competitor">他社流出</option>
        </select>
    </div>
    <input type="submit" value="契約登録">
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>