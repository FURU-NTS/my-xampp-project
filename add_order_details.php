<?php
include_once 'db_connection.php';
$page_title = "受注詳細追加";
include_once 'header.php';

$order_id = $_GET['order_id'] ?? '';
$customer_name = $_GET['customer_name'] ?? '';
$sales_rep_id = $_GET['sales_rep_id'] ?? '';

try {
    $conn = getDBConnection();

    // order_id が指定されている場合、受注日と顧客名を取得
    $order_date = '';
    if ($order_id) {
        $stmt = $conn->prepare("SELECT order_date, customer_name FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($order) {
            $order_date = $order['order_date'] ?? '';
            $customer_name = $order['customer_name'] ?? $customer_name; // GETパラメータより優先
        } else {
            throw new Exception('指定された受注が見つかりません');
        }
    } else {
        throw new Exception('受注IDが指定されていません');
    }

    // 担当者名を取得
    $sales_rep_name = '';
    if ($sales_rep_id) {
        $stmt = $conn->prepare("SELECT full_name FROM employees WHERE employee_id = ?");
        $stmt->execute([$sales_rep_id]);
        $sales_rep = $stmt->fetch(PDO::FETCH_ASSOC);
        $sales_rep_name = $sales_rep ? $sales_rep['full_name'] : '';
    }
?>
<form method="POST" action="process_add_order_details.php" onsubmit="return validateForm()">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
    <div class="form-group">
        <label for="order_date" class="required">受注日:</label>
        <input type="text" id="order_date" name="order_date" value="<?php echo htmlspecialchars($order_date); ?>" readonly onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="customer_name" class="required">顧客名:</label>
        <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($customer_name); ?>" readonly onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="sales_rep">担当者名:</label>
        <input type="text" id="sales_rep" name="sales_rep" maxlength="255" value="<?php echo htmlspecialchars($sales_rep_name); ?>" readonly onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="mobile_revision">携帯見直し (税込):</label>
        <input type="number" id="mobile_revision" name="mobile_revision" step="1" min="0" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="mobile_content">携帯内容:</label>
        <textarea id="mobile_content" name="mobile_content" onkeydown="preventEnterSubmit(event)"></textarea>
    </div>
    <div class="form-group">
        <label for="mobile_monitor_fee_a">モニター費A (税込):</label>
        <input type="number" id="mobile_monitor_fee_a" name="mobile_monitor_fee_a" step="1" min="0" oninput="calculateMonitorTotal()" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="monitor_content_a">A内容:</label>
        <textarea id="monitor_content_a" name="monitor_content_a" onkeydown="preventEnterSubmit(event)"></textarea>
    </div>
    <div class="form-group">
        <label for="monitor_fee_b">モニター費B (税込):</label>
        <input type="number" id="monitor_fee_b" name="monitor_fee_b" step="1" min="0" oninput="calculateMonitorTotal()" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="monitor_content_b">B内容:</label>
        <textarea id="monitor_content_b" name="monitor_content_b" onkeydown="preventEnterSubmit(event)"></textarea>
    </div>
    <div class="form-group">
        <label for="monitor_fee_c">モニター費C (税込):</label>
        <input type="number" id="monitor_fee_c" name="monitor_fee_c" step="1" min="0" oninput="calculateMonitorTotal()" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="monitor_content_c">C内容:</label>
        <textarea id="monitor_content_c" name="monitor_content_c" onkeydown="preventEnterSubmit(event)"></textarea>
    </div>
    <div class="form-group">
        <label for="monitor_total">モニター合計 (税込):</label>
        <input type="number" id="monitor_total" name="monitor_total" step="1" min="0" readonly onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="service_item_1">サービス品1 (税込):</label>
        <input type="number" id="service_item_1" name="service_item_1" step="1" min="0" oninput="calculateServiceTotal()" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="service_content_1">1内容:</label>
        <textarea id="service_content_1" name="service_content_1" onkeydown="preventEnterSubmit(event)"></textarea>
    </div>
    <div class="form-group">
        <label for="service_item_2">サービス品2 (税込):</label>
        <input type="number" id="service_item_2" name="service_item_2" step="1" min="0" oninput="calculateServiceTotal()" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="service_content_2">2内容:</label>
        <textarea id="service_content_2" name="service_content_2" onkeydown="preventEnterSubmit(event)"></textarea>
    </div>
    <div class="form-group">
        <label for="service_item_3">サービス品3 (税込):</label>
        <input type="number" id="service_item_3" name="service_item_3" step="1" min="0" oninput="calculateServiceTotal()" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="service_content_3">3内容:</label>
        <textarea id="service_content_3" name="service_content_3" onkeydown="preventEnterSubmit(event)"></textarea>
    </div>
    <div class="form-group">
        <label for="service_total">サービス合計 (税込):</label>
        <input type="number" id="service_total" name="service_total" step="1" min="0" readonly onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="others">その他:</label>
        <textarea id="others" name="others" onkeydown="preventEnterSubmit(event)"></textarea>
    </div>
    <div style="margin-top: 10px;">
        <input type="submit" value="追加" style="margin-right: 10px;">
        <input type="button" value="キャンセル" onclick="window.location.href='orders_list.php';">
    </div>
</form>

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
        height: 60px; /* 高さを100pxから60pxに抑える */
    }
    .form-group input[readonly] { 
        background-color: #f0f0f0; 
        border: 1px solid #ccc; 
    }
</style>

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