<?php
include_once 'db_connection.php';
$page_title = "リース審査追加";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $orders_stmt = $conn->query("SELECT o.id, o.customer_name, o.order_date, e.full_name AS sales_rep, c.company_id 
                                 FROM orders o 
                                 LEFT JOIN employees e ON o.sales_rep_id = e.employee_id 
                                 LEFT JOIN companies c ON o.customer_name = c.company_name 
                                 WHERE o.negotiation_status NOT IN ('工事前キャンセル', '工事後キャンセル', '書換完了')");
    $orders = $orders_stmt->fetchAll();
    $providers_stmt = $conn->query("SELECT provider_id, provider_name FROM lease_providers");
    $providers = $providers_stmt->fetchAll();
?>
<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
    .form-group input, .form-group select { width: 100%; max-width: 300px; padding: 5px; box-sizing: border-box; }
</style>
<form method="POST" action="process_add_credit_applications.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <div class="form-group">
        <label for="order_id" class="required">顧客企業名 (受注情報):</label>
        <select id="order_id" name="order_id" required onchange="updateCompanyId(this)" onkeydown="preventEnterSubmit(event)">
            <option value="">選択してください</option>
            <?php foreach ($orders as $order) {
                $display = htmlspecialchars($order['customer_name'] . ' (' . $order['order_date'] . ', 担当: ' . ($order['sales_rep'] ?? '') . ')');
                echo "<option value='" . htmlspecialchars($order['id']) . "' data-company-id='" . htmlspecialchars($order['company_id']) . "'>" . $display . "</option>";
            } ?>
        </select>
        <input type="hidden" id="company_id" name="company_id">
    </div>
    <div class="form-group">
        <label for="provider_id" class="required">リース会社:</label>
        <select id="provider_id" name="provider_id" required onkeydown="preventEnterSubmit(event)">
            <option value="">選択してください</option>
            <?php foreach ($providers as $provider) {
                echo "<option value='" . htmlspecialchars($provider['provider_id']) . "'>" . htmlspecialchars($provider['provider_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="application_date" class="required">申請日:</label>
        <input type="date" id="application_date" name="application_date" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="monthly_fee" class="required">月額 (税抜):</label>
        <input type="number" id="monthly_fee" name="monthly_fee" step="1" min="0" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="total_payments" class="required">回数:</label>
        <input type="number" id="total_payments" name="total_payments" min="1" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="memo">メモ:</label>
        <input type="text" id="memo" name="memo" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="expected_payment" class="required">見積金額 (税込):</label>
        <input type="number" id="expected_payment" name="expected_payment" step="1" min="0" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="status" class="required">ステータス:</label>
        <select id="status" name="status" required onkeydown="preventEnterSubmit(event)">
            <option value="">選択してください</option>
            <option value="準備中">準備中</option>
            <option value="与信中">与信中</option>
            <option value="条件あり">条件あり</option>
            <option value="与信OK">与信OK</option>
            <option value="特案OK">特案OK</option>
            <option value="与信NG">与信NG</option>
            <option value="手続き待ち">手続き待ち</option>
            <option value="手続きOK">手続きOK</option>
            <option value="承認待ち">承認待ち</option>
            <option value="承認完了">承認完了</option>
            <option value="証明書待ち">証明書待ち</option>
            <option value="入金待ち">入金待ち</option>
            <option value="入金完了">入金完了</option>
            <option value="商談保留">商談保留</option>
            <option value="商談キャンセル">商談キャンセル</option>
            <option value="承認後キャンセル">承認後キャンセル</option>
        </select>
    </div>
    <div class="form-group">
        <label for="special_case">特案:</label>
        <select id="special_case" name="special_case" onkeydown="preventEnterSubmit(event)">
            <option value="">空白</option>
            <option value="補償">補償</option>
        </select>
    </div>
    <div style="margin-top: 10px;">
        <input type="submit" value="追加" style="margin-right: 10px;">
        <input type="button" value="キャンセル" onclick="window.location.href='credit_applications_list.php';">
    </div>
</form>
<script>
function updateCompanyId(select) {
    var selectedOption = select.options[select.selectedIndex];
    var companyId = selectedOption.getAttribute('data-company-id');
    document.getElementById('company_id').value = companyId;
}

function preventEnterSubmit(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
}
</script>
</body>
</html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>