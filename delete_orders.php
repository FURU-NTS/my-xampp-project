<?php
include_once 'db_connection.php';
$page_title = "受注削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $id = $_GET['id'] ?? '';
    if (empty($id)) throw new Exception('受注IDが指定されていません');

    $stmt = $conn->prepare("SELECT o.*, 
                            e1.full_name AS sales_rep_1, e1.department AS dept_1,
                            e2.full_name AS sales_rep_2, e2.department AS dept_2,
                            e3.full_name AS sales_rep_3, e3.department AS dept_3,
                            e4.full_name AS sales_rep_4, e4.department AS dept_4,
                            a1.full_name AS appointment_rep_1, a1.department AS appt_dept_1,
                            a2.full_name AS appointment_rep_2, a2.department AS appt_dept_2,
                            r.full_name AS rewriting_person, r.department AS rewrite_dept
                            FROM orders o 
                            LEFT JOIN employees e1 ON o.sales_rep_id = e1.employee_id 
                            LEFT JOIN employees e2 ON o.sales_rep_id_2 = e2.employee_id 
                            LEFT JOIN employees e3 ON o.sales_rep_id_3 = e3.employee_id 
                            LEFT JOIN employees e4 ON o.sales_rep_id_4 = e4.employee_id 
                            LEFT JOIN employees a1 ON o.appointment_rep_id_1 = a1.employee_id 
                            LEFT JOIN employees a2 ON o.appointment_rep_id_2 = a2.employee_id 
                            LEFT JOIN employees r ON o.rewriting_person_id = r.employee_id 
                            WHERE o.id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('受注が見つかりません');
?>
<style>
    .delete-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        max-width: 1000px;
        margin: 20px auto;
    }
    .delete-item {
        margin: 5px 0;
    }
    .delete-item label {
        font-weight: bold;
    }
    .button-group {
        grid-column: span 3;
        text-align: center;
        margin-top: 10px;
    }
    .button-group input, .button-group a {
        margin: 0 10px;
    }
    .error {
        color: red;
    }
</style>
<p>以下の受注を削除してもよろしいですか？</p>
<div class="delete-container">
    <div class="delete-item">
        <label>顧客名:</label> <?php echo htmlspecialchars($item['customer_name'] ?? '未設定'); ?>
    </div>
    <div class="delete-item">
        <label>客層:</label> <?php echo htmlspecialchars($item['customer_type'] ?? '未設定'); ?>
    </div>
    <div class="delete-item">
        <label>受注日:</label> <?php echo htmlspecialchars($item['order_date'] ?? '未設定'); ?>
    </div>
    <div class="delete-item">
        <label>月額 (税抜):</label> <?php echo number_format($item['monthly_fee'], 0); ?> 円
    </div>
    <div class="delete-item">
        <label>回数:</label> <?php echo htmlspecialchars($item['total_payments'] ?? '未設定'); ?>
    </div>
    <div class="delete-item">
        <label>商談ステータス:</label> <?php echo htmlspecialchars($item['negotiation_status'] ?? '未設定'); ?>
    </div>
    <div class="delete-item">
        <label>工事ステータス:</label> <?php echo htmlspecialchars($item['construction_status'] ?? '未設定'); ?>
    </div>
    <div class="delete-item">
        <label>与信ステータス:</label> <?php echo htmlspecialchars($item['credit_status'] ?? '未設定'); ?>
    </div>
    <div class="delete-item">
        <label>書類ステータス:</label> <?php echo htmlspecialchars($item['document_status'] ?? '未設定'); ?>
    </div>
    <div class="delete-item">
        <label>書換ステータス:</label> <?php echo htmlspecialchars($item['rewrite_status'] ?? '未設定'); ?>
    </div>
    <div class="delete-item">
        <label>印鑑証明ステータス:</label> <?php echo htmlspecialchars($item['seal_certificate_status'] ?? '未設定'); ?>
    </div>
    <div class="delete-item">
        <label>発送ステータス:</label> <?php echo htmlspecialchars($item['shipping_status'] ?? '未設定'); ?>
    </div>
    <div class="delete-item">
        <label>メモ:</label> <?php echo htmlspecialchars($item['memo'] ?? '未設定'); ?>
    </div>
    <div class="delete-item">
        <label>担当者1:</label> <?php echo htmlspecialchars(($item['dept_1'] ?? '') . "/" . ($item['sales_rep_1'] ?? '未設定')); ?>
    </div>
    <div class="delete-item">
        <label>担当者2:</label> <?php echo htmlspecialchars(($item['dept_2'] ?? '') . "/" . ($item['sales_rep_2'] ?? '未設定')); ?>
    </div>
    <div class="delete-item">
        <label>担当者3:</label> <?php echo htmlspecialchars(($item['dept_3'] ?? '') . "/" . ($item['sales_rep_3'] ?? '未設定')); ?>
    </div>
    <div class="delete-item">
        <label>担当者4:</label> <?php echo htmlspecialchars(($item['dept_4'] ?? '') . "/" . ($item['sales_rep_4'] ?? '未設定')); ?>
    </div>
    <div class="delete-item">
        <label>アポイント者1:</label> <?php echo htmlspecialchars(($item['appt_dept_1'] ?? '') . "/" . ($item['appointment_rep_1'] ?? '未設定')); ?>
    </div>
    <div class="delete-item">
        <label>アポイント者2:</label> <?php echo htmlspecialchars(($item['appt_dept_2'] ?? '') . "/" . ($item['appointment_rep_2'] ?? '未設定')); ?>
    </div>
    <div class="delete-item">
        <label>書換担当:</label> <?php echo htmlspecialchars(($item['rewrite_dept'] ?? '') . "/" . ($item['rewriting_person'] ?? '未設定')); ?>
    </div>
    <div class="button-group">
        <form method="POST" action="process_delete_orders.php">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
            <input type="submit" value="削除">
            <a href="orders_list.php">キャンセル</a>
        </form>
    </div>
</div>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>