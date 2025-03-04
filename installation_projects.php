<?php
include_once 'db_connection.php';
$page_title = "工事プロジェクト追加";
include_once 'header.php';

try {
    $conn = getDBConnection();

    // 条件付きで orders から顧客名を取得
    $stmt = $conn->query("SELECT o.id, o.customer_name, o.company_id 
                          FROM orders o 
                          WHERE o.negotiation_status IN ('進行中', '与信怪しい', '書換完了') 
                          ORDER BY o.customer_name");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 既存のリース契約を取得
    $contracts_stmt = $conn->query("SELECT lc.contract_id, lc.company_id, c.company_name 
                                    FROM lease_contracts lc 
                                    LEFT JOIN companies c ON lc.company_id = c.company_id");
    $contracts = $contracts_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
    .form-group input, .form-group select { width: 100%; max-width: 300px; padding: 5px; box-sizing: border-box; }
    .required::after { content: " *"; color: red; }
    .error { color: red; }
    .success { color: green; }
</style>
<form method="POST" action="process_add_installation_projects.php">
    <div class="form-group">
        <label for="order_id" class="required">顧客名（受注）:</label>
        <select id="order_id" name="order_id" required onchange="updateContractSelect(this)">
            <option value="">選択してください</option>
            <?php foreach ($orders as $order) {
                echo "<option value='" . htmlspecialchars($order['id']) . "' data-company-id='" . htmlspecialchars($order['company_id']) . "'>" . htmlspecialchars($order['customer_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="contract_id" class="required">契約ID:</label>
        <select id="contract_id" name="contract_id" required>
            <option value="">顧客を選択して契約を選択</option>
            <?php foreach ($contracts as $contract) {
                echo "<option value='" . htmlspecialchars($contract['contract_id']) . "' data-company-id='" . htmlspecialchars($contract['company_id']) . "'>" . htmlspecialchars($contract['contract_id']) . " - " . htmlspecialchars($contract['company_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="status" class="required">ステータス:</label>
        <select id="status" name="status" required>
            <option value="planning">計画中</option>
            <option value="in_progress">進行中</option>
            <option value="completed">完了</option>
        </select>
    </div>
    <div class="form-group">
        <label for="start_date" class="required">開始日:</label>
        <input type="date" id="start_date" name="start_date" required>
    </div>
    <div class="form-group">
        <label for="end_date" class="required">終了日:</label>
        <input type="date" id="end_date" name="end_date" required>
    </div>
    <div>
        <input type="submit" value="追加">
        <input type="button" value="キャンセル" onclick="window.location.href='installation_projects_list.php';">
    </div>
</form>

<script>
function updateContractSelect(select) {
    var companyId = select.options[select.selectedIndex].getAttribute('data-company-id');
    var contractSelect = document.getElementById('contract_id');
    var options = contractSelect.options;

    for (var i = 0; i < options.length; i++) {
        var optionCompanyId = options[i].getAttribute('data-company-id');
        if (optionCompanyId === null || optionCompanyId === companyId) {
            options[i].style.display = '';
        } else {
            options[i].style.display = 'none';
        }
    }
    contractSelect.selectedIndex = 0; // リセット
}
</script>
</body>
</html>
<?php
} catch (Exception $e) {
    error_log("Error in add_installation_projects.php: " . $e->getMessage());
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>