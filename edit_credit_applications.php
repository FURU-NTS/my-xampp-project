<?php
include_once 'db_connection.php';
$page_title = "リース審査編集";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $application_id = $_GET['application_id'] ?? '';
    if (empty($application_id)) throw new Exception('申請IDが指定されていません');

    $stmt = $conn->prepare("SELECT * FROM credit_applications WHERE application_id = ?");
    $stmt->execute([$application_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) throw new Exception('申請が見つかりません');

    $companies_stmt = $conn->query("SELECT company_id, company_name FROM companies");
    $companies = $companies_stmt->fetchAll();
    $orders_stmt = $conn->query("SELECT id, customer_name, order_date FROM orders");
    $orders = $orders_stmt->fetchAll();
    $providers_stmt = $conn->query("SELECT provider_id, provider_name FROM lease_providers");
    $providers = $providers_stmt->fetchAll();

    // order_id から order_date を取得
    $order_date = '';
    foreach ($orders as $order) {
        if ($order['id'] == $item['order_id']) {
            $order_date = $order['order_date'];
            break;
        }
    }

    // デバッグ情報
    error_log("edit_credit_applications.php - application_id: $application_id, monthly_fee: " . var_export($item['monthly_fee'], true) . ", expected_payment: " . var_export($item['expected_payment'], true));
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
<form method="POST" action="process_edit_credit_applications.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($item['application_id']); ?>">
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
    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($item['order_id']); ?>">
    <div class="form-group">
        <label for="order_date" class="required">受注日:</label>
        <input type="date" id="order_date" name="order_date" value="<?php echo htmlspecialchars($order_date); ?>" disabled>
        <input type="hidden" name="order_date" value="<?php echo htmlspecialchars($order_date); ?>">
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
        <label for="application_date" class="required">申請日:</label>
        <input type="date" id="application_date" name="application_date" value="<?php echo htmlspecialchars($item['application_date']); ?>" disabled required>
        <input type="hidden" name="application_date" value="<?php echo htmlspecialchars($item['application_date']); ?>">
    </div>
    <div class="form-group">
        <label for="monthly_fee" class="required">月額 (税抜):</label>
        <input type="number" id="monthly_fee" name="monthly_fee" min="0" value="<?php echo htmlspecialchars(floor($item['monthly_fee'] ?? 0)); ?>" disabled required>
        <input type="hidden" name="monthly_fee" value="<?php echo htmlspecialchars($item['monthly_fee'] ?? 0); ?>">
    </div>
    <div class="form-group">
        <label for="total_payments" class="required">回数:</label>
        <input type="number" id="total_payments" name="total_payments" min="1" value="<?php echo htmlspecialchars($item['total_payments'] ?? ''); ?>" disabled required>
        <input type="hidden" name="total_payments" value="<?php echo htmlspecialchars($item['total_payments'] ?? ''); ?>">
    </div>
    <?php if ($_SESSION['is_admin']) { ?>
    <div class="form-group">
        <label for="expected_payment" class="required">見積金額 (税込):</label>
        <input type="number" id="expected_payment" name="expected_payment" min="0" value="<?php echo htmlspecialchars(floor($item['expected_payment'] ?? 0)); ?>" disabled required>
        <input type="hidden" name="expected_payment" value="<?php echo htmlspecialchars($item['expected_payment'] ?? 0); ?>">
    </div>
    <?php } else { ?>
    <input type="hidden" name="expected_payment" value="<?php echo htmlspecialchars($item['expected_payment'] ?? 0); ?>">
    <?php } ?>
    <div class="form-group">
        <label for="status" class="required">ステータス:</label>
        <select id="status" name="status" required>
            <option value="">選択してください</option>
            <?php
            $statuses = ['準備中', '与信中', '条件あり', '与信OK', '特案OK', '与信NG', '手続き待ち', '手続きOK', '承認待ち', '承認完了', '証明書待ち', '入金待ち', '入金完了', '商談保留', '商談キャンセル', '承認後キャンセル'];
            foreach ($statuses as $s) {
                $selected = $s == $item['status'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($s) . "' $selected>" . htmlspecialchars($s) . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="special_case">特案:</label>
        <select id="special_case" name="special_case">
            <option value="" <?php echo $item['special_case'] === '' ? 'selected' : ''; ?>>空白</option>
            <option value="補償" <?php echo $item['special_case'] === '補償' ? 'selected' : ''; ?>>補償</option>
        </select>
    </div>
    <div class="form-group">
        <label for="memo">メモ:</label>
        <textarea id="memo" name="memo"><?php echo htmlspecialchars($item['memo'] ?? ''); ?></textarea>
    </div>
    <?php if ($_SESSION['is_admin']) { ?>
    <div class="form-group">
        <label for="expected_payment_date">入金予定日:</label>
        <input type="date" id="expected_payment_date" name="expected_payment_date" value="<?php echo htmlspecialchars($item['expected_payment_date'] ?? ''); ?>">
    </div>
    <?php } else { ?>
    <input type="hidden" name="expected_payment_date" value="<?php echo htmlspecialchars($item['expected_payment_date'] ?? ''); ?>">
    <?php } ?>
    <div style="margin-top: 10px;">
        <input type="submit" value="更新" style="margin-right: 10px;">
        <input type="button" value="キャンセル" onclick="window.location.href='credit_applications_list.php';">
    </div>
</form>
</body></html>
<?php
} catch (Exception $e) {
    error_log("Exception in edit_credit_applications.php: " . $e->getMessage());
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>