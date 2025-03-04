<?php
include_once 'db_connection.php';
$page_title = "新規工事タスク編集";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $task_id = $_GET['task_id'] ?? '';
    if (empty($task_id)) throw new Exception('タスクIDが指定されていません');

    $stmt = $conn->prepare("SELECT it.*, ip.new_schedule_date, o.customer_name 
                            FROM installation_tasks it 
                            LEFT JOIN installation_projects ip ON it.project_id = ip.project_id 
                            LEFT JOIN orders o ON ip.order_id = o.id 
                            WHERE it.task_id = ?");
    $stmt->execute([$task_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) throw new Exception('タスクが見つかりません');

    // 担当者一覧
    $stmt = $conn->query("SELECT employee_id, full_name, department FROM employees ORDER BY department, full_name");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 部門の一覧
    $stmt = $conn->query("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL ORDER BY department");
    $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // タスク名の選択肢
    $task_options = [
        '見積機器納品', '見積機器設定', 'PC設定', '配線整理', 'Sメッシュ設置', 
        'Sラック設置', 'Sカメラ設置', 'Sパソコン設置', 'VPN構築', '他サービス品納品', 
        '機器撤去', '機器預かり', '書類預かり', '各種コンサル', 'HP.SNSサポート', 'その他'
    ];

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrf_token = $_SESSION['csrf_token'];

    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
    .form-group input, .form-group select { width: 100%; max-width: 300px; padding: 5px; box-sizing: border-box; }
    .required::after { content: " *"; color: red; }
    .error { color: red; }
    .success { color: green; }
    .readonly { background-color: #f0f0f0; border: 1px solid #ccc; }
</style>
<form method="POST" action="process_edit_installation_tasks.php">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($item['task_id']); ?>">
    <div class="form-group">
        <label for="project_id" class="required">プロジェクト:</label>
        <input type="text" id="project_id" class="readonly" value="<?php echo htmlspecialchars($item['customer_name'] . ' - ' . $item['new_schedule_date']); ?>" readonly>
        <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($item['project_id']); ?>">
    </div>
    <div class="form-group">
        <label for="task_name" class="required">タスク名:</label>
        <select id="task_name" name="task_name" required onkeydown="preventEnterSubmit(event)">
            <?php foreach ($task_options as $option) {
                $selected = $option == $item['task_name'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($option) . "' $selected>" . htmlspecialchars($option) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="status" class="required">ステータス:</label>
        <select id="status" name="status" required onkeydown="preventEnterSubmit(event)">
            <option value="not_started" <?php echo $item['status'] === 'not_started' ? 'selected' : ''; ?>>未開始</option>
            <option value="in_progress" <?php echo $item['status'] === 'in_progress' ? 'selected' : ''; ?>>進行中</option>
            <option value="completed" <?php echo $item['status'] === 'completed' ? 'selected' : ''; ?>>完了</option>
        </select>
    </div>
    <div class="form-group">
        <label for="start_date">開始日:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($item['start_date'] ?? ''); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="end_date">終了日:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($item['end_date'] ?? ''); ?>" onkeydown="preventEnterSubmit(event)">
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
                $selected = $emp['employee_id'] == $item['employee_id_1'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' data-department='" . htmlspecialchars($emp['department']) . "' $selected>" . htmlspecialchars($emp['full_name']) . "</option>";
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
                $selected = $emp['employee_id'] == $item['employee_id_2'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' data-department='" . htmlspecialchars($emp['department']) . "' $selected>" . htmlspecialchars($emp['full_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="memo">メモ:</label>
        <input type="text" id="memo" name="memo" value="<?php echo htmlspecialchars($item['memo'] ?? ''); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div>
        <input type="submit" value="更新">
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
    // 現在の選択値を保持
    var currentValue = select.value;
    select.selectedIndex = 0;
    for (var i = 0; i < options.length; i++) {
        if (options[i].value === currentValue && options[i].style.display !== 'none') {
            select.selectedIndex = i;
            break;
        }
    }
}
</script>
</body>
</html>
<?php
} catch (Exception $e) {
    error_log("Error in edit_installation_tasks.php: " . $e->getMessage());
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>