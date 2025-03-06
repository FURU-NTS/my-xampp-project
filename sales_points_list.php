<?php
include_once 'db_connection.php';
$page_title = "営業ポイントリスト";
include_once 'header.php';

try {
    $conn = getDBConnection();

    $search_customer = $_GET['customer'] ?? '';
    $search_start_date = $_GET['start_date'] ?? '';
    $search_end_date = $_GET['end_date'] ?? '';
    $search_employee = $_GET['employee'] ?? '';
    $search_construction_status = $_GET['construction_status'] ?? [];
    $search_negotiation_status = $_GET['negotiation_status'] ?? [];
    $search_rewrite_status = $_GET['rewrite_status'] ?? [];
    $search_rewrite_start_date = $_GET['rewrite_start_date'] ?? '';
    $search_rewrite_end_date = $_GET['rewrite_end_date'] ?? '';
    $search_granted_start = $_GET['points_granted_month_start'] ?? '';
    $search_granted_end = $_GET['points_granted_month_end'] ?? '';
    $search_changed_start = $_GET['points_changed_month_start'] ?? '';
    $search_changed_end = $_GET['points_changed_month_end'] ?? '';

    // 基本クエリ
    $base_query = "SELECT sp.point_id, sp.order_id, sp.employee_id, sp.rewrite_date, sp.points, sp.removal_points, 
                          sp.points_revision, sp.points_granted_month, sp.points_changed_month, sp.memo,
                          o.order_date, o.customer_name, o.construction_status, o.negotiation_status, 
                          o.rewrite_status, e.full_name AS employee_name, e.department,
                          it.start_date
                   FROM sales_points sp
                   JOIN orders o ON sp.order_id = o.id
                   JOIN employees e ON sp.employee_id = e.employee_id
                   LEFT JOIN installation_projects ip ON o.id = ip.order_id
                   LEFT JOIN installation_tasks it ON ip.project_id = it.project_id
                   WHERE o.construction_status IN ('残あり', '完了', '回収待ち', '回収完了')";

    // 共通条件とパラメータ
    $conditions = [];
    $params = [];
    if ($search_customer) {
        $conditions[] = "o.customer_name LIKE ?";
        $params[] = "%$search_customer%";
    }
    if ($search_employee) {
        $conditions[] = "sp.employee_id = ?";
        $params[] = $search_employee;
    }
    if (!empty($search_construction_status)) {
        $placeholders = implode(',', array_fill(0, count($search_construction_status), '?'));
        $conditions[] = "o.construction_status IN ($placeholders)";
        $params = array_merge($params, $search_construction_status);
    }
    if (!empty($search_negotiation_status)) {
        $placeholders = implode(',', array_fill(0, count($search_negotiation_status), '?'));
        $conditions[] = "o.negotiation_status IN ($placeholders)";
        $params = array_merge($params, $search_negotiation_status);
    }
    if (!empty($search_rewrite_status)) {
        $placeholders = implode(',', array_fill(0, count($search_rewrite_status), '?'));
        $conditions[] = "o.rewrite_status IN ($placeholders)";
        $params = array_merge($params, $search_rewrite_status);
    }
    if ($search_rewrite_start_date) {
        $conditions[] = "sp.rewrite_date >= ?";
        $params[] = $search_rewrite_start_date;
    }
    if ($search_rewrite_end_date) {
        $conditions[] = "sp.rewrite_date <= ?";
        $params[] = $search_rewrite_end_date;
    }

    // ポイント付与月範囲のクエリ
    $query_granted = $base_query;
    $params_granted = $params;
    $granted_conditions = $conditions;
    if ($search_granted_start) {
        $granted_conditions[] = "sp.points_granted_month >= ?";
        $params_granted[] = $search_granted_start;
    }
    if ($search_granted_end) {
        $granted_conditions[] = "sp.points_granted_month <= ?";
        $params_granted[] = $search_granted_end;
    }
    if (!empty($granted_conditions)) {
        $query_granted .= " AND " . implode(" AND ", $granted_conditions);
    }

    // ポイント変更月範囲のクエリ
    $query_changed = $base_query;
    $params_changed = $params;
    $changed_conditions = $conditions;
    if ($search_changed_start) {
        $changed_conditions[] = "sp.points_changed_month >= ?";
        $params_changed[] = $search_changed_start;
    }
    if ($search_changed_end) {
        $changed_conditions[] = "sp.points_changed_month <= ?";
        $params_changed[] = $search_changed_end;
    }
    if (!empty($changed_conditions)) {
        $query_changed .= " AND " . implode(" AND ", $changed_conditions);
    }

    // 受注日範囲のクエリ
    $query_order = $base_query;
    $params_order = $params;
    $order_conditions = $conditions;
    if ($search_start_date) {
        $order_conditions[] = "o.order_date >= ?";
        $params_order[] = $search_start_date;
    }
    if ($search_end_date) {
        $order_conditions[] = "o.order_date <= ?";
        $params_order[] = $search_end_date;
    }
    if (!empty($order_conditions)) {
        $query_order .= " AND " . implode(" AND ", $order_conditions);
    }

    // クエリとパラメータを準備
    $final_query = $base_query;
    $final_params = $params;
    $has_granted = $search_granted_start || $search_granted_end;
    $has_changed = $search_changed_start || $search_changed_end;
    $has_order = $search_start_date || $search_end_date;

    if ($has_granted || $has_changed || $has_order) {
        $final_query = "";
        $final_params = [];
        if ($has_granted) {
            $final_query .= "($query_granted)";
            $final_params = array_merge($final_params, $params_granted);
        }
        if ($has_changed) {
            if ($final_query) $final_query .= " UNION ";
            $final_query .= "($query_changed)";
            $final_params = array_merge($final_params, $params_changed);
        }
        if ($has_order) {
            if ($final_query) $final_query .= " UNION ";
            $final_query .= "($query_order)";
            $final_params = array_merge($final_params, $params_order);
        }
    } elseif (!empty($conditions)) {
        $final_query .= " AND " . implode(" AND ", $conditions);
    }

    $stmt = $conn->prepare($final_query);
    $stmt->execute($final_params);
    $sales_points = $stmt->fetchAll();

    // 担当者ごとのポイント集計
    $points_summary = [];
    foreach ($sales_points as $point) {
        $emp_name = $point['employee_name'];
        if (!isset($points_summary[$emp_name])) {
            $points_summary[$emp_name] = [
                'points' => 0,
                'removal_points' => 0,
                'points_revision' => 0,
                'points_granted_month' => [],
                'points_changed_month' => []
            ];
        }
        $points_summary[$emp_name]['points'] += $point['points'] ?? 0;
        $points_summary[$emp_name]['removal_points'] += $point['removal_points'] ?? 0;
        $points_summary[$emp_name]['points_revision'] += $point['points_revision'] ?? 0;
        if ($point['points_granted_month']) {
            $points_summary[$emp_name]['points_granted_month'][] = $point['points_granted_month'];
        }
        if ($point['points_changed_month']) {
            $points_summary[$emp_name]['points_changed_month'][] = $point['points_changed_month'];
        }
    }

    // 従業員リストを取得
    $employees_stmt = $conn->query("SELECT employee_id, full_name, department FROM employees ORDER BY department, full_name");
    $employees = $employees_stmt->fetchAll();
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <style>
        .search-form {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            padding: 5px;
        }
        .form-group select[multiple] {
            height: 100px;
        }
        .search-button {
            grid-column: span 4;
            text-align: center;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
            white-space: normal;
            overflow: hidden; 
            text-overflow: ellipsis; 
        }
        th { 
            background-color: #f2f2f2; 
            font-size: 0.9em; 
        }
        th:nth-child(1), td:nth-child(1) { width: 10%; } /* 担当者 */
        th:nth-child(2), td:nth-child(2) { width: 10%; } /* 受注日 */
        th:nth-child(3), td:nth-child(3) { width: 12%; } /* 顧客名 */
        th:nth-child(4), td:nth-child(4) { width: 8%; } /* 開始日 */
        th:nth-child(5), td:nth-child(5) { width: 8%; } /* 工事ステータス */
        th:nth-child(6), td:nth-child(6) { width: 8%; } /* 商談ステータス */
        th:nth-child(7), td:nth-child(7) { width: 8%; } /* 書換ステータス */
        th:nth-child(8), td:nth-child(8) { width: 8%; } /* 書き換え日 */
        th:nth-child(9), td:nth-child(9) { width: 5%; } /* ポイント */
        th:nth-child(10), td:nth-child(10) { width: 5%; } /* 撤去ポイント */
        th:nth-child(11), td:nth-child(11) { width: 5%; } /* ポイント修正 */
        th:nth-child(12), td:nth-child(12) { width: 5%; } /* ポイント付与月 */
        th:nth-child(13), td:nth-child(13) { width: 5%; } /* ポイント変更月 */
        th:nth-child(14), td:nth-child(14) { width: 10%; } /* メモ */
        th:nth-child(15), td:nth-child(15) { width: 5%; } /* 編集 */
        th:nth-child(16), td:nth-child(16) { width: 5%; } /* 削除 */
        .summary-table {
            width: 80%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #ddd;
            padding: 8px;
            white-space: normal;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<form method="GET" action="sales_points_list.php" class="search-form no-print">
    <div class="form-group">
        <label for="customer">顧客名:</label>
        <input type="text" id="customer" name="customer" value="<?php echo htmlspecialchars($search_customer); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="start_date">受注日（開始）:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($search_start_date); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="end_date">受注日（終了）:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($search_end_date); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="employee">担当者:</label>
        <select id="employee" name="employee" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php foreach ($employees as $emp) {
                $selected = $search_employee == $emp['employee_id'] ? 'selected' : '';
                $display_name = htmlspecialchars($emp['department'] . "/" . $emp['full_name']);
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>$display_name</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="construction_status">工事ステータス:</label>
        <select id="construction_status" name="construction_status[]" multiple onkeydown="preventEnterSubmit(event)">
            <option value="残あり" <?php echo in_array('残あり', $search_construction_status) ? 'selected' : ''; ?>>残あり</option>
            <option value="完了" <?php echo in_array('完了', $search_construction_status) ? 'selected' : ''; ?>>完了</option>
            <option value="回収待ち" <?php echo in_array('回収待ち', $search_construction_status) ? 'selected' : ''; ?>>回収待ち</option>
            <option value="回収完了" <?php echo in_array('回収完了', $search_construction_status) ? 'selected' : ''; ?>>回収完了</option>
        </select>
    </div>
    <div class="form-group">
        <label for="negotiation_status">商談ステータス:</label>
        <select id="negotiation_status" name="negotiation_status[]" multiple onkeydown="preventEnterSubmit(event)">
            <option value="未設定" <?php echo in_array('未設定', $search_negotiation_status) ? 'selected' : ''; ?>>未設定</option>
            <option value="進行中" <?php echo in_array('進行中', $search_negotiation_status) ? 'selected' : ''; ?>>進行中</option>
            <option value="与信怪しい" <?php echo in_array('与信怪しい', $search_negotiation_status) ? 'selected' : ''; ?>>与信怪しい</option>
            <option value="工事前再説" <?php echo in_array('工事前再説', $search_negotiation_status) ? 'selected' : ''; ?>>工事前再説</option>
            <option value="工事後再説" <?php echo in_array('工事後再説', $search_negotiation_status) ? 'selected' : ''; ?>>工事後再説</option>
            <option value="工事前キャンセル" <?php echo in_array('工事前キャンセル', $search_negotiation_status) ? 'selected' : ''; ?>>工事前キャンセル</option>
            <option value="工事後キャンセル" <?php echo in_array('工事後キャンセル', $search_negotiation_status) ? 'selected' : ''; ?>>工事後キャンセル</option>
            <option value="書換完了" <?php echo in_array('書換完了', $search_negotiation_status) ? 'selected' : ''; ?>>書換完了</option>
            <option value="承認完了" <?php echo in_array('承認完了', $search_negotiation_status) ? 'selected' : ''; ?>>承認完了</option>
            <option value="承認後キャンセル" <?php echo in_array('承認後キャンセル', $search_negotiation_status) ? 'selected' : ''; ?>>承認後キャンセル</option>
        </select>
    </div>
    <div class="form-group">
        <label for="rewrite_status">書換ステータス:</label>
        <select id="rewrite_status" name="rewrite_status[]" multiple onkeydown="preventEnterSubmit(event)">
            <option value="待ち" <?php echo in_array('待ち', $search_rewrite_status) ? 'selected' : ''; ?>>待ち</option>
            <option value="準備中" <?php echo in_array('準備中', $search_rewrite_status) ? 'selected' : ''; ?>>準備中</option>
            <option value="アポOK" <?php echo in_array('アポOK', $search_rewrite_status) ? 'selected' : ''; ?>>アポOK</option>
            <option value="残あり" <?php echo in_array('残あり', $search_rewrite_status) ? 'selected' : ''; ?>>残あり</option>
            <option value="完了" <?php echo in_array('完了', $search_rewrite_status) ? 'selected' : ''; ?>>完了</option>
        </select>
    </div>
    <div class="form-group">
        <label for="rewrite_start_date">書き換え日（開始）:</label>
        <input type="date" id="rewrite_start_date" name="rewrite_start_date" value="<?php echo htmlspecialchars($search_rewrite_start_date); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="rewrite_end_date">書き換え日（終了）:</label>
        <input type="date" id="rewrite_end_date" name="rewrite_end_date" value="<?php echo htmlspecialchars($search_rewrite_end_date); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="points_granted_month_start">ポイント付与月（開始）:</label>
        <input type="text" id="points_granted_month_start" name="points_granted_month_start" value="<?php echo htmlspecialchars($search_granted_start); ?>" pattern="\d{4}-\d{2}" placeholder="YYYY-MM" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="points_granted_month_end">ポイント付与月（終了）:</label>
        <input type="text" id="points_granted_month_end" name="points_granted_month_end" value="<?php echo htmlspecialchars($search_granted_end); ?>" pattern="\d{4}-\d{2}" placeholder="YYYY-MM" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="points_changed_month_start">ポイント変更月（開始）:</label>
        <input type="text" id="points_changed_month_start" name="points_changed_month_start" value="<?php echo htmlspecialchars($search_changed_start); ?>" pattern="\d{4}-\d{2}" placeholder="YYYY-MM" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="points_changed_month_end">ポイント変更月（終了）:</label>
        <input type="text" id="points_changed_month_end" name="points_changed_month_end" value="<?php echo htmlspecialchars($search_changed_end); ?>" pattern="\d{4}-\d{2}" placeholder="YYYY-MM" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="search-button">
        <input type="submit" value="検索" onkeydown="preventEnterSubmit(event)">
        <input type="button" value="全リスト表示" onclick="window.location.href='sales_points_list.php';" onkeydown="preventEnterSubmit(event)">
        <input type="button" value="印刷" onclick="window.print();" onkeydown="preventEnterSubmit(event)">
    </div>
</form>
<table>
    <tr>
        <th>担当者</th>
        <th>受注日</th>
        <th>顧客名</th>
        <th>開始日</th>
        <th>工事ステータス</th>
        <th>商談ステータス</th>
        <th>書換ステータス</th>
        <th>書き換え日</th>
        <th>ポイント</th>
        <th>撤去ポイント</th>
        <th>ポイント修正</th>
        <th>ポイント付与月</th>
        <th>ポイント変更月</th>
        <th>メモ</th>
        <th class="no-print">編集</th>
        <th class="no-print">削除</th>
    </tr>
    <?php
    if ($sales_points) {
        foreach ($sales_points as $point) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($point['employee_name']) . "</td>";
            echo "<td>" . htmlspecialchars($point['order_date'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($point['customer_name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($point['start_date'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($point['construction_status'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($point['negotiation_status'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($point['rewrite_status'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($point['rewrite_date'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($point['points'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($point['removal_points'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($point['points_revision'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($point['points_granted_month'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($point['points_changed_month'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($point['memo'] ?? '') . "</td>";
            echo "<td class='no-print'><a href='edit_sales_points.php?order_id=" . htmlspecialchars($point['order_id']) . "'>編集</a></td>";
            echo "<td class='no-print'><a href='delete_sales_points.php?order_id=" . htmlspecialchars($point['order_id']) . "'>削除</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='16'>データが見つかりません。</td></tr>";
    }
    ?>
</table>

<table class="summary-table">
    <tr>
        <th>担当者</th>
        <th>ポイント合計</th>
        <th>撤去ポイント合計</th>
        <th>ポイント修正合計</th>
        <th>ポイント付与月</th>
        <th>ポイント変更月</th>
    </tr>
    <?php
    foreach ($points_summary as $emp_name => $summary) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($emp_name) . "</td>";
        echo "<td>" . htmlspecialchars($summary['points']) . "</td>";
        echo "<td>" . htmlspecialchars($summary['removal_points']) . "</td>";
        echo "<td>" . htmlspecialchars($summary['points_revision']) . "</td>";
        echo "<td>" . htmlspecialchars(implode(', ', array_unique($summary['points_granted_month']))) . "</td>";
        echo "<td>" . htmlspecialchars(implode(', ', array_unique($summary['points_changed_month']))) . "</td>";
        echo "</tr>";
    }
    ?>
</table>

<script>
function preventEnterSubmit(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
}
</script>
</body>
</html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>