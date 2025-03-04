<?php
include_once 'db_connection.php';
$page_title = "リース契約編集";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $contract_id = $_GET['contract_id'] ?? '';
    if (empty($contract_id)) throw new Exception('契約IDが指定されていません');

    $stmt = $conn->prepare("SELECT * FROM lease_contracts WHERE contract_id = ?");
    $stmt->execute([$contract_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('契約が見つかりません');

    $companies_stmt = $conn->query("SELECT company_id, company_name FROM companies");
    $companies = $companies_stmt->fetchAll();
    $providers_stmt = $conn->query("SELECT provider_id, provider_name FROM lease_providers");
    $providers = $providers_stmt->fetchAll();
    $equipment_stmt = $conn->query("SELECT equipment_id, equipment_name, model_number FROM equipment_master");
    $equipment = $equipment_stmt->fetchAll();

    $current_equipment_stmt = $conn->prepare("SELECT equipment_id FROM leased_equipment WHERE contract_id = ?");
    $current_equipment_stmt->execute([$contract_id]);
    $current_equipment = $current_equipment_stmt->fetchAll(PDO::FETCH_COLUMN);
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
<form method="POST" action="process_edit_lease_contracts.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="contract_id" value="<?php echo htmlspecialchars($item['contract_id']); ?>">
    <div class="form-group">
        <label for="company_id" class="required">顧客企業:</label>
        <select id="company_id" name="company_id" disabled required>
            <option value="">選択してください</option>
            <?php foreach ($companies as $company) {
                $selected = $company['company_id'] == $item['company_id'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($company['company_id']) . "' $selected>" . htmlspecialchars($company['company_name']) . "</option>";
            } ?>
        </select>
        <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($item['company_id']); ?>">
    </div>
    <div class="form-group">
        <label for="provider_id" class="required">リース会社:</label>
        <select id="provider_id" name="provider_id" disabled required>
            <option value="">選択してください</option>
            <?php foreach ($providers as $provider) {
                $selected = $provider['provider_id'] == $item['provider_id'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($provider['provider_id']) . "' $selected>" . htmlspecialchars($provider['provider_name']) . "</option>";
            } ?>
        </select>
        <input type="hidden" name="provider_id" value="<?php echo htmlspecialchars($item['provider_id']); ?>">
    </div>
    <div class="form-group">
        <label for="start_date" class="required">開始日:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($item['start_date']); ?>" disabled required>
        <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($item['start_date']); ?>">
    </div>
    <div class="form-group">
        <label for="end_date" class="required">終了日:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($item['end_date']); ?>" disabled required>
        <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($item['end_date']); ?>">
    </div>
    <div class="form-group">
        <label for="monthly_fee" class="required">リース月額 (税込):</label>
        <input type="number" id="monthly_fee" name="monthly_fee" step="0.01" min="0" value="<?php echo (int)$item['monthly_fee']; ?>" disabled required>
        <input type="hidden" name="monthly_fee" value="<?php echo htmlspecialchars($item['monthly_fee']); ?>">
    </div>
    <div class="form-group">
        <label for="total_payments" class="required">回数:</label>
        <input type="number" id="total_payments" name="total_payments" min="1" value="<?php echo htmlspecialchars($item['total_payments']); ?>" disabled required>
        <input type="hidden" name="total_payments" value="<?php echo htmlspecialchars($item['total_payments']); ?>">
    </div>
    <div class="form-group">
        <label for="equipment_ids">リース機器 (複数選択可):</label>
        <select id="equipment_ids" name="equipment_ids[]" multiple size="5" style="width: 300px;">
            <?php if ($equipment) {
                foreach ($equipment as $eq) {
                    $selected = in_array($eq['equipment_id'], $current_equipment) ? 'selected' : '';
                    $display_name = ($eq['model_number'] ? htmlspecialchars($eq['model_number']) . ' - ' : '') . htmlspecialchars($eq['equipment_name']);
                    echo "<option value='" . htmlspecialchars($eq['equipment_id']) . "' $selected>" . $display_name . "</option>";
                }
            } else {
                echo "<option value=''>機器が登録されていません</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="special_case" class="required">特案:</label>
        <select id="special_case" name="special_case" disabled>
            <option value="" <?php echo $item['special_case'] === '' ? 'selected' : ''; ?>>空白</option>
            <option value="補償" <?php echo $item['special_case'] === '補償' ? 'selected' : ''; ?>>補償</option>
        </select>
        <input type="hidden" name="special_case" value="<?php echo htmlspecialchars($item['special_case']); ?>">
    </div>
    <div class="form-group">
        <label for="status" class="required">ステータス:</label>
        <select id="status" name="status" required>
            <option value="">選択してください</option>
            <option value="contract_active" <?php echo $item['status'] === 'contract_active' ? 'selected' : ''; ?>>契約中</option>
            <option value="offsetting" <?php echo $item['status'] === 'offsetting' ? 'selected' : ''; ?>>相殺中</option>
            <option value="early_termination" <?php echo $item['status'] === 'early_termination' ? 'selected' : ''; ?>>中途解約</option>
            <option value="expired" <?php echo $item['status'] === 'expired' ? 'selected' : ''; ?>>満了</option>
            <option value="lost_to_competitor" <?php echo $item['status'] === 'lost_to_competitor' ? 'selected' : ''; ?>>他社流出</option>
        </select>
    </div>
    <div class="form-group">
        <label for="memo">メモ:</label>
        <textarea id="memo" name="memo"><?php echo htmlspecialchars($item['memo'] ?? ''); ?></textarea>
    </div>
    <div style="margin-top: 10px;">
        <input type="submit" value="更新" style="margin-right: 10px;">
        <input type="button" value="キャンセル" onclick="window.location.href='lease_contracts_list.php';">
    </div>
</form>
</body></html>
<?php
} catch (Exception $e) {
    error_log("Exception in edit_lease_contracts.php: " . $e->getMessage());
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>