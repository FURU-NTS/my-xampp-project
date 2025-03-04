<?php
include_once 'db_connection.php';
$page_title = "リース契約削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $contract_id = $_GET['contract_id'] ?? '';
    if (empty($contract_id)) throw new Exception('契約IDが指定されていません');

    $stmt = $conn->prepare("SELECT lc.*, c.company_name, lp.provider_name 
                            FROM lease_contracts lc 
                            LEFT JOIN companies c ON lc.company_id = c.company_id 
                            LEFT JOIN lease_providers lp ON lc.provider_id = lp.provider_id 
                            WHERE lc.contract_id = ?");
    $stmt->execute([$contract_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('契約が見つかりません');

    // 残回数と残債の計算（自動計算された payments_made を使用）
    $current_date = new DateTime();
    $start_date = new DateTime($item['start_date']);
    $interval = $start_date->diff($current_date);
    $months_passed = ($interval->y * 12) + $interval->m;
    $remaining_payments = max(0, $item['total_payments'] - $item['payments_made']);
    $remaining_balance = $item['monthly_fee'] * $remaining_payments;

    // ステータス日本語化
    function translateStatus($status) {
        switch ($status) {
            case 'contract_active': return '契約中';
            case 'offsetting': return '相殺中';
            case 'early_termination': return '中途解約';
            case 'expired': return '満了';
            case 'lost_to_competitor': return '他社流出';
            default: return $status;
        }
    }
?>
<p>以下のリース契約を削除してもよろしいですか？</p>
<p>顧客企業: <?php echo htmlspecialchars($item['company_name']); ?></p>
<p>リース会社: <?php echo htmlspecialchars($item['provider_name']); ?></p>
<p>開始日: <?php echo htmlspecialchars($item['start_date']); ?></p>
<p>終了日: <?php echo htmlspecialchars($item['end_date']); ?></p>
<p>リース月額 (税込): <?php echo number_format($item['monthly_fee'], 0); ?> 円</p>
<p>回数: <?php echo htmlspecialchars($item['total_payments']); ?></p>
<p>支払済み回数: <?php echo htmlspecialchars($item['payments_made']); ?></p>
<p>残回数: <?php echo htmlspecialchars($remaining_payments); ?></p>
<p>残債: <?php echo number_format($remaining_balance, 0); ?> 円</p>
<p>特案: <?php echo htmlspecialchars($item['special_case'] === '補償' ? '補償' : ''); ?></p>
<p>ステータス: <?php echo translateStatus($item['status']); ?></p>
<form method="POST" action="process_delete_lease_contracts.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="contract_id" value="<?php echo htmlspecialchars($item['contract_id']); ?>">
    <input type="submit" value="削除">
    <a href="lease_contracts_list.php">キャンセル</a>
</form>
</body></html>
<?php
} catch (Exception $e) {
    error_log("Exception in delete_lease_contracts.php: " . $e->getMessage());
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>