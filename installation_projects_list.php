<?php
include_once 'db_connection.php';
ob_start();
$page_title = "新規工事プロジェクト一覧";
include_once 'header.php';

try {
    $conn = getDBConnection();

    // 検索パラメータの取得
    $search_customer_name = isset($_GET['customer_name']) ? trim($_GET['customer_name']) : '';
    $search_address = isset($_GET['address']) ? trim($_GET['address']) : '';
    $search_order_date_min = isset($_GET['order_date_min']) ? trim($_GET['order_date_min']) : '';
    $search_order_date_max = isset($_GET['order_date_max']) ? trim($_GET['order_date_max']) : '';
    $search_elapsed_days_min = isset($_GET['elapsed_days_min']) ? trim($_GET['elapsed_days_min']) : '';
    $search_elapsed_days_max = isset($_GET['elapsed_days_max']) ? trim($_GET['elapsed_days_max']) : '';
    $search_memo = isset($_GET['memo']) ? trim($_GET['memo']) : '';
    $search_status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $search_negotiation_status = isset($_GET['negotiation_status']) ? trim($_GET['negotiation_status']) : '';

    $export_csv = isset($_GET['export_csv']) && $_GET['export_csv'] === '1';

    $query = "SELECT ip.*, o.customer_name, o.negotiation_status, o.order_date, c.address 
              FROM installation_projects ip 
              LEFT JOIN orders o ON ip.order_id = o.id 
              LEFT JOIN companies c ON o.company_id = c.company_id 
              WHERE 1=1";
    $params = [];

    if (isset($_GET['search']) && $_GET['search'] === '1') {
        if (!empty($search_customer_name)) {
            $query .= " AND o.customer_name LIKE :customer_name";
            $params[':customer_name'] = "%$search_customer_name%";
        }
        if (!empty($search_address)) {
            $query .= " AND c.address LIKE :address";
            $params[':address'] = "%$search_address%";
        }
        if (!empty($search_order_date_min)) {
            $query .= " AND o.order_date >= :order_date_min";
            $params[':order_date_min'] = $search_order_date_min;
        }
        if (!empty($search_order_date_max)) {
            $query .= " AND o.order_date <= :order_date_max";
            $params[':order_date_max'] = $search_order_date_max;
        }
        if ($search_elapsed_days_min !== '' && is_numeric($search_elapsed_days_min)) {
            $query .= " AND DATEDIFF(ip.new_schedule_date, '2025-03-04') >= :elapsed_days_min";
            $params[':elapsed_days_min'] = (int)$search_elapsed_days_min;
        }
        if ($search_elapsed_days_max !== '' && is_numeric($search_elapsed_days_max)) {
            $query .= " AND DATEDIFF(ip.new_schedule_date, '2025-03-04') <= :elapsed_days_max";
            $params[':elapsed_days_max'] = (int)$search_elapsed_days_max;
        }
        if (!empty($search_memo)) {
            $query .= " AND ip.memo LIKE :memo";
            $params[':memo'] = "%$search_memo%";
        }
        if (!empty($search_status)) {
            $query .= " AND ip.status = :status";
            $params[':status'] = $search_status;
        }
        if (!empty($search_negotiation_status)) {
            $query .= " AND o.negotiation_status = :negotiation_status";
            $params[':negotiation_status'] = $search_negotiation_status;
        }
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $statusTranslations = [
        'planning' => '段取り中',
        'in_progress' => '進行中',
        'completed' => '完了'
    ];

    function calculateElapsedDays($new_schedule_date, $status) {
        $current_date = new DateTime('2025-03-04');
        $schedule_date = new DateTime($new_schedule_date);
        $interval = $schedule_date->diff($current_date);
        $days = $interval->days;

        if ($status === 'completed') {
            return '完了';
        } elseif ($schedule_date > $current_date) {
            return $days . '日後';
        } else {
            return $days . '日';
        }
    }

    foreach ($projects as &$project) {
        $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed FROM installation_tasks WHERE project_id = ?");
        $stmt->execute([$project['project_id']]);
        $task_status = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($task_status['total'] > 0 && $task_status['total'] == $task_status['completed'] && $project['status'] !== 'completed') {
            $stmt = $conn->prepare("UPDATE installation_projects SET status = 'completed' WHERE project_id = ?");
            $stmt->execute([$project['project_id']]);
            $project['status'] = 'completed';
        } elseif ($task_status['completed'] < $task_status['total'] && $project['status'] === 'completed') {
            $stmt = $conn->prepare("UPDATE installation_projects SET status = 'in_progress' WHERE project_id = ?");
            $stmt->execute([$project['project_id']]);
            $project['status'] = 'in_progress';
        }
    }
    unset($project);

    if ($export_csv) {
        ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="installation_projects_' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['ID', '顧客名', '住所', '受注日', '新規予定日', '経過日数', 'メモ', 'ステータス', '商談ステータス']);
        foreach ($projects as $item) {
            fputcsv($output, [
                $item['project_id'],
                $item['customer_name'] ?? '未指定',
                $item['address'] ?? '未指定',
                $item['order_date'],
                $item['new_schedule_date'],
                calculateElapsedDays($item['new_schedule_date'], $item['status']),
                $item['memo'] ?? '',
                $statusTranslations[$item['status']] ?? $item['status'],
                $item['negotiation_status'] ?? '未指定'
            ]);
        }
        fclose($output);
        exit;
    }

    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<form method="GET" action="installation_projects_list.php" class="search-form">
    <input type="hidden" name="search" value="1">
    <div class="form-group">
        <label for="customer_name">顧客名:</label>
        <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($search_customer_name); ?>">
    </div>
    <div class="form-group">
        <label for="address">住所:</label>
        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($search_address); ?>">
    </div>
    <div class="form-group">
        <label>受注日:</label>
        <div style="display: flex; gap: 5px;">
            <input type="date" name="order_date_min" value="<?php echo htmlspecialchars($search_order_date_min); ?>">
            <span>-</span>
            <input type="date" name="order_date_max" value="<?php echo htmlspecialchars($search_order_date_max); ?>">
        </div>
    </div>
    <div class="form-group">
        <label>経過日数:</label>
        <div style="display: flex; gap: 5px;">
            <input type="number" name="elapsed_days_min" value="<?php echo htmlspecialchars($search_elapsed_days_min); ?>" placeholder="最小" min="0">
            <span>-</span>
            <input type="number" name="elapsed_days_max" value="<?php echo htmlspecialchars($search_elapsed_days_max); ?>" placeholder="最大" min="0">
        </div>
    </div>
    <div class="form-group">
        <label for="memo">メモ:</label>
        <input type="text" id="memo" name="memo" value="<?php echo htmlspecialchars($search_memo); ?>">
    </div>
    <div class="form-group">
        <label for="status">ステータス:</label>
        <select id="status" name="status">
            <option value="">すべて</option>
            <option value="planning" <?php echo $search_status === 'planning' ? 'selected' : ''; ?>>段取り中</option>
            <option value="in_progress" <?php echo $search_status === 'in_progress' ? 'selected' : ''; ?>>進行中</option>
            <option value="completed" <?php echo $search_status === 'completed' ? 'selected' : ''; ?>>完了</option>
        </select>
    </div>
    <div class="form-group">
        <label for="negotiation_status">商談ステータス:</label>
        <select id="negotiation_status" name="negotiation_status">
            <option value="">すべて</option>
            <option value="進行中" <?php echo $search_negotiation_status === '進行中' ? 'selected' : ''; ?>>進行中</option>
            <option value="与信怪しい" <?php echo $search_negotiation_status === '与信怪しい' ? 'selected' : ''; ?>>与信怪しい</option>
            <option value="工事前再説" <?php echo $search_negotiation_status === '工事前再説' ? 'selected' : ''; ?>>工事前再説</option>
            <option value="工事後再説" <?php echo $search_negotiation_status === '工事後再説' ? 'selected' : ''; ?>>工事後再説</option>
            <option value="工事前キャンセル" <?php echo $search_negotiation_status === '工事前キャンセル' ? 'selected' : ''; ?>>工事前キャンセル</option>
            <option value="工事後キャンセル" <?php echo $search_negotiation_status === '工事後キャンセル' ? 'selected' : ''; ?>>工事後キャンセル</option>
            <option value="書換完了" <?php echo $search_negotiation_status === '書換完了' ? 'selected' : ''; ?>>書換完了</option>
            <option value="承認完了" <?php echo $search_negotiation_status === '承認完了' ? 'selected' : ''; ?>>承認完了</option>
            <option value="承認後キャンセル" <?php echo $search_negotiation_status === '承認後キャンセル' ? 'selected' : ''; ?>>承認後キャンセル</option>
        </select>
    </div>
    <div>
        <input type="submit" value="検索">
        <input type="button" value="全リスト表示" onclick="window.location.href='installation_projects_list.php';">
        <input type="button" value="CSVダウンロード" onclick="exportCSV();">
    </div>
</form>
<a href="add_installation_projects.php">新規工事プロジェクト追加</a>
<table>
    <tr>
        <th>ID</th>
        <th>顧客名</th>
        <th>住所</th>
        <th>受注日</th>
        <th>新規予定日</th>
        <th>経過日数</th>
        <th>メモ</th>
        <th>ステータス</th>
        <th>商談ステータス</th>
        <th>アクション</th>
    </tr>
    <?php
    if ($projects) {
        foreach ($projects as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['project_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['customer_name'] ?? '未指定') . "</td>";
            echo "<td>" . htmlspecialchars($row['address'] ?? '未指定') . "</td>";
            echo "<td>" . htmlspecialchars($row['order_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['new_schedule_date']) . "</td>";
            echo "<td>" . htmlspecialchars(calculateElapsedDays($row['new_schedule_date'], $row['status'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['memo'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($statusTranslations[$row['status']] ?? $row['status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['negotiation_status'] ?? '未指定') . "</td>";
            echo "<td><a href='edit_installation_projects.php?project_id=" . htmlspecialchars($row['project_id']) . "'>編集</a> | <a href='delete_installation_projects.php?project_id=" . htmlspecialchars($row['project_id']) . "'>削除</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='10'>データが見つかりません。</td></tr>";
    }
    ?>
</table>
<a href="add_installation_projects.php">新規工事プロジェクト追加</a>
<script>
function exportCSV() {
    var form = document.querySelector('form');
    var url = new URL(form.action);
    var params = new URLSearchParams(new FormData(form));
    params.set('export_csv', '1');
    url.search = params.toString();
    window.location.href = url.toString();
}
</script>
</body></html>
<?php
} catch (Exception $e) {
    ob_end_clean();
    error_log("Error in installation_projects_list.php: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました: " . htmlspecialchars($e->getMessage()) . "</p>";
}
ob_end_flush();
?>