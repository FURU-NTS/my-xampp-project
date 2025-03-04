<?php
include_once 'db_connection.php';
$page_title = "受注詳細編集";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $id = $_GET['id'] ?? '';
    if (empty($id)) throw new Exception('受注詳細IDが指定されていません');

    $stmt = $conn->prepare("SELECT od.*, o.customer_name, o.order_date, o.sales_rep_id, o.memo 
                            FROM order_details od 
                            LEFT JOIN orders o ON od.order_id = o.id 
                            WHERE od.id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('受注詳細が見つかりません');

    // 担当者名を取得
    $sales_rep_name = '';
    if ($item['sales_rep_id']) {
        $stmt = $conn->prepare("SELECT full_name FROM employees WHERE employee_id = ?");
        $stmt->execute([$item['sales_rep_id']]);
        $sales_rep = $stmt->fetch(PDO::FETCH_ASSOC);
        $sales_rep_name = $sales_rep ? $sales_rep['full_name'] : '未設定';
    } else {
        $sales_rep_name = $item['sales_rep'] ?? '未設定';
    }
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
    .form-group input, .form-group textarea { 
        width: 100%; 
        max-width: 300px; 
        padding: 5px; 
        box-sizing: border-box; 
    }
    .form-group input[type="number"] { 
        max-width: 150px; 
    }
    .form-group textarea { 
        height: 60px; 
    }
    .form-group input[readonly] { 
        background-color: #f0f0f0; 
        border: 1px solid #ccc; 
    }
</style>
<form method="POST" action="process_edit_order_details.php" onsubmit="return validateForm()">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($item['order_id']); ?>">
    <div class="form-group">
        <label for="order_date">受注日:</label>
        <input type="text" id="order_date" value="<?php echo htmlspecialchars($item['order_date'] ?? '未設定'); ?>" readonly>
    </div>
    <div class="form-group">
        <label for="customer_name" class="required">受注顧客名:</label>
        <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($item['customer_name'] ?? '未設定'); ?>" readonly onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="sales_rep">担当者名:</label>
        <input type="text" id="sales_rep" name="sales_rep" maxlength="255" value="<?php echo htmlspecialchars($sales_rep_name); ?>" readonly onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="memo">メモ:</label>
        <textarea id="memo" name="memo" onkeydown="preventEnterSubmit(event)"><?php echo htmlspecialchars($item['memo'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="mobile_revision">携帯見直し (税込):</label>
        <input type="number" id="mobile_revision" name="mobile_revision" step="1" min="0" value="<?php echo htmlspecialchars($item['mobile_revision'] ?? ''); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="mobile_content">携帯内容:</label>
        <textarea id="mobile_content" name="mobile_content" onkeydown="preventEnterSubmit(event)"><?php echo htmlspecialchars($item['mobile_content'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="mobile_monitor_fee_a">モニター費A (税込):</label>
        <input type="number" id="mobile_monitor_fee_a" name="mobile_monitor_fee_a" step="1" min="0" value="<?php echo htmlspecialchars($item['mobile_monitor_fee_a'] ?? ''); ?>" oninput="calculateMonitorTotal()" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="monitor_content_a">A内容:</label>
        <textarea id="monitor_content_a" name="monitor_content_a" onkeydown="preventEnterSubmit(event)"><?php echo htmlspecialchars($item['monitor_content_a'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="monitor_fee_b">モニター費B (税込):</label>
        <input type="number" id="monitor_fee_b" name="monitor_fee_b" step="1" min="0" value="<?php echo htmlspecialchars($item['monitor_fee_b'] ?? ''); ?>" oninput="calculateMonitorTotal()" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="monitor_content_b">B内容:</label>
        <textarea id="monitor_content_b" name="monitor_content_b" onkeydown="preventEnterSubmit(event)"><?php echo htmlspecialchars($item['monitor_content_b'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="monitor_fee_c">モニター費C (税込):</label>
        <input type="number" id="monitor_fee_c" name="monitor_fee_c" step="1" min="0" value="<?php echo htmlspecialchars($item['monitor_fee_c'] ?? ''); ?>" oninput="calculateMonitorTotal()" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="monitor_content_c">C内容:</label>
        <textarea id="monitor_content_c" name="monitor_content_c" onkeydown="preventEnterSubmit(event)"><?php echo htmlspecialchars($item['monitor_content_c'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="monitor_total">モニター合計 (税込):</label>
        <input type="number" id="monitor_total" name="monitor_total" step="1" min="0" value="<?php echo htmlspecialchars($item['monitor_total'] ?? ''); ?>" readonly onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="service_item_1">サービス品1 (税込):</label>
        <input type="number" id="service_item_1" name="service_item_1" step="1" min="0" value="<?php echo htmlspecialchars($item['service_item_1'] ?? ''); ?>" oninput="calculateServiceTotal()" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="service_content_1">1内容:</label>
        <textarea id="service_content_1" name="service_content_1" onkeydown="preventEnterSubmit(event)"><?php echo htmlspecialchars($item['service_content_1'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="service_item_2">サービス品2 (税込):</label>
        <input type="number" id="service_item_2" name="service_item_2" step="1" min="0" value="<?php echo htmlspecialchars($item['service_item_2'] ?? ''); ?>" oninput="calculateServiceTotal()" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="service_content_2">2内容:</label>
        <textarea id="service_content_2" name="service_content_2" onkeydown="preventEnterSubmit(event)"><?php echo htmlspecialchars($item['service_content_2'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="service_item_3">サービス品3 (税込):</label>
        <input type="number" id="service_item_3" name="service_item_3" step="1" min="0" value="<?php echo htmlspecialchars($item['service_item_3'] ?? ''); ?>" oninput="calculateServiceTotal()" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="service_content_3">3内容:</label>
        <textarea id="service_content_3" name="service_content_3" onkeydown="preventEnterSubmit(event)"><?php echo htmlspecialchars($item['service_content_3'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="service_total">サービス合計 (税込):</label>
        <input type="number" id="service_total" name="service_total" step="1" min="0" value="<?php echo htmlspecialchars($item['service_total'] ?? ''); ?>" readonly onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="others">その他:</label>
        <textarea id="others" name="others" onkeydown="preventEnterSubmit(event)"><?php echo htmlspecialchars($item['others'] ?? ''); ?></textarea>
    </div>
    <div style="margin-top: 10px;">
        <input type="submit" value="更新" style="margin-right: 10px;">
        <input type="button" value="キャンセル" onclick="window.location.href='orders_list.php';">
    </div>
</form>

<script>
function preventEnterSubmit(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
}

function calculateMonitorTotal() {
    const feeA = parseFloat(document.getElementById('mobile_monitor_fee_a').value) || 0;
    const feeB = parseFloat(document.getElementById('monitor_fee_b').value) || 0;
    const feeC = parseFloat(document.getElementById('monitor_fee_c').value) || 0;
    const total = feeA + feeB + feeC;
    document.getElementById('monitor_total').value = total;
}

function calculateServiceTotal() {
    const item1 = parseFloat(document.getElementById('service_item_1').value) || 0;
    const item2 = parseFloat(document.getElementById('service_item_2').value) || 0;
    const item3 = parseFloat(document.getElementById('service_item_3').value) || 0;
    const total = item1 + item2 + item3;
    document.getElementById('service_total').value = total;
}

function validateForm() {
    calculateMonitorTotal();
    calculateServiceTotal();
    return true;
}

window.onload = function() {
    calculateMonitorTotal();
    calculateServiceTotal();
};
</script>
</body>
</html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>