<?php
include_once 'db_connection.php';
$page_title = "工事タスク追加";
include_once 'header.php';

try {
    $conn = getDBConnection();

    // プロジェクト一覧を取得
    $stmt = $conn->query("SELECT ip.project_id, ip.new_schedule_date, o.customer_name 
                          FROM installation_projects ip 
                          LEFT JOIN orders o ON ip.order_id = o.id 
                          WHERE o.negotiation_status IN ('進行中', '与信怪しい', '書換完了')");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 担当者一覧を取得（全データ）
    $stmt = $conn->query("SELECT employee_id, full_name, department FROM employees ORDER BY department, full_name");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 部門の一覧を取得（重複なし）
    $stmt = $conn->query("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL ORDER BY department");
    $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // タスク名の選択肢
    $task_options = [
        '見積機器納品', '見積機器設定', 'PC設定', '配線整理', 'Sメッシュ設置', 
        'Sラック設置', 'Sカメラ設置', 'Sパソコン設置', 'VPN構築', '他サービス品納品', 
        '機器撤去', '機器預かり', '書類預かり'
    ];
?>
<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
    .form-group input, .form-group select { width: 100%; max-width: 300px; padding: 5px; box-sizing: border-box; }
    .required::after { content: " *"; color: red; }
    .error { color: red; }
    .success { color: green; }
</style>
<form method="POST" action="process_add_installation_tasks.php">
    <div class="form-group">
        <label for="project_id" class="required">プロジェクト:</label>
        <select id="project_id" name="project_id" required onkeydown="preventEnterSubmit(event)">
            <option value="">選択してください</option>
            <?php foreach ($projects as $project) {
                echo "<option value='" . htmlspecialchars($project['project_id']) . "'>" . htmlspecialchars($project['customer_name']) . " - " . htmlspecialchars($project['new_schedule_date']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="task_names" class="required">タスク名:</label>
        <select id="task_names" name="task_names[]" multiple required size="5" onkeydown="preventEnterSubmit(event)">
            <?php foreach ($task_options as $option) {
                echo "<option value='" . htmlspecialchars($option) . "'>" . htmlspecialchars($option) . "</option>";
            } ?>
        </select>
        <small>Ctrlキー（MacはCommandキー）を押しながら複数選択可能です</small>
    </div>
    <div class="form-group">
        <label for="department_1">部門１:</label>
        <select id="department_1" name="department_1" onchange="filterEmployees('employee_id_1', this.value)" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php foreach ($departments as $dept) {
                echo "<option value='" . htmlspecialchars($dept) . "'>" . htmlspecialchars($dept) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="employee_id_1">担当者１:</label>
        <select id="employee_id_1" name="employee_id_1" onkeydown="preventEnterSubmit(event)">
            <option value="">選択なし</option>
            <?php foreach ($employees as $emp) {
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' data-department='" . htmlspecialchars($emp['department']) . "'>" . htmlspecialchars($emp['full_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="department_2">部門２:</label>
        <select id="department_2" name="department_2" onchange="filterEmployees('employee_id_2', this.value)" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php foreach ($departments as $dept) {
                echo "<option value='" . htmlspecialchars($dept) . "'>" . htmlspecialchars($dept) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="employee_id_2">担当者２:</label>
        <select id="employee_id_2" name="employee_id_2" onkeydown="preventEnterSubmit(event)">
            <option value="">選択なし</option>
            <?php foreach ($employees as $emp) {
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' data-department='" . htmlspecialchars($emp['department']) . "'>" . htmlspecialchars($emp['full_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="memo">メモ:</label>
        <input type="text" id="memo" name="memo" onkeydown="preventEnterSubmit(event)">
    </div>
    <div>
        <input type="submit" value="追加">
        <input type="button" value="キャンセル" onclick="window.location.href='installation_tasks_list.php';">
    </div>
</form>

<script>
function preventEnterSubmit(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
}

function filterEmployees(selectId, department) {
    var select = document.getElementById(selectId);
    var options = select.getElementsByTagName('option');
    for (var i = 0; i < options.length; i++) {
        var optionDept = options[i].getAttribute('data-department');
        if (department === '' || optionDept === department) {
            options[i].style.display = '';
        } else {
            options[i].style.display = 'none';
        }
    }
    select.selectedIndex = 0; // リセット
}
</script>
</body>
</html>
<?php
} catch (Exception $e) {
    error_log("Error in add_installation_tasks.php: " . $e->getMessage());
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>