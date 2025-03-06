<?php
include_once 'db_connection.php';
$page_title = "受注編集";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $id = $_GET['id'] ?? '';
    if (empty($id)) throw new Exception('受注IDが指定されていません');

    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch();
    if (!$order) throw new Exception('受注が見つかりません');

    $employees_stmt = $conn->query("SELECT employee_id, full_name, department FROM employees");
    $employees = $employees_stmt->fetchAll();
?>
<style>
    .form-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        max-width: 1000px;
        margin: 0 auto;
    }
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
    .form-group input[readonly] { 
        background-color: #f0f0f0; 
        border: 1px solid #ccc; 
    }
    .error { 
        color: red; 
    }
    .button-group {
        grid-column: span 3;
        text-align: center;
        margin-top: 10px;
    }
</style>
<?php
if (isset($_GET['status']) && isset($_GET['message'])) {
    $class = $_GET['status'] === 'success' ? 'success' : 'error';
    echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
}
?>
<form method="POST" action="process_edit_orders.php" onsubmit="return validateForm()">
    <div class="form-container">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($order['id']); ?>">
        <div class="form-group">
            <label for="customer_name">顧客名:</label>
            <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($order['customer_name']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="customer_type">客層:</label>
            <select id="customer_type" name="customer_type">
                <option value="新規" <?php echo $order['customer_type'] === '新規' ? 'selected' : ''; ?>>新規</option>
                <option value="既存" <?php echo $order['customer_type'] === '既存' ? 'selected' : ''; ?>>既存</option>
                <option value="旧顧客" <?php echo $order['customer_type'] === '旧顧客' ? 'selected' : ''; ?>>旧顧客</option>
            </select>
        </div>
        <div class="form-group">
            <label for="order_date">受注日:</label>
            <input type="date" id="order_date" name="order_date" value="<?php echo htmlspecialchars($order['order_date']); ?>">
        </div>
        <div class="form-group">
            <label for="monthly_fee">月額 (税抜):</label>
            <input type="number" id="monthly_fee" name="monthly_fee" value="<?php echo htmlspecialchars($order['monthly_fee']); ?>">
        </div>
        <div class="form-group">
            <label for="total_payments">回数:</label>
            <input type="number" id="total_payments" name="total_payments" value="<?php echo htmlspecialchars($order['total_payments']); ?>">
        </div>
        <div class="form-group">
            <label for="negotiation_status">商談ステータス:</label>
            <select id="negotiation_status" name="negotiation_status">
                <option value="" <?php echo empty($order['negotiation_status']) ? 'selected' : ''; ?>>空白</option>
                <option value="未設定" <?php echo $order['negotiation_status'] === '未設定' ? 'selected' : ''; ?>>未設定</option>
                <option value="進行中" <?php echo $order['negotiation_status'] === '進行中' ? 'selected' : ''; ?>>進行中</option>
                <option value="与信怪しい" <?php echo $order['negotiation_status'] === '与信怪しい' ? 'selected' : ''; ?>>与信怪しい</option>
                <option value="工事前再説" <?php echo $order['negotiation_status'] === '工事前再説' ? 'selected' : ''; ?>>工事前再説</option>
                <option value="工事後再説" <?php echo $order['negotiation_status'] === '工事後再説' ? 'selected' : ''; ?>>工事後再説</option>
                <option value="工事前キャンセル" <?php echo $order['negotiation_status'] === '工事前キャンセル' ? 'selected' : ''; ?>>工事前キャンセル</option>
                <option value="工事後キャンセル" <?php echo $order['negotiation_status'] === '工事後キャンセル' ? 'selected' : ''; ?>>工事後キャンセル</option>
                <option value="書換完了" <?php echo $order['negotiation_status'] === '書換完了' ? 'selected' : ''; ?>>書換完了</option>
                <option value="承認完了" <?php echo $order['negotiation_status'] === '承認完了' ? 'selected' : ''; ?>>承認完了</option>
                <option value="承認後キャンセル" <?php echo $order['negotiation_status'] === '承認後キャンセル' ? 'selected' : ''; ?>>承認後キャンセル</option>
            </select>
        </div>
        <div class="form-group">
            <label for="construction_status">工事ステータス:</label>
            <select id="construction_status" name="construction_status">
                <option value="" <?php echo $order['construction_status'] === null ? 'selected' : ''; ?>>空白</option>
                <option value="待ち" <?php echo $order['construction_status'] === '待ち' ? 'selected' : ''; ?>>待ち</option>
                <option value="与信待ち" <?php echo $order['construction_status'] === '与信待ち' ? 'selected' : ''; ?>>与信待ち</option>
                <option value="残あり" <?php echo $order['construction_status'] === '残あり' ? 'selected' : ''; ?>>残あり</option>
                <option value="完了" <?php echo $order['construction_status'] === '完了' ? 'selected' : ''; ?>>完了</option>
                <option value="回収待ち" <?php echo $order['construction_status'] === '回収待ち' ? 'selected' : ''; ?>>回収待ち</option>
                <option value="回収完了" <?php echo $order['construction_status'] === '回収完了' ? 'selected' : ''; ?>>回収完了</option>
            </select>
        </div>
        <div class="form-group">
            <label for="credit_status">与信ステータス:</label>
            <select id="credit_status" name="credit_status">
                <option value="" <?php echo $order['credit_status'] === null ? 'selected' : ''; ?>>空白</option>
                <option value="待ち" <?php echo $order['credit_status'] === '待ち' ? 'selected' : ''; ?>>待ち</option>
                <option value="与信中" <?php echo $order['credit_status'] === '与信中' ? 'selected' : ''; ?>>与信中</option>
                <option value="再与信中" <?php echo $order['credit_status'] === '再与信中' ? 'selected' : ''; ?>>再与信中</option>
                <option value="与信OK" <?php echo $order['credit_status'] === '与信OK' ? 'selected' : ''; ?>>与信OK</option>
                <option value="与信NG" <?php echo $order['credit_status'] === '与信NG' ? 'selected' : ''; ?>>与信NG</option>
            </select>
        </div>
        <div class="form-group">
            <label for="document_status">書類ステータス:</label>
            <select id="document_status" name="document_status">
                <option value="" <?php echo $order['document_status'] === null ? 'selected' : ''; ?>>空白</option>
                <option value="待ち" <?php echo $order['document_status'] === '待ち' ? 'selected' : ''; ?>>待ち</option>
                <option value="準備中" <?php echo $order['document_status'] === '準備中' ? 'selected' : ''; ?>>準備中</option>
                <option value="変更中" <?php echo $order['document_status'] === '変更中' ? 'selected' : ''; ?>>変更中</option>
                <option value="発送済" <?php echo $order['document_status'] === '発送済' ? 'selected' : ''; ?>>発送済</option>
                <option value="受取済" <?php echo $order['document_status'] === '受取済' ? 'selected' : ''; ?>>受取済</option>
            </select>
        </div>
        <div class="form-group">
            <label for="rewrite_status">書換ステータス:</label>
            <select id="rewrite_status" name="rewrite_status">
                <option value="" <?php echo $order['rewrite_status'] === null ? 'selected' : ''; ?>>空白</option>
                <option value="待ち" <?php echo $order['rewrite_status'] === '待ち' ? 'selected' : ''; ?>>待ち</option>
                <option value="準備中" <?php echo $order['rewrite_status'] === '準備中' ? 'selected' : ''; ?>>準備中</option>
                <option value="アポOK" <?php echo $order['rewrite_status'] === 'アポOK' ? 'selected' : ''; ?>>アポOK</option>
                <option value="残あり" <?php echo $order['rewrite_status'] === '残あり' ? 'selected' : ''; ?>>残あり</option>
                <option value="完了" <?php echo $order['rewrite_status'] === '完了' ? 'selected' : ''; ?>>完了</option>
            </select>
        </div>
        <div class="form-group">
            <label for="seal_certificate_status">印鑑証明ステータス:</label>
            <select id="seal_certificate_status" name="seal_certificate_status">
                <option value="" <?php echo $order['seal_certificate_status'] === null ? 'selected' : ''; ?>>空白</option>
                <option value="不要" <?php echo $order['seal_certificate_status'] === '不要' ? 'selected' : ''; ?>>不要</option>
                <option value="取得待" <?php echo $order['seal_certificate_status'] === '取得待' ? 'selected' : ''; ?>>取得待</option>
                <option value="回収待" <?php echo $order['seal_certificate_status'] === '回収待' ? 'selected' : ''; ?>>回収待</option>
                <option value="完了" <?php echo $order['seal_certificate_status'] === '完了' ? 'selected' : ''; ?>>完了</option>
            </select>
        </div>
        <div class="form-group">
            <label for="shipping_status">発送ステータス:</label>
            <select id="shipping_status" name="shipping_status">
                <option value="" <?php echo $order['shipping_status'] === null ? 'selected' : ''; ?>>空白</option>
                <option value="準備中" <?php echo $order['shipping_status'] === '準備中' ? 'selected' : ''; ?>>準備中</option>
                <option value="発送済" <?php echo $order['shipping_status'] === '発送済' ? 'selected' : ''; ?>>発送済</option>
            </select>
        </div>
        <div class="form-group">
            <label for="memo">メモ:</label>
            <textarea id="memo" name="memo"><?php echo htmlspecialchars($order['memo'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="sales_rep_id">担当者1:</label>
            <select id="sales_rep_id" name="sales_rep_id">
                <option value="" <?php echo $order['sales_rep_id'] === null ? 'selected' : ''; ?>>空白</option>
                <?php foreach ($employees as $emp) {
                    $selected = $order['sales_rep_id'] == $emp['employee_id'] ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>" . htmlspecialchars($emp['department'] . "/" . $emp['full_name']) . "</option>";
                } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="sales_rep_id_2">担当者2:</label>
            <select id="sales_rep_id_2" name="sales_rep_id_2">
                <option value="" <?php echo $order['sales_rep_id_2'] === null ? 'selected' : ''; ?>>空白</option>
                <?php foreach ($employees as $emp) {
                    $selected = $order['sales_rep_id_2'] == $emp['employee_id'] ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>" . htmlspecialchars($emp['department'] . "/" . $emp['full_name']) . "</option>";
                } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="sales_rep_id_3">担当者3:</label>
            <select id="sales_rep_id_3" name="sales_rep_id_3">
                <option value="" <?php echo $order['sales_rep_id_3'] === null ? 'selected' : ''; ?>>空白</option>
                <?php foreach ($employees as $emp) {
                    $selected = $order['sales_rep_id_3'] == $emp['employee_id'] ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>" . htmlspecialchars($emp['department'] . "/" . $emp['full_name']) . "</option>";
                } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="sales_rep_id_4">担当者4:</label>
            <select id="sales_rep_id_4" name="sales_rep_id_4">
                <option value="" <?php echo $order['sales_rep_id_4'] === null ? 'selected' : ''; ?>>空白</option>
                <?php foreach ($employees as $emp) {
                    $selected = $order['sales_rep_id_4'] == $emp['employee_id'] ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>" . htmlspecialchars($emp['department'] . "/" . $emp['full_name']) . "</option>";
                } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="appointment_rep_id_1">アポイント者1:</label>
            <select id="appointment_rep_id_1" name="appointment_rep_id_1">
                <option value="" <?php echo $order['appointment_rep_id_1'] === null ? 'selected' : ''; ?>>空白</option>
                <?php foreach ($employees as $emp) {
                    $selected = $order['appointment_rep_id_1'] == $emp['employee_id'] ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>" . htmlspecialchars($emp['department'] . "/" . $emp['full_name']) . "</option>";
                } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="appointment_rep_id_2">アポイント者2:</label>
            <select id="appointment_rep_id_2" name="appointment_rep_id_2">
                <option value="" <?php echo $order['appointment_rep_id_2'] === null ? 'selected' : ''; ?>>空白</option>
                <?php foreach ($employees as $emp) {
                    $selected = $order['appointment_rep_id_2'] == $emp['employee_id'] ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>" . htmlspecialchars($emp['department'] . "/" . $emp['full_name']) . "</option>";
                } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="rewriting_person_id">書換担当:</label>
            <select id="rewriting_person_id" name="rewriting_person_id">
                <option value="" <?php echo $order['rewriting_person_id'] === null ? 'selected' : ''; ?>>空白</option>
                <?php foreach ($employees as $emp) {
                    $selected = $order['rewriting_person_id'] == $emp['employee_id'] ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>" . htmlspecialchars($emp['department'] . "/" . $emp['full_name']) . "</option>";
                } ?>
            </select>
        </div>
        <div class="button-group">
            <input type="submit" value="更新" style="margin-right: 10px;">
            <input type="button" value="キャンセル" onclick="window.location.href='orders_list.php';">
        </div>
    </div>
</form>

<script>
function validateForm() {
    var order_date = document.getElementById('order_date').value;
    var monthly_fee = document.getElementById('monthly_fee').value;
    var total_payments = document.getElementById('total_payments').value;

    if (!order_date || monthly_fee === '' || total_payments === '') {
        alert('受注日、月額、回数は必須です。');
        return false;
    }
    return true;
}
</script>
</body>
</html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>