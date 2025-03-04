<?php
include_once 'db_connection.php';
$page_title = "リース契約追加";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $companies_stmt = $conn->query("SELECT company_id, company_name FROM companies");
    $companies = $companies_stmt->fetchAll();
    $providers_stmt = $conn->query("SELECT provider_id, provider_name FROM lease_providers");
    $providers = $providers_stmt->fetchAll();
    $equipment_stmt = $conn->query("SELECT equipment_id, equipment_name, model_number FROM equipment_master");
    $equipment = $equipment_stmt->fetchAll();

    $credit_application_id = $_GET['credit_application_id'] ?? '';
    $company_id = $_GET['company_id'] ?? '';
    $customer_name = $_GET['customer_name'] ?? '';
    $monthly_fee = $_GET['monthly_fee'] ?? '';
    $total_payments = $_GET['total_payments'] ?? '';
    $provider_id = $_GET['provider_id'] ?? '';

    // credit_applications から special_case を取得
    $special_case = '';
    if (!empty($credit_application_id)) {
        $stmt = $conn->prepare("SELECT special_case FROM credit_applications WHERE application_id = ?");
        $stmt->execute([$credit_application_id]);
        $credit_data = $stmt->fetch();
        if ($credit_data === false) {
            error_log("No data found for credit_application_id: $credit_application_id");
        }
        $special_case = $credit_data['special_case'] ?? '';
    }

    error_log("add_lease_contracts.php - Params: credit_application_id=$credit_application_id, company_id=$company_id, provider_id=$provider_id, monthly_fee=$monthly_fee, total_payments=$total_payments, special_case=$special_case");
?>
<style>
    .form-group { 
        margin-bottom: 15px; 
    }
    .form-group label { 
        display: block; 
        font-weight: bold; 
        margin-bottom: 5px; 
    }
    .form-group input, .form-group select, .form-group textarea { 
        width: 100%; 
        max-width: 300px; 
        padding: 5px; 
        box-sizing: border-box; 
    }
    .form-group textarea { 
        height: 60px; 
    }
    .form-group select[disabled], .form-group input[disabled] { 
        background-color: #f0f0f0; 
        color: #666; 
        border: 1px solid #ccc; 
        cursor: not-allowed; 
    }
    .required::after { 
        content: " *"; 
        color: red; 
    }
</style>
<?php
if (isset($_GET['status']) && isset($_GET['message'])) {
    $class = $_GET['status'] === 'success' ? 'success' : 'error';
    echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
}
?>
<form method="POST" action="process_add_lease_contracts.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="credit_application_id" value="<?php echo htmlspecialchars($credit_application_id); ?>">
    <div class="form-group">
        <label for="company_id" class="required">顧客企業:</label>
        <select id="company_id" name="company_id" disabled required>
            <option value="">選択してください</option>
            <?php foreach ($companies as $company) {
                $selected = $company['company_id'] == $company_id ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($company['company_id']) . "' $selected>" . htmlspecialchars($company['company_name']) . "</option>";
            } ?>
        </select>
        <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($company_id); ?>">
    </div>
    <div class="form-group">
        <label for="provider_id" class="required">リース会社:</label>
        <select id="provider_id" name="provider_id" disabled required>
            <option value="">選択してください</option>
            <?php foreach ($providers as $provider) {
                $selected = $provider['provider_id'] == $provider_id ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($provider['provider_id']) . "' $selected>" . htmlspecialchars($provider['provider_name']) . "</option>";
            } ?>
        </select>
        <input type="hidden" name="provider_id" value="<?php echo htmlspecialchars($provider_id); ?>">
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
        <label for="monthly_fee" class="required">リース月額 (税込):</label>
        <select id="monthly_fee" name="monthly_fee" disabled required>
            <option value="<?php echo htmlspecialchars($monthly_fee); ?>" selected><?php echo number_format($monthly_fee, 0); ?> 円</option>
        </select>
        <input type="hidden" name="monthly_fee" value="<?php echo htmlspecialchars($monthly_fee); ?>">
    </div>
    <div class="form-group">
        <label for="total_payments" class="required">回数:</label>
        <select id="total_payments" name="total_payments" disabled required>
            <option value="<?php echo htmlspecialchars($total_payments); ?>" selected><?php echo htmlspecialchars($total_payments); ?></option>
        </select>
        <input type="hidden" name="total_payments" value="<?php echo htmlspecialchars($total_payments); ?>">
    </div>
    <div class="form-group">
        <label for="equipment_ids">リース機器 (複数選択可):</label>
        <select id="equipment_ids" name="equipment_ids[]" multiple size="5" style="width: 300px;">
            <?php if ($equipment) {
                foreach ($equipment as $eq) {
                    $display_name = ($eq['model_number'] ? htmlspecialchars($eq['model_number']) . ' - ' : '') . htmlspecialchars($eq['equipment_name']);
                    echo "<option value='" . htmlspecialchars($eq['equipment_id']) . "'>" . $display_name . "</option>";
                }
            } else {
                echo "<option value=''>機器が登録されていません</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="special_case" class="required">特案:</label>
        <select id="special_case" name="special_case" disabled>
            <option value="" <?php echo $special_case === '' ? 'selected' : ''; ?>>空白</option>
            <option value="補償" <?php echo $special_case === '補償' ? 'selected' : ''; ?>>補償</option>
        </select>
        <input type="hidden" name="special_case" value="<?php echo htmlspecialchars($special_case); ?>">
    </div>
    <div class="form-group">
        <label for="status" class="required">ステータス:</label>
        <select id="status" name="status" required>
            <option value="">選択してください</option>
            <option value="contract_active">契約中</option>
            <option value="offsetting">相殺中</option>
            <option value="early_termination">中途解約</option>
            <option value="expired">満了</option>
            <option value="lost_to_competitor">他社流出</option>
        </select>
    </div>
    <div class="form-group">
        <label for="memo">メモ:</label>
        <textarea id="memo" name="memo"></textarea>
    </div>
    <div style="margin-top: 10px;">
        <input type="submit" value="追加" style="margin-right: 10px;">
        <input type="button" value="キャンセル" onclick="window.location.href='credit_applications_list.php';">
    </div>
</form>
</body></html>
<?php
} catch (Exception $e) {
    error_log("Exception in add_lease_contracts.php: " . $e->getMessage());
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>