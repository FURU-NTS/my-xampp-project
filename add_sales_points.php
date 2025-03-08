<?php
include_once 'db_connection.php';
$page_title = "営業ポイント追加";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $order_id = $_GET['order_id'] ?? '';

    if (empty($order_id)) throw new Exception('受注IDが指定されていません');

    $stmt = $conn->prepare("SELECT o.*, 
                            e1.employee_id AS sales_rep_id_1, e1.full_name AS sales_rep_1,
                            e2.employee_id AS sales_rep_id_2, e2.full_name AS sales_rep_2,
                            e3.employee_id AS sales_rep_id_3, e3.full_name AS sales_rep_3,
                            e4.employee_id AS sales_rep_id_4, e4.full_name AS sales_rep_4,
                            a1.employee_id AS appointment_rep_id_1, a1.full_name AS appointment_rep_1,
                            a2.employee_id AS appointment_rep_id_2, a2.full_name AS appointment_rep_2
                            FROM orders o
                            LEFT JOIN employees e1 ON o.sales_rep_id = e1.employee_id
                            LEFT JOIN employees e2 ON o.sales_rep_id_2 = e2.employee_id
                            LEFT JOIN employees e3 ON o.sales_rep_id_3 = e3.employee_id
                            LEFT JOIN employees e4 ON o.sales_rep_id_4 = e4.employee_id
                            LEFT JOIN employees a1 ON o.appointment_rep_id_1 = a1.employee_id
                            LEFT JOIN employees a2 ON o.appointment_rep_id_2 = a2.employee_id
                            WHERE o.id = ? AND o.construction_status IN ('残あり', '完了', '回収待ち', '回収完了')");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if (!$order) throw new Exception('指定された受注が見つかりません、または対象外です');

    $related_employees = array_filter([
        ['id' => $order['sales_rep_id_1'], 'name' => $order['sales_rep_1']],
        ['id' => $order['sales_rep_id_2'], 'name' => $order['sales_rep_2']],
        ['id' => $order['sales_rep_id_3'], 'name' => $order['sales_rep_3']],
        ['id' => $order['sales_rep_id_4'], 'name' => $order['sales_rep_4']],
        ['id' => $order['appointment_rep_id_1'], 'name' => $order['appointment_rep_1']],
        ['id' => $order['appointment_rep_id_2'], 'name' => $order['appointment_rep_2']]
    ], function($emp) { return !empty($emp['id']); });
?>
<style>
    .form-container {
        display: grid;
        gap: 15px;
        max-width: 1200px;
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
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 5px;
        box-sizing: border-box;
    }
    .form-group input[readonly] {
        background-color: #f0f0f0;
        border: 1px solid #ccc;
    }
    .employee-table {
        width: 100%;
        border-collapse: collapse;
    }
    .employee-table th, .employee-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .employee-table th {
        background-color: #f2f2f2;
    }
    .button-group {
        text-align: center;
        margin-top: 10px;
    }
</style>
<form method="POST" action="process_add_sales_points.php" class="form-container">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
    <div class="form-group">
        <label for="customer_name">顧客名:</label>
        <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($order['customer_name']); ?>" readonly>
    </div>
    <div class="form-group">
        <label for="customer_type">客層:</label>
        <input type="text" id="customer_type" name="customer_type" value="<?php echo htmlspecialchars($order['customer_type']); ?>" readonly>
    </div>
    <div class="form-group">
        <label for="order_date">受注日:</label>
        <input type="text" id="order_date" name="order_date" value="<?php echo htmlspecialchars($order['order_date']); ?>" readonly>
    </div>
    <table class="employee-table">
        <tr>
            <th>担当者</th>
            <th>ポイント</th>
            <th>紹介ポイント</th>
            <th>車輛ポイント</th>
            <th>新規ボーナス（アポ無）</th>
            <th>報奨金</th>
            <th>書き換え日</th>
            <th>ポイント付与月</th>
            <th>メモ</th>
        </tr>
        <?php
        foreach ($related_employees as $index => $emp) {
            echo "<tr>";
            echo "<td><input type='hidden' name='employees[$index][employee_id]' value='" . htmlspecialchars($emp['id']) . "'>" 
                 . htmlspecialchars($emp['name']) . "</td>";
            echo "<td><input type='number' name='employees[$index][points]' min='0' required oninput='updateBonus(this, $index)' onkeydown='preventEnterSubmit(event)'></td>";
            echo "<td><input type='number' name='employees[$index][referral_points]' min='0' onkeydown='preventEnterSubmit(event)'></td>";
            echo "<td><input type='number' name='employees[$index][vehicle_points]' min='0' onkeydown='preventEnterSubmit(event)'></td>";
            echo "<td><input type='number' name='employees[$index][new_customer_bonus_no_appt]' id='new_customer_bonus_no_appt_$index' min='0' onkeydown='preventEnterSubmit(event)'></td>";
            echo "<td><input type='number' name='employees[$index][bonus]' min='0' onkeydown='preventEnterSubmit(event)'></td>";
            if ($index === 0) {
                echo "<td><input type='date' name='employees[$index][rewrite_date]' id='rewrite_date' onkeydown='preventEnterSubmit(event)'></td>";
                echo "<td><input type='text' name='employees[$index][points_granted_month]' id='points_granted_month' pattern='\d{4}-\d{2}' placeholder='YYYY-MM' onkeydown='preventEnterSubmit(event)'></td>";
            } else {
                echo "<td><input type='date' name='employees[$index][rewrite_date]' readonly class='inherit-rewrite'></td>";
                echo "<td><input type='text' name='employees[$index][points_granted_month]' readonly class='inherit-granted'></td>";
            }
            echo "<td><textarea name='employees[$index][memo]' onkeydown='preventEnterSubmit(event)'></textarea></td>";
            echo "</tr>";
        }
        ?>
    </table>
    <div class="button-group">
        <input type="submit" value="追加" style="margin-right: 10px;" onkeydown="preventEnterSubmit(event)">
        <input type="button" value="キャンセル" onclick="window.location.href='sales_points_list.php';" onkeydown="preventEnterSubmit(event)">
    </div>
</form>
<script>
function preventEnterSubmit(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const rewriteDate = document.getElementById('rewrite_date');
    const pointsGrandedMonth = document.getElementById('points_granted_month');
    const inheritRewrites = document.querySelectorAll('.inherit-rewrite');
    const inheritGranteds = document.querySelectorAll('.inherit-granted');

    rewriteDate.addEventListener('change', function() {
        inheritRewrites.forEach(input => input.value = this.value);
    });
    pointsGrandedMonth.addEventListener('change', function() {
        inheritGranteds.forEach(input => input.value = this.value);
    });
});

function updateBonus(input, index) {
    const customerType = '<?php echo $order['customer_type']; ?>';
    const points = parseInt(input.value) || 0;
    const bonusField = document.getElementById('new_customer_bonus_no_appt_' + index);
    if (customerType === '新規') {
        bonusField.value = Math.round(points * 0.2);
    } else {
        bonusField.value = '';
    }
}
</script>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>