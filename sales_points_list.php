<?php
include_once 'db_connection.php';
ob_start();
include_once 'header.php';

function exportCSV($filename, $headers, $data) {
    ob_end_clean();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");
    fputcsv($output, $headers);
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

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
    $search_rewrite_end_date = $_GET['rewrite_start_date'] ?? '';
    $search_granted_start = $_GET['points_granted_month_start'] ?? '';
    $search_granted_end = $_GET['points_granted_month_end'] ?? '';
    $search_changed_start = $_GET['points_changed_month_start'] ?? '';
    $search_changed_end = $_GET['points_changed_month_end'] ?? '';

    $now = new DateTime();
    $last_month_start = (clone $now)->modify('first day of last month')->format('Y-m-d');
    $this_month_end = (clone $now)->modify('last day of this month')->format('Y-m-d');
    $current_month = $now->format('Y-m');
    $last_month = (clone $now)->modify('last month')->format('Y-m');
    $two_months_ago = (clone $now)->modify('-2 months')->format('Y-m'); // 先々月

    $is_initial_load = empty($_GET) || (!isset($_GET['all']) && !$search_customer && !$search_employee && empty($search_construction_status) && empty($search_negotiation_status) && empty($search_rewrite_status) && !$search_rewrite_start_date && !$search_rewrite_end_date && !$search_granted_start && !$search_granted_end && !$search_changed_start && !$search_changed_end);
    if ($is_initial_load) {
        $search_start_date = $last_month_start;
        $search_end_date = $this_month_end;
    }
    $base_query = "SELECT sp.point_id, sp.order_id, sp.employee_id, sp.rewrite_date, sp.points, sp.referral_points, sp.vehicle_points,
    sp.removal_points, sp.points_revision, sp.points_granted_month, sp.points_changed_month, sp.memo,
    o.order_date, o.customer_name, o.construction_status, o.negotiation_status, 
    o.rewrite_status, e.full_name AS employee_name, e.department,
    (SELECT MIN(it.start_date) 
     FROM installation_projects ip 
     LEFT JOIN installation_tasks it ON ip.project_id = it.project_id 
     WHERE ip.order_id = o.id) AS start_date
FROM sales_points sp
JOIN orders o ON sp.order_id = o.id
JOIN employees e ON sp.employee_id = e.employee_id
WHERE o.construction_status IN ('残あり', '完了', '回収待ち', '回収完了')";

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

$vehicle_points_summary = [];
foreach ($sales_points as $point) {
$emp_name = $point['employee_name'];
if (!isset($vehicle_points_summary[$emp_name])) {
$vehicle_points_summary[$emp_name] = [
'total' => 0,
'count' => 0
];
}
$vehicle_points = $point['vehicle_points'] ?? 0;
$vehicle_points_summary[$emp_name]['total'] += $vehicle_points;
if ($vehicle_points > 0) {
$vehicle_points_summary[$emp_name]['count'] += 1;
}
}

$assessment_orders = [];
$referrals = [];
$carryovers = [];
$carryover_removals = []; // 変数名を「繰越撤去」に変更
$revisions = [];

foreach ($sales_points as $point) {
$order_date_month = substr($point['order_date'], 0, 7);
$granted_month = $point['points_granted_month'] ?? '';
if ($granted_month === 'Mar-25') $granted_month = '2025-03';

if ($granted_month === $current_month && $order_date_month === $last_month && (($point['points'] ?? 0) > 0 || ($point['removal_points'] ?? 0) > 0)) {
$assessment_orders[] = $point;
}
if ($granted_month === $current_month && $order_date_month === $last_month && ($point['referral_points'] ?? 0) > 0) {
$referrals[] = $point;
}
if ($granted_month === $current_month && $order_date_month <= $two_months_ago && (($point['points'] ?? 0) > 0 || ($point['referral_points'] ?? 0) > 0)) {
$carryovers[] = $point;
}
// 繰越撤去: 当月の撤去ポイントで先々月以前
if ($granted_month === $current_month && ($point['removal_points'] ?? 0) > 0 && $order_date_month <= $two_months_ago) {
$carryover_removals[] = $point;
}
if ($granted_month === $current_month && ($point['points_revision'] ?? 0) != 0) {
$revisions[] = $point;
}
}

$total_points = 0;
foreach ($assessment_orders as $point) {
$total_points += ($point['points'] ?? 0) + ($point['referral_points'] ?? 0) + ($point['removal_points'] ?? 0) + ($point['points_revision'] ?? 0);
}
foreach ($referrals as $point) {
$total_points += ($point['points'] ?? 0) + ($point['referral_points'] ?? 0) + ($point['removal_points'] ?? 0) + ($point['points_revision'] ?? 0);
}
foreach ($carryovers as $point) {
$total_points += ($point['points'] ?? 0) + ($point['referral_points'] ?? 0) + ($point['removal_points'] ?? 0) + ($point['points_revision'] ?? 0);
}
foreach ($carryover_removals as $point) { // 変数名変更
$total_points += ($point['points'] ?? 0) + ($point['referral_points'] ?? 0) + ($point['removal_points'] ?? 0) + ($point['points_revision'] ?? 0);
}
foreach ($revisions as $point) {
$total_points += ($point['points'] ?? 0) + ($point['referral_points'] ?? 0) + ($point['removal_points'] ?? 0) + ($point['points_revision'] ?? 0);
}
if (isset($_GET['export']) && $_GET['export'] === 'list') {
    $headers = ['担当者', '受注日', '顧客名', '開始日', '工事ステータス', '商談ステータス', '書換ステータス', '書き換え日', 'ポイント', '紹介ポイント', '撤去ポイント', 'ポイント修正', 'ポイント付与月', 'ポイント変更月', 'メモ'];
    $data = [];
    foreach ($sales_points as $point) {
        $data[] = [
            $point['employee_name'],
            $point['order_date'] ?? '',
            $point['customer_name'] ?? '',
            $point['start_date'] ?? '',
            $point['construction_status'] ?? '',
            $point['negotiation_status'] ?? '',
            $point['rewrite_status'] ?? '',
            $point['rewrite_date'] ?? '',
            $point['points'] ?? '',
            $point['referral_points'] ?? '',
            $point['removal_points'] ?? '',
            $point['points_revision'] ?? '',
            $point['points_granted_month'] ?? '',
            $point['points_changed_month'] ?? '',
            $point['memo'] ?? ''
        ];
    }
    exportCSV('sales_points_list.csv', $headers, $data);
} elseif (isset($_GET['export']) && $_GET['export'] === 'summary') {
    $headers = ['カテゴリ', '担当者', '受注日', '顧客名', '開始日', '書き換え日', 'ポイント', '紹介ポイント', '撤去ポイント', 'ポイント修正', '件数', '車輛ポイント合計'];
    $data = [];
    foreach (['査定受注' => $assessment_orders, '紹介' => $referrals, '繰越し' => $carryovers, '繰越撤去' => $carryover_removals, '修正' => $revisions] as $category => $items) {
        foreach ($items as $point) {
            $data[] = [
                $category,
                $point['employee_name'],
                $point['order_date'] ?? '',
                $point['customer_name'] ?? '',
                $point['start_date'] ?? '',
                $point['rewrite_date'] ?? '',
                $point['points'] ?? '',
                $point['referral_points'] ?? '',
                $point['removal_points'] ?? '',
                $point['points_revision'] ?? '',
                '', // 件数（他の表では空）
                ''  // 車輛ポイント合計（他の表では空）
            ];
        }
    }
    foreach ($vehicle_points_summary as $emp_name => $vehicle_data) {
        $data[] = [
            '車輛ポイント',
            $emp_name,
            '', // 受注日
            '', // 顧客名
            '', // 開始日
            '', // 書き換え日
            '', // ポイント
            '', // 紹介ポイント
            '', // 撤去ポイント
            '', // ポイント修正
            $vehicle_data['count'],
            $vehicle_data['total']
        ];
    }
    exportCSV('sales_points_summary.csv', $headers, $data);
}

$employees_stmt = $conn->query("SELECT employee_id, full_name, department FROM employees ORDER BY department, full_name");
$employees = $employees_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title></title>
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
    th:nth-child(1), td:nth-child(1) { width: 10%; }
    th:nth-child(2), td:nth-child(2) { width: 10%; }
    th:nth-child(3), td:nth-child(3) { width: 12%; }
    th:nth-child(4), td:nth-child(4) { width: 8%; }
    th:nth-child(5), td:nth-child(5) { width: 8%; }
    th:nth-child(6), td:nth-child(6) { width: 8%; }
    th:nth-child(7), td:nth-child(7) { width: 8%; }
    th:nth-child(8), td:nth-child(8) { width: 8%; }
    th:nth-child(9), td:nth-child(9) { width: 5%; }
    th:nth-child(10), td:nth-child(10) { width: 5%; }
    th:nth-child(11), td:nth-child(11) { width: 5%; }
    th:nth-child(12), td:nth-child(12) { width: 5%; }
    th:nth-child(13), td:nth-child(13) { width: 5%; }
    th:nth-child(14), td:nth-child(14) { width: 5%; }
    th:nth-child(15), td:nth-child(15) { width: 10%; }
    th:nth-child(16), td:nth-child(16) { width: 5%; }
    th:nth-child(17), td:nth-child(17) { width: 5%; }
    .summary-table, .vehicle-summary-table {
        width: 100%;
        margin: 5px 0;
        border-collapse: collapse;
        font-size: 0.8em;
    }
    .summary-table th, .summary-table td {
        border: 1px solid #ddd;
        padding: 3px;
        white-space: normal;
    }
    .summary-table th:nth-child(1), .summary-table td:nth-child(1) { width: 30mm; } /* 担当者 */
    .summary-table th:nth-child(2), .summary-table td:nth-child(2) { width: 20mm; } /* 受注日 */
    .summary-table th:nth-child(3), .summary-table td:nth-child(3) { width: 40mm; } /* 顧客名 */
    .summary-table th:nth-child(4), .summary-table td:nth-child(4) { width: 20mm; } /* 開始日 */
    .summary-table th:nth-child(5), .summary-table td:nth-child(5) { width: 20mm; } /* 書き換え日 */
    .summary-table th:nth-child(6), .summary-table td:nth-child(6) { width: auto; } /* ポイント系1 */
    .summary-table th:nth-child(7), .summary-table td:nth-child(7) { width: auto; } /* ポイント系2 */
    .vehicle-summary-table th, .vehicle-summary-table td {
        border: 1px solid #ddd;
        padding: 3px;
        white-space: normal;
    }
    .vehicle-summary-table th:nth-child(1), .vehicle-summary-table td:nth-child(1) { width: 30mm; }
    .vehicle-summary-table th:nth-child(2), .vehicle-summary-table td:nth-child(2) { width: 100mm; }
    .vehicle-summary-table th:nth-child(3), .vehicle-summary-table td:nth-child(3) { width: auto; }
    .category-title {
        font-size: 1em;
        font-weight: bold;
        margin: 3px 0;
        background-color: #f2f2f2;
        padding: 2px;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    tfoot td {
        font-weight: bold;
        background-color: #f9f9f9;
    }
    .print-area {
        display: block;
    }
    .conditions {
        margin-top: 10px;
        font-size: 0.9em;
        padding: 5px;
        border: 1px solid #ddd;
        background-color: #f9f9f9;
    }
    @media print {
        body > *:not(.print-area) {
            display: none;
        }
        .no-print, .main-list {
            display: none;
        }
        .print-area { 
            display: block;
            width: 210mm;
            height: 297mm;
            margin: 0;
            padding: 3mm;
            box-sizing: border-box;
            page-break-before: always;
        }
        .summary-table, .vehicle-summary-table {
            width: 100%;
            margin: 2px 0;
            font-size: 0.6em;
            page-break-inside: avoid;
        }
        .summary-table th, .summary-table td {
            padding: 2px;
        }
        .summary-table th:nth-child(1), .summary-table td:nth-child(1) { width: 30mm; }
        .summary-table th:nth-child(2), .summary-table td:nth-child(2) { width: 20mm; }
        .summary-table th:nth-child(3), .summary-table td:nth-child(3) { width: 40mm; }
        .summary-table th:nth-child(4), .summary-table td:nth-child(4) { width: 20mm; }
        .summary-table th:nth-child(5), .summary-table td:nth-child(5) { width: 20mm; }
        .summary-table th:nth-child(6), .summary-table td:nth-child(6) { width: auto; }
        .summary-table th:nth-child(7), .summary-table td:nth-child(7) { width: auto; }
        .vehicle-summary-table th, .vehicle-summary-table td {
            padding: 2px;
        }
        .vehicle-summary-table th:nth-child(1), .vehicle-summary-table td:nth-child(1) { width: 30mm; }
        .vehicle-summary-table th:nth-child(2), .vehicle-summary-table td:nth-child(2) { width: 100mm; }
        .vehicle-summary-table th:nth-child(3), .vehicle-summary-table td:nth-child(3) { width: auto; }
        .category-title {
            font-size: 0.8em;
            margin: 2px 0;
            background-color: #f2f2f2;
            padding: 2px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            margin: 0;
            padding: 0;
        }
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
    <input type="button" value="全リスト表示" onclick="window.location.href='sales_points_list.php?all=1';" onkeydown="preventEnterSubmit(event)">
    <input type="button" value="初期画面に戻る" onclick="window.location.href='sales_points_list.php?start_date=<?php echo urlencode($last_month_start); ?>&end_date=<?php echo urlencode($this_month_end); ?>';" onkeydown="preventEnterSubmit(event)">
    <input type="button" value="印刷" onclick="window.print();" onkeydown="preventEnterSubmit(event)">
    <input type="button" value="リストCSV" onclick="window.location.href='sales_points_list.php?export=list&<?php echo htmlspecialchars(http_build_query($_GET)); ?>';" onkeydown="preventEnterSubmit(event)">
    <input type="button" value="集計CSV" onclick="window.location.href='sales_points_list.php?export=summary&<?php echo htmlspecialchars(http_build_query($_GET)); ?>';" onkeydown="preventEnterSubmit(event)">
</div>
</form>

<div class="conditions no-print">
<h3>表示条件</h3>
<ul>
    <li>査定受注: ポイント付与月が当月で受注日が先月のポイントと撤去ポイント</li>
    <li>紹介: ポイント付与月が当月で受注日が先月の紹介ポイント</li>
    <li>繰越し: ポイント付与月が当月で受注日が先々月以前のポイントと紹介ポイント</li>
    <li>繰越撤去: ポイント付与月が当月の撤去ポイントのうち受注日が先々月以前のもの</li>
    <li>修正: ポイント付与月が当月のポイント修正</li>
</ul>
<p>※印刷時、背景色が見えない場合は、印刷設定で「背景のグラフィック」または「背景色とイメージ」にチェックを入れてください（ブラウザによる）。</p>
</div>

<table class="main-list">
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
    <th>紹介ポイント</th>
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
        echo "<td>" . htmlspecialchars($point['referral_points'] ?? '') . "</td>";
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
    echo "<tr><td colspan='17'>データが見つかりません。</td></tr>";
}
?>
</table>

<div class="print-area">
<!-- 査定受注: ポイントと撤去ポイント -->
<div class="category-title">査定受注</div>
<table class="summary-table">
    <thead>
        <tr>
            <th>担当者</th>
            <th>受注日</th>
            <th>顧客名</th>
            <th>開始日</th>
            <th>書き換え日</th>
            <th>ポイント</th>
            <th>撤去ポイント</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $subtotal = ['points' => 0, 'removal_points' => 0];
        if ($assessment_orders) {
            foreach ($assessment_orders as $point) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($point['employee_name']) . "</td>";
                echo "<td>" . htmlspecialchars($point['order_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['customer_name'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['start_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['rewrite_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['points'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['removal_points'] ?? '') . "</td>";
                echo "</tr>";
                $subtotal['points'] += $point['points'] ?? 0;
                $subtotal['removal_points'] += $point['removal_points'] ?? 0;
            }
        } else {
            echo "<tr><td colspan='7'>データなし</td></tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">合計</td>
            <td><?php echo $subtotal['points']; ?></td>
            <td><?php echo $subtotal['removal_points']; ?></td>
        </tr>
    </tfoot>
</table>

<!-- 紹介: 紹介ポイントのみ -->
<div class="category-title">紹介</div>
<table class="summary-table">
    <thead>
        <tr>
            <th>担当者</th>
            <th>受注日</th>
            <th>顧客名</th>
            <th>開始日</th>
            <th>書き換え日</th>
            <th>紹介ポイント</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $subtotal = ['referral_points' => 0];
        if ($referrals) {
            foreach ($referrals as $point) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($point['employee_name']) . "</td>";
                echo "<td>" . htmlspecialchars($point['order_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['customer_name'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['start_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['rewrite_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['referral_points'] ?? '') . "</td>";
                echo "</tr>";
                $subtotal['referral_points'] += $point['referral_points'] ?? 0;
            }
        } else {
            echo "<tr><td colspan='6'>データなし</td></tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">合計</td>
            <td><?php echo $subtotal['referral_points']; ?></td>
        </tr>
    </tfoot>
</table>

<!-- 繰越し: ポイントと紹介ポイント -->
<div class="category-title">繰越し</div>
<table class="summary-table">
    <thead>
        <tr>
            <th>担当者</th>
            <th>受注日</th>
            <th>顧客名</th>
            <th>開始日</th>
            <th>書き換え日</th>
            <th>ポイント</th>
            <th>紹介ポイント</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $subtotal = ['points' => 0, 'referral_points' => 0];
        if ($carryovers) {
            foreach ($carryovers as $point) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($point['employee_name']) . "</td>";
                echo "<td>" . htmlspecialchars($point['order_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['customer_name'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['start_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['rewrite_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['points'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['referral_points'] ?? '') . "</td>";
                echo "</tr>";
                $subtotal['points'] += $point['points'] ?? 0;
                $subtotal['referral_points'] += $point['referral_points'] ?? 0;
            }
        } else {
            echo "<tr><td colspan='7'>データなし</td></tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">合計</td>
            <td><?php echo $subtotal['points']; ?></td>
            <td><?php echo $subtotal['referral_points']; ?></td>
        </tr>
    </tfoot>
</table>

<!-- 繰越撤去: 撤去ポイントのみ -->
<div class="category-title">繰越撤去</div>
<table class="summary-table">
    <thead>
        <tr>
            <th>担当者</th>
            <th>受注日</th>
            <th>顧客名</th>
            <th>開始日</th>
            <th>書き換え日</th>
            <th>撤去ポイント</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $subtotal = ['removal_points' => 0];
        if ($carryover_removals) {
            foreach ($carryover_removals as $point) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($point['employee_name']) . "</td>";
                echo "<td>" . htmlspecialchars($point['order_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['customer_name'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['start_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['rewrite_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['removal_points'] ?? '') . "</td>";
                echo "</tr>";
                $subtotal['removal_points'] += $point['removal_points'] ?? 0;
            }
        } else {
            echo "<tr><td colspan='6'>データなし</td></tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">合計</td>
            <td><?php echo $subtotal['removal_points']; ?></td>
        </tr>
    </tfoot>
</table>

<!-- 修正: ポイント修正のみ -->
<div class="category-title">修正</div>
<table class="summary-table">
    <thead>
        <tr>
            <th>担当者</th>
            <th>受注日</th>
            <th>顧客名</th>
            <th>開始日</th>
            <th>書き換え日</th>
            <th>ポイント修正</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $subtotal = ['points_revision' => 0];
        if ($revisions) {
            foreach ($revisions as $point) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($point['employee_name']) . "</td>";
                echo "<td>" . htmlspecialchars($point['order_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['customer_name'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['start_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['rewrite_date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($point['points_revision'] ?? '') . "</td>";
                echo "</tr>";
                $subtotal['points_revision'] += $point['points_revision'] ?? 0;
            }
        } else {
            echo "<tr><td colspan='6'>データなし</td></tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">合計</td>
            <td><?php echo $subtotal['points_revision']; ?></td>
        </tr>
    </tfoot>
</table>

<!-- 合計: 変更なし -->
<div class="category-title">合計</div>
<table class="summary-table">
    <thead>
        <tr>
            <th>全ポイント合計</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?php echo $total_points; ?></td>
        </tr>
    </tbody>
</table>

<!-- 車輛ポイント: 幅調整 -->
<div class="category-title">車輛ポイント</div>
<table class="vehicle-summary-table">
    <thead>
        <tr>
            <th>担当者</th>
            <th>件数</th>
            <th>車輛ポイント合計</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($vehicle_points_summary as $emp_name => $vehicle_data) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($emp_name) . "</td>";
            echo "<td>" . htmlspecialchars($vehicle_data['count']) . "</td>";
            echo "<td>" . htmlspecialchars($vehicle_data['total']) . "</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
</div>

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
ob_end_clean();
echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
ob_end_flush();
?>