<?php
include_once 'db_connection.php';
ob_start();
$page_title = "受注管理";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $employees_stmt = $conn->query("SELECT employee_id, full_name, department FROM employees ORDER BY department, full_name");
    $employees = $employees_stmt->fetchAll();

    $export_csv = isset($_GET['export_csv']) && $_GET['export_csv'] === '1';

    $search_customer = $_GET['customer'] ?? '';
    $search_customer_type = $_GET['customer_type'] ?? '';
    $search_start_date = $_GET['start_date'] ?? '';
    $search_end_date = $_GET['end_date'] ?? '';
    $search_negotiation_status = $_GET['negotiation_status'] ?? '';
    $search_construction_status = $_GET['construction_status'] ?? '';
    $search_credit_status = $_GET['credit_status'] ?? '';
    $search_document_status = $_GET['document_status'] ?? '';
    $search_rewrite_status = $_GET['rewrite_status'] ?? '';
    $search_seal_status = $_GET['seal_status'] ?? '';
    $search_shipping_status = $_GET['shipping_status'] ?? '';
    $search_sales_rep_1 = $_GET['sales_rep_1'] ?? '';
    $search_sales_rep_2 = $_GET['sales_rep_2'] ?? '';
    $search_sales_rep_3 = $_GET['sales_rep_3'] ?? '';
    $search_sales_rep_4 = $_GET['sales_rep_4'] ?? '';
    $search_appointment_1 = $_GET['appointment_1'] ?? '';
    $search_appointment_2 = $_GET['appointment_2'] ?? '';
    $search_rewriting_person = $_GET['rewriting_person'] ?? '';
    $search_min_monthly_fee = $_GET['min_monthly_fee'] ?? '';
    $search_max_monthly_fee = $_GET['max_monthly_fee'] ?? '';

    $query = "SELECT o.*, 
              e1.full_name AS sales_rep_1,
              e2.full_name AS sales_rep_2,
              e3.full_name AS sales_rep_3,
              e4.full_name AS sales_rep_4,
              a1.full_name AS appointment_rep_1,
              a2.full_name AS appointment_rep_2,
              r.full_name AS rewriting_person,
              (SELECT COALESCE(SUM(COALESCE(od.mobile_revision, 0) + COALESCE(od.monitor_total, 0) + COALESCE(od.service_total, 0)), 0)
               FROM order_details od WHERE od.order_id = o.id) AS revision_total_calc,
              (SELECT COUNT(*) FROM order_details od WHERE od.order_id = o.id) AS details_count,
              (SELECT od.id FROM order_details od WHERE od.order_id = o.id LIMIT 1) AS detail_id,
              (SELECT COUNT(*) FROM sales_points sp WHERE sp.order_id = o.id) AS points_count
              FROM orders o 
              LEFT JOIN employees e1 ON o.sales_rep_id = e1.employee_id 
              LEFT JOIN employees e2 ON o.sales_rep_id_2 = e2.employee_id 
              LEFT JOIN employees e3 ON o.sales_rep_id_3 = e3.employee_id 
              LEFT JOIN employees e4 ON o.sales_rep_id_4 = e4.employee_id 
              LEFT JOIN employees a1 ON o.appointment_rep_id_1 = a1.employee_id 
              LEFT JOIN employees a2 ON o.appointment_rep_id_2 = a2.employee_id 
              LEFT JOIN employees r ON o.rewriting_person_id = r.employee_id 
              WHERE 1=1";
    $params = [];

    if ($search_customer) {
        $query .= " AND o.customer_name LIKE ?";
        $params[] = "%$search_customer%";
    }
    if ($search_customer_type) {
        $query .= " AND o.customer_type = ?";
        $params[] = $search_customer_type;
    }
    if ($search_start_date) {
        $query .= " AND o.order_date >= ?";
        $params[] = $search_start_date;
    }
    if ($search_end_date) {
        $query .= " AND o.order_date <= ?";
        $params[] = $search_end_date;
    }
    if ($search_negotiation_status) {
        $query .= " AND o.negotiation_status = ?";
        $params[] = $search_negotiation_status;
    }
    if ($search_construction_status) {
        $query .= " AND o.construction_status = ?";
        $params[] = $search_construction_status;
    }
    if ($search_credit_status) {
        $query .= " AND o.credit_status = ?";
        $params[] = $search_credit_status;
    }
    if ($search_document_status) {
        $query .= " AND o.document_status = ?";
        $params[] = $search_document_status;
    }
    if ($search_rewrite_status) {
        $query .= " AND o.rewrite_status = ?";
        $params[] = $search_rewrite_status;
    }
    if ($search_seal_status) {
        $query .= " AND o.seal_certificate_status = ?";
        $params[] = $search_seal_status;
    }
    if ($search_shipping_status) {
        $query .= " AND o.shipping_status = ?";
        $params[] = $search_shipping_status;
    }
    if ($search_sales_rep_1) {
        $query .= " AND o.sales_rep_id = ?";
        $params[] = $search_sales_rep_1;
    }
    if ($search_sales_rep_2) {
        $query .= " AND o.sales_rep_id_2 = ?";
        $params[] = $search_sales_rep_2;
    }
    if ($search_sales_rep_3) {
        $query .= " AND o.sales_rep_id_3 = ?";
        $params[] = $search_sales_rep_3;
    }
    if ($search_sales_rep_4) {
        $query .= " AND o.sales_rep_id_4 = ?";
        $params[] = $search_sales_rep_4;
    }
    if ($search_appointment_1) {
        $query .= " AND o.appointment_rep_id_1 = ?";
        $params[] = $search_appointment_1;
    }
    if ($search_appointment_2) {
        $query .= " AND o.appointment_rep_id_2 = ?";
        $params[] = $search_appointment_2;
    }
    if ($search_rewriting_person) {
        $query .= " AND o.rewriting_person_id = ?";
        $params[] = $search_rewriting_person;
    }
    if ($search_min_monthly_fee !== '') {
        $query .= " AND o.monthly_fee >= ?";
        $params[] = $search_min_monthly_fee;
    }
    if ($search_max_monthly_fee !== '') {
        $query .= " AND o.monthly_fee <= ?";
        $params[] = $search_max_monthly_fee;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    if ($export_csv) {
        ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="orders_list_' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");

        $headers = [
            '受注日', '顧客名', '客層', '月額 (税抜)', '回数', '見直し合計 (税込)', '商談ステータス', 
            '工事ステータス', '与信ステータス', '書類ステータス', '書換ステータス', '印鑑証明ステータス', 
            '発送ステータス', 'メモ', '担当者1', '担当者2', '担当者3', '担当者4', 'アポイント者1', 'アポイント者2', '書換担当'
        ];
        fputcsv($output, $headers);

        foreach ($orders as $row) {
            $data = [
                $row['order_date'] ?? '',
                $row['customer_name'] ?? '',
                $row['customer_type'] ?? '',
                number_format($row['monthly_fee'], 0),
                $row['total_payments'] ?? '',
                number_format($row['revision_total_calc'], 0),
                $row['negotiation_status'] ?? '',
                $row['construction_status'] ?? '',
                $row['credit_status'] ?? '',
                $row['document_status'] ?? '',
                $row['rewrite_status'] ?? '',
                $row['seal_certificate_status'] ?? '',
                $row['shipping_status'] ?? '',
                $row['memo'] ?? '',
                $row['sales_rep_1'] ?? '',
                $row['sales_rep_2'] ?? '',
                $row['sales_rep_3'] ?? '',
                $row['sales_rep_4'] ?? '',
                $row['appointment_rep_1'] ?? '',
                $row['appointment_rep_2'] ?? '',
                $row['rewriting_person'] ?? ''
            ];
            fputcsv($output, $data);
        }

        fclose($output);
        exit;
    }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <style>
        .search-form { 
            display: grid; 
            grid-template-columns: repeat(6, 1fr);
            gap: 10px; 
            margin-bottom: 20px; 
        }
        .form-group { 
            display: flex; 
            flex-direction: column; 
        }
        .form-group label { 
            margin-bottom: 5px; 
            font-size: 0.9em; 
        }
        .form-group input, .form-group select { 
            padding: 5px; 
            width: 100%; 
            box-sizing: border-box; 
        }
        .range-group { 
            display: flex; 
            gap: 5px; 
        }
        .search-button { 
            grid-column: span 6; 
            text-align: center; 
        }
        .search-button input { 
            margin: 0 10px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed;
        }
        th { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
            white-space: normal;
            overflow: hidden; 
            text-overflow: ellipsis; 
            background-color: #f2f2f2; 
            font-size: 0.9em; 
        }
        td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
            white-space: nowrap;
            overflow: hidden; 
            text-overflow: ellipsis; 
        }
        td:nth-child(1), td:nth-child(2), td:nth-child(4), td:nth-child(14), td:nth-child(22) { /* 受注日、顧客名、月額、メモ、アクション */
            white-space: normal;
        }
        th:nth-child(7), td:nth-child(7),
        th:nth-child(8), td:nth-child(8),
        th:nth-child(9), td:nth-child(9),
        th:nth-child(10), td:nth-child(10),
        th:nth-child(11), td:nth-child(11),
        th:nth-child(12), td:nth-child(12),
        th:nth-child(13), td:nth-child(13) {
            background-color: #e6f3ff;
        }
        th:nth-child(1), td:nth-child(1) { width: 5%; }
        th:nth-child(2), td:nth-child(2) { width: 15%; }
        th:nth-child(3), td:nth-child(3) { width: 4%; }
        th:nth-child(4), td:nth-child(4) { width: 6%; }
        th:nth-child(5), td:nth-child(5) { width: 4%; }
        th:nth-child(6), td:nth-child(6) { width: 6%; }
        th:nth-child(7), td:nth-child(7) { width: 6%; }
        th:nth-child(8), td:nth-child(8) { width: 6%; }
        th:nth-child(9), td:nth-child(9) { width: 6%; }
        th:nth-child(10), td:nth-child(10) { width: 6%; }
        th:nth-child(11), td:nth-child(11) { width: 6%; }
        th:nth-child(12), td:nth-child(12) { width: 6%; }
        th:nth-child(13), td:nth-child(13) { width: 6%; }
        th:nth-child(14), td:nth-child(14) { width: 12%; }
        th:nth-child(15), td:nth-child(15) { width: 5%; }
        th:nth-child(16), td:nth-child(16) { width: 5%; }
        th:nth-child(17), td:nth-child(17) { width: 5%; }
        th:nth-child(18), td:nth-child(18) { width: 5%; }
        th:nth-child(19), td:nth-child(19) { width: 5%; }
        th:nth-child(20), td:nth-child(20) { width: 5%; }
        th:nth-child(21), td:nth-child(21) { width: 5%; }
        th:nth-child(22), td:nth-child(22) { width: 8%; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
<?php
    static $message_displayed = false;
    if (!$message_displayed && isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
        $message_displayed = true;
    }
?>

<form method="GET" action="orders_list.php" class="search-form" id="searchForm">
    <div class="form-group">
        <label for="customer">顧客名:</label>
        <input type="text" id="customer" name="customer" value="<?php echo htmlspecialchars($search_customer); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="customer_type">客層:</label>
        <select id="customer_type" name="customer_type" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <option value="新規" <?php echo $search_customer_type === '新規' ? 'selected' : ''; ?>>新規</option>
            <option value="既存" <?php echo $search_customer_type === '既存' ? 'selected' : ''; ?>>既存</option>
            <option value="旧顧客" <?php echo $search_customer_type === '旧顧客' ? 'selected' : ''; ?>>旧顧客</option>
        </select>
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
        <label>月額 (税抜):</label>
        <div class="range-group">
            <input type="number" id="min_monthly_fee" name="min_monthly_fee" placeholder="最小" value="<?php echo htmlspecialchars($search_min_monthly_fee); ?>" step="1" min="0" onkeydown="preventEnterSubmit(event)">
            <input type="number" id="max_monthly_fee" name="max_monthly_fee" placeholder="最大" value="<?php echo htmlspecialchars($search_max_monthly_fee); ?>" step="1" min="0" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label for="negotiation_status">商談ステータス:</label>
        <select id="negotiation_status" name="negotiation_status" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <option value="未設定" <?php echo $search_negotiation_status === '未設定' ? 'selected' : ''; ?>>未設定</option>
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
    <div class="form-group">
        <label for="construction_status">工事ステータス:</label>
        <select id="construction_status" name="construction_status" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <option value="待ち" <?php echo $search_construction_status === '待ち' ? 'selected' : ''; ?>>待ち</option>
            <option value="与信待ち" <?php echo $search_construction_status === '与信待ち' ? 'selected' : ''; ?>>与信待ち</option>
            <option value="残あり" <?php echo $search_construction_status === '残あり' ? 'selected' : ''; ?>>残あり</option>
            <option value="完了" <?php echo $search_construction_status === '完了' ? 'selected' : ''; ?>>完了</option>
            <option value="回収待ち" <?php echo $search_construction_status === '回収待ち' ? 'selected' : ''; ?>>回収待ち</option>
            <option value="回収完了" <?php echo $search_construction_status === '回収完了' ? 'selected' : ''; ?>>回収完了</option>
        </select>
    </div>
    <div class="form-group">
        <label for="credit_status">与信ステータス:</label>
        <select id="credit_status" name="credit_status" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <option value="待ち" <?php echo $search_credit_status === '待ち' ? 'selected' : ''; ?>>待ち</option>
            <option value="与信中" <?php echo $search_credit_status === '与信中' ? 'selected' : ''; ?>>与信中</option>
            <option value="再与信中" <?php echo $search_credit_status === '再与信中' ? 'selected' : ''; ?>>再与信中</option>
            <option value="与信OK" <?php echo $search_credit_status === '与信OK' ? 'selected' : ''; ?>>与信OK</option>
            <option value="与信NG" <?php echo $search_credit_status === '与信NG' ? 'selected' : ''; ?>>与信NG</option>
        </select>
    </div>
    <div class="form-group">
        <label for="document_status">書類ステータス:</label>
        <select id="document_status" name="document_status" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <option value="待ち" <?php echo $search_document_status === '待ち' ? 'selected' : ''; ?>>待ち</option>
            <option value="準備中" <?php echo $search_document_status === '準備中' ? 'selected' : ''; ?>>準備中</option>
            <option value="変更中" <?php echo $search_document_status === '変更中' ? 'selected' : ''; ?>>変更中</option>
            <option value="発送済" <?php echo $search_document_status === '発送済' ? 'selected' : ''; ?>>発送済</option>
            <option value="受取済" <?php echo $search_document_status === '受取済' ? 'selected' : ''; ?>>受取済</option>
        </select>
    </div>
    <div class="form-group">
        <label for="rewrite_status">書換ステータス:</label>
        <select id="rewrite_status" name="rewrite_status" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <option value="待ち" <?php echo $search_rewrite_status === '待ち' ? 'selected' : ''; ?>>待ち</option>
            <option value="準備中" <?php echo $search_rewrite_status === '準備中' ? 'selected' : ''; ?>>準備中</option>
            <option value="アポOK" <?php echo $search_rewrite_status === 'アポOK' ? 'selected' : ''; ?>>アポOK</option>
            <option value="残あり" <?php echo $search_rewrite_status === '残あり' ? 'selected' : ''; ?>>残あり</option>
            <option value="完了" <?php echo $search_rewrite_status === '完了' ? 'selected' : ''; ?>>完了</option>
        </select>
    </div>
    <div class="form-group">
        <label for="seal_status">印鑑証明ステータス:</label>
        <select id="seal_status" name="seal_status" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <option value="不要" <?php echo $search_seal_status === '不要' ? 'selected' : ''; ?>>不要</option>
            <option value="取得待" <?php echo $search_seal_status === '取得待' ? 'selected' : ''; ?>>取得待</option>
            <option value="回収待" <?php echo $search_seal_status === '回収待' ? 'selected' : ''; ?>>回収待</option>
            <option value="完了" <?php echo $search_seal_status === '完了' ? 'selected' : ''; ?>>完了</option>
        </select>
    </div>
    <div class="form-group">
        <label for="shipping_status">発送ステータス:</label>
        <select id="shipping_status" name="shipping_status" onkeydown="preventEnterSubmit(event)">
            <option value="" <?php echo $search_shipping_status === '' ? 'selected' : ''; ?>>すべて</option>
            <option value="準備中" <?php echo $search_shipping_status === '準備中' ? 'selected' : ''; ?>>準備中</option>
            <option value="発送済" <?php echo $search_shipping_status === '発送済' ? 'selected' : ''; ?>>発送済</option>
        </select>
    </div>
    <div class="form-group">
        <label for="sales_rep_1">担当者1:</label>
        <select id="sales_rep_1" name="sales_rep_1" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php foreach ($employees as $emp) {
                $selected = $search_sales_rep_1 == $emp['employee_id'] ? 'selected' : '';
                $display_name = htmlspecialchars($emp['department'] . "/" . $emp['full_name']);
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>$display_name</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="sales_rep_2">担当者2:</label>
        <select id="sales_rep_2" name="sales_rep_2" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php foreach ($employees as $emp) {
                $selected = $search_sales_rep_2 == $emp['employee_id'] ? 'selected' : '';
                $display_name = htmlspecialchars($emp['department'] . "/" . $emp['full_name']);
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>$display_name</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="sales_rep_3">担当者3:</label>
        <select id="sales_rep_3" name="sales_rep_3" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php foreach ($employees as $emp) {
                $selected = $search_sales_rep_3 == $emp['employee_id'] ? 'selected' : '';
                $display_name = htmlspecialchars($emp['department'] . "/" . $emp['full_name']);
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>$display_name</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="sales_rep_4">担当者4:</label>
        <select id="sales_rep_4" name="sales_rep_4" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php foreach ($employees as $emp) {
                $selected = $search_sales_rep_4 == $emp['employee_id'] ? 'selected' : '';
                $display_name = htmlspecialchars($emp['department'] . "/" . $emp['full_name']);
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>$display_name</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="appointment_1">アポイント者1:</label>
        <select id="appointment_1" name="appointment_1" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php foreach ($employees as $emp) {
                $selected = $search_appointment_1 == $emp['employee_id'] ? 'selected' : '';
                $display_name = htmlspecialchars($emp['department'] . "/" . $emp['full_name']);
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>$display_name</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="appointment_2">アポイント者2:</label>
        <select id="appointment_2" name="appointment_2" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php foreach ($employees as $emp) {
                $selected = $search_appointment_2 == $emp['employee_id'] ? 'selected' : '';
                $display_name = htmlspecialchars($emp['department'] . "/" . $emp['full_name']);
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>$display_name</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="rewriting_person">書換担当:</label>
        <select id="rewriting_person" name="rewriting_person" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php foreach ($employees as $emp) {
                $selected = $search_rewriting_person == $emp['employee_id'] ? 'selected' : '';
                $display_name = htmlspecialchars($emp['department'] . "/" . $emp['full_name']);
                echo "<option value='" . htmlspecialchars($emp['employee_id']) . "' $selected>$display_name</option>";
            } ?>
        </select>
    </div>
    <div class="search-button">
        <input type="submit" value="検索">
        <input type="button" value="全リスト表示" onclick="window.location.href='orders_list.php';">
        <input type="button" value="CSVダウンロード" onclick="exportCSV();">
    </div>
</form>
<table>
    <tr>
        <th>受注日</th>
        <th>顧客名</th>
        <th>客層</th>
        <th>月額 (税抜)</th>
        <th>回数</th>
        <th>見直し合計 (税込)</th>
        <th>商談ステータス</th>
        <th>工事ステータス</th>
        <th>与信ステータス</th>
        <th>書類ステータス</th>
        <th>書換ステータス</th>
        <th>印鑑証明ステータス</th>
        <th>発送ステータス</th>
        <th>メモ</th>
        <th>担当者1</th>
        <th>担当者2</th>
        <th>担当者3</th>
        <th>担当者4</th>
        <th>アポイント者1</th>
        <th>アポイント者2</th>
        <th>書換担当</th>
        <th>アクション</th>
    </tr>
    <?php
    if ($orders) {
        foreach ($orders as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['order_date'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['customer_name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['customer_type'] ?? '') . "</td>";
            echo "<td>" . number_format($row['monthly_fee'], 0) . " 円</td>";
            echo "<td>" . htmlspecialchars($row['total_payments'] ?? '') . "</td>";
            echo "<td>" . number_format($row['revision_total_calc'], 0) . " 円</td>";
            echo "<td>" . htmlspecialchars($row['negotiation_status'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['construction_status'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['credit_status'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['document_status'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['rewrite_status'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['seal_certificate_status'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['shipping_status'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['memo'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['sales_rep_1'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['sales_rep_2'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['sales_rep_3'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['sales_rep_4'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['appointment_rep_1'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['appointment_rep_2'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['rewriting_person'] ?? '') . "</td>";
            echo "<td>";
            echo "<a href='edit_orders.php?id=" . htmlspecialchars($row['id']) . "'>編集</a> | ";
            echo "<a href='delete_orders.php?id=" . htmlspecialchars($row['id']) . "'>削除</a>";
            if ($row['details_count'] == 0) {
                echo " | <a href='add_order_details.php?order_id=" . htmlspecialchars($row['id']) . "&customer_name=" . urlencode($row['customer_name']) . "&sales_rep_id=" . urlencode($row['sales_rep_id'] ?? '') . "'>受注詳細追加</a>";
            } elseif ($row['detail_id']) {
                echo " | <a href='edit_order_details.php?id=" . htmlspecialchars($row['detail_id']) . "'>詳細編集</a>";
            }
            if (in_array($row['construction_status'], ['残あり', '完了', '回収待ち', '回収完了'])) {
                if ($row['points_count'] > 0) {
                    echo " | <a href='sales_points_list.php'>ポイント一覧</a>";
                } else {
                    echo " | <a href='add_sales_points.php?order_id=" . htmlspecialchars($row['id']) . "'>ポイント追加</a>";
                }
            }
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='22'>データが見つかりません。</td></tr>";
    }
    ?>
</table>

<script>
function preventEnterSubmit(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
}

function exportCSV() {
    var form = document.getElementById('searchForm');
    var url = new URL(form.action);
    var params = new URLSearchParams(new FormData(form));
    params.set('export_csv', '1');
    url.search = params.toString();
    window.location.href = url.toString();
}
</script>
</body>
</html>
<?php
} catch (Exception $e) {
    ob_end_clean();
    error_log("Error: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました。</p>";
}
ob_end_flush();
?>