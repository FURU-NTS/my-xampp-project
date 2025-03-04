<?php
include_once 'db_connection.php';
$page_title = "受注追加";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $employees_stmt = $conn->query("SELECT employee_id, full_name FROM employees");
    $employees = $employees_stmt->fetchAll();

    $company_id = $_GET['company_id'] ?? '';
    $company_name = '';
    if (!empty($company_id)) {
        $stmt = $conn->prepare("SELECT company_name FROM companies WHERE company_id = ?");
        $stmt->execute([$company_id]);
        $company = $stmt->fetch();
        $company_name = $company ? htmlspecialchars($company['company_name']) : '未設定';
    }
?>
<form method="POST" action="process_add_orders.php" onsubmit="return validateForm()">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($company_id); ?>">
    <div class="form-group">
        <label for="customer_name" class="required">顧客名:</label>
        <input type="text" id="customer_name" name="customer_name" value="<?php echo $company_name; ?>" readonly required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="customer_type" class="required">客層:</label>
        <select id="customer_type" name="customer_type" required onkeydown="preventEnterSubmit(event)">
            <option value="新規">新規</option>
            <option value="既存">既存</option>
            <option value="旧顧客">旧顧客</option>
        </select>
    </div>
    <div class="form-group">
        <label for="order_date" class="required">受注日:</label>
        <input type="date" id="order_date" name="order_date" required onkeydown="preventEnterSubmit(event)">
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
        <label for="negotiation_status">商談ステータス:</label>
        <select id="negotiation_status" name="negotiation_status" onkeydown="preventEnterSubmit(event)">
            <option value="">未設定</option>
            <option value="進行中">進行中</option>
            <option value="与信怪しい">与信怪しい</option>
            <option value="工事前再説">工事前再説</option>
            <option value="工事後再説">工事後再説</option>
            <option value="工事前キャンセル">工事前キャンセル</option>
            <option value="工事後キャンセル">工事後キャンセル</option>
            <option value="書換完了">書換完了</option>
        </select>
    </div>
    <div class="form-group">
        <label for="construction_status">工事ステータス:</label>
        <select id="construction_status" name="construction_status" onkeydown="preventEnterSubmit(event)">
            <option value="">未設定</option>
            <option value="待ち">待ち</option>
            <option value="与信待ち">与信待ち</option>
            <option value="残あり">残あり</option>
            <option value="完了">完了</option>
            <option value="回収待ち">回収待ち</option>
        </select>
    </div>
    <div class="form-group">
        <label for="credit_status">与信ステータス:</label>
        <select id="credit_status" name="credit_status" onkeydown="preventEnterSubmit(event)">
            <option value="">未設定</option>
            <option value="待ち">待ち</option>
            <option value="与信中">与信中</option>
            <option value="再与信中">再与信中</option>
            <option value="与信OK">与信OK</option>
            <option value="与信NG">与信NG</option>
        </select>
    </div>
    <div class="form-group">
        <label for="document_status">書類ステータス:</label>
        <select id="document_status" name="document_status" onkeydown="preventEnterSubmit(event)">
            <option value="">未設定</option>
            <option value="待ち">待ち</option>
            <option value="準備中">準備中</option>
            <option value="変更中">変更中</option>
            <option value="発送済">発送済</option>
            <option value="受取済">受取済</option>
        </select>
    </div>
    <div class="form-group">
        <label for="rewrite_status">書換ステータス:</label>
        <select id="rewrite_status" name="rewrite_status" onkeydown="preventEnterSubmit(event)">
            <option value="">未設定</option>
            <option value="待ち">待ち</option>
            <option value="準備中">準備中</option>
            <option value="アポOK">アポOK</option>
            <option value="残あり">残あり</option>
            <option value="完了">完了</option>
        </select>
    </div>
    <div class="form-group">
        <label for="seal_certificate_status">印鑑証明ステータス:</label>
        <select id="seal_certificate_status" name="seal_certificate_status" onkeydown="preventEnterSubmit(event)">
            <option value="">未設定</option>
            <option value="不要">不要</option>
            <option value="取得待">取得待</option>
            <option value="回収待">回収待</option>
            <option value="完了">完了</option>
        </select>
    </div>
    <div class="form-group">
        <label for="sales_rep_id">担当者1:</label>
        <select id="sales_rep_id" name="sales_rep_id" onkeydown="preventEnterSubmit(event)">
            <option value="">選択なし</option>
            <?php foreach ($employees as $emp) {
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "'>" . htmlspecialchars($emp['full_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="sales_rep_id_2">担当者2:</label>
        <select id="sales_rep_id_2" name="sales_rep_id_2" onkeydown="preventEnterSubmit(event)">
            <option value="">選択なし</option>
            <?php foreach ($employees as $emp) {
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "'>" . htmlspecialchars($emp['full_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="sales_rep_id_3">担当者3:</label>
        <select id="sales_rep_id_3" name="sales_rep_id_3" onkeydown="preventEnterSubmit(event)">
            <option value="">選択なし</option>
            <?php foreach ($employees as $emp) {
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "'>" . htmlspecialchars($emp['full_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="sales_rep_id_4">担当者4:</label>
        <select id="sales_rep_id_4" name="sales_rep_id_4" onkeydown="preventEnterSubmit(event)">
            <option value="">選択なし</option>
            <?php foreach ($employees as $emp) {
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "'>" . htmlspecialchars($emp['full_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="appointment_rep_id_1">アポイント者1:</label>
        <select id="appointment_rep_id_1" name="appointment_rep_id_1" onkeydown="preventEnterSubmit(event)">
            <option value="">選択なし</option>
            <?php foreach ($employees as $emp) {
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "'>" . htmlspecialchars($emp['full_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="appointment_rep_id_2">アポイント者2:</label>
        <select id="appointment_rep_id_2" name="appointment_rep_id_2" onkeydown="preventEnterSubmit(event)">
            <option value="">選択なし</option>
            <?php foreach ($employees as $emp) {
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "'>" . htmlspecialchars($emp['full_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="rewriting_person_id">書換担当:</label>
        <select id="rewriting_person_id" name="rewriting_person_id" onkeydown="preventEnterSubmit(event)">
            <option value="">選択なし</option>
            <?php foreach ($employees as $emp) {
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "'>" . htmlspecialchars($emp['full_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div style="margin-top: 10px;">
        <input type="submit" value="追加" style="margin-right: 10px;">
        <input type="button" value="キャンセル" onclick="window.location.href='companies_list.php';">
    </div>
</form>

<script>
function preventEnterSubmit(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
}

function validateForm() {
    return true;
}
</script>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>