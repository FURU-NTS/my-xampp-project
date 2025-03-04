<?php
include_once 'db_connection.php';
ob_start();
$page_title = "与信管理";
include_once 'header.php';

if (isset($_GET['toggle_admin'])) {
    $_SESSION['is_admin'] = !$_SESSION['is_admin'];
    header('Location: credit_applications_list.php');
    exit;
}
$_SESSION['is_admin'] = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : false;

try {
    $conn = getDBConnection();

    // 検索パラメータの取得
    $search_application_id = isset($_GET['application_id']) ? $_GET['application_id'] : '';
    $search_order_date_min = isset($_GET['order_date_min']) ? $_GET['order_date_min'] : '';
    $search_order_date_max = isset($_GET['order_date_max']) ? $_GET['order_date_max'] : '';
    $search_customer_name = isset($_GET['customer_name']) ? $_GET['customer_name'] : '';
    $search_provider_id = isset($_GET['provider_id']) ? $_GET['provider_id'] : '';
    $search_application_date_min = isset($_GET['application_date_min']) ? $_GET['application_date_min'] : '';
    $search_application_date_max = isset($_GET['application_date_max']) ? $_GET['application_date_max'] : '';
    $search_monthly_fee_min = isset($_GET['monthly_fee_min']) ? $_GET['monthly_fee_min'] : '';
    $search_monthly_fee_max = isset($_GET['monthly_fee_max']) ? $_GET['monthly_fee_max'] : '';
    $search_total_payments_min = isset($_GET['total_payments_min']) ? $_GET['total_payments_min'] : '';
    $search_total_payments_max = isset($_GET['total_payments_max']) ? $_GET['total_payments_max'] : '';
    $search_memo = isset($_GET['memo']) ? $_GET['memo'] : '';
    $search_expected_payment_min = isset($_GET['expected_payment_min']) ? $_GET['expected_payment_min'] : '';
    $search_expected_payment_max = isset($_GET['expected_payment_max']) ? $_GET['expected_payment_max'] : '';
    $search_expected_payment_date_min = isset($_GET['expected_payment_date_min']) ? $_GET['expected_payment_date_min'] : ''; // 追加
    $search_expected_payment_date_max = isset($_GET['expected_payment_date_max']) ? $_GET['expected_payment_date_max'] : ''; // 追加
    $search_status = isset($_GET['status']) ? $_GET['status'] : '';
    $search_special_case = isset($_GET['special_case']) ? $_GET['special_case'] : '';
    $export_csv = isset($_GET['export_csv']) && $_GET['export_csv'] === '1';

    // リース会社リストの取得
    $stmt = $conn->query("SELECT provider_id, provider_name FROM lease_providers");
    $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 全データを取得
    $query = "SELECT ca.*, o.order_date, o.customer_name, lp.provider_name, c.company_name 
              FROM credit_applications ca 
              LEFT JOIN orders o ON ca.order_id = o.id 
              LEFT JOIN lease_providers lp ON ca.provider_id = lp.provider_id 
              LEFT JOIN companies c ON ca.company_id = c.company_id 
              WHERE 1=1";
    $params = [];

    // 検索条件が明示的に指定された場合のみ適用
    if (isset($_GET['search']) && $_GET['search'] === '1') {
        if (!empty($search_application_id)) {
            $query .= " AND ca.application_id LIKE :application_id";
            $params[':application_id'] = "%$search_application_id%";
        }
        if (!empty($search_order_date_min)) {
            $query .= " AND o.order_date >= :order_date_min";
            $params[':order_date_min'] = $search_order_date_min;
        }
        if (!empty($search_order_date_max)) {
            $query .= " AND o.order_date <= :order_date_max";
            $params[':order_date_max'] = $search_order_date_max;
        }
        if (!empty($search_customer_name)) {
            $query .= " AND o.customer_name LIKE :customer_name";
            $params[':customer_name'] = "%$search_customer_name%";
        }
        if (!empty($search_provider_id)) {
            $query .= " AND ca.provider_id = :provider_id";
            $params[':provider_id'] = $search_provider_id;
        }
        if (!empty($search_application_date_min)) {
            $query .= " AND ca.application_date >= :application_date_min";
            $params[':application_date_min'] = $search_application_date_min;
        }
        if (!empty($search_application_date_max)) {
            $query .= " AND ca.application_date <= :application_date_max";
            $params[':application_date_max'] = $search_application_date_max;
        }
        if (!empty($search_monthly_fee_min)) {
            $query .= " AND ca.monthly_fee >= :monthly_fee_min";
            $params[':monthly_fee_min'] = $search_monthly_fee_min;
        }
        if (!empty($search_monthly_fee_max)) {
            $query .= " AND ca.monthly_fee <= :monthly_fee_max";
            $params[':monthly_fee_max'] = $search_monthly_fee_max;
        }
        if (!empty($search_total_payments_min)) {
            $query .= " AND ca.total_payments >= :total_payments_min";
            $params[':total_payments_min'] = $search_total_payments_min;
        }
        if (!empty($search_total_payments_max)) {
            $query .= " AND ca.total_payments <= :total_payments_max";
            $params[':total_payments_max'] = $search_total_payments_max;
        }
        if (!empty($search_memo)) {
            $query .= " AND ca.memo LIKE :memo";
            $params[':memo'] = "%$search_memo%";
        }
        if (!empty($search_expected_payment_min) && $_SESSION['is_admin']) {
            $query .= " AND ca.expected_payment >= :expected_payment_min";
            $params[':expected_payment_min'] = $search_expected_payment_min;
        }
        if (!empty($search_expected_payment_max) && $_SESSION['is_admin']) {
            $query .= " AND ca.expected_payment <= :expected_payment_max";
            $params[':expected_payment_max'] = $search_expected_payment_max;
        }
        if (!empty($search_expected_payment_date_min) && $_SESSION['is_admin']) {
            $query .= " AND ca.expected_payment_date >= :expected_payment_date_min";
            $params[':expected_payment_date_min'] = $search_expected_payment_date_min;
        }
        if (!empty($search_expected_payment_date_max) && $_SESSION['is_admin']) {
            $query .= " AND ca.expected_payment_date <= :expected_payment_date_max";
            $params[':expected_payment_date_max'] = $search_expected_payment_date_max;
        }
        if (!empty($search_status)) {
            $query .= " AND ca.status = :status";
            $params[':status'] = $search_status;
        }
        if (isset($_GET['special_case']) && in_array($search_special_case, ['', '補償'])) {
            $query .= " AND ca.special_case = :special_case";
            $params[':special_case'] = $search_special_case;
        }
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // デバッグ情報
    $debug_info = "Query: $query\nItems Count: " . count($items) . "\nGET Params: " . json_encode($_GET) . "\nApplied Params: " . json_encode($params);
    error_log($debug_info);

    if ($export_csv) {
        ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="credit_applications_list_' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

        $headers = ['申請ID', '受注日', '顧客名', 'リース会社', '申請日', '月額 (税抜)', '回数', 'メモ'];
        if ($_SESSION['is_admin']) {
            $headers[] = '見積金額 (税込)';
            $headers[] = '入金予定日';
        }
        $headers[] = 'ステータス';
        $headers[] = '特案';
        fputcsv($output, $headers);

        foreach ($items as $row) {
            $data = [
                $row['application_id'],
                $row['order_date'] ?? '',
                $row['customer_name'] ?? '',
                $row['provider_name'] ?? '',
                $row['application_date'] ?? '',
                number_format($row['monthly_fee'], 0) . ' 円',
                $row['total_payments'] ?? '',
                $row['memo'] ?? '',
            ];
            if ($_SESSION['is_admin']) {
                $data[] = number_format($row['expected_payment'], 0) . ' 円';
                $data[] = $row['expected_payment_date'] ?? '';
            }
            $data[] = $row['status'];
            $data[] = $row['special_case'] === '補償' ? '補償' : '';
            fputcsv($output, $data);
        }

        fclose($output);
        exit;
    }

    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<style>
    table { 
        width: 100%; 
        border-collapse: collapse; 
    }
    th, td { 
        border: 1px solid #ddd; 
        padding: 8px; 
        text-align: left; 
    }
    th { 
        background-color: #f2f2f2; 
    }
    .table-header { 
        margin-bottom: 10px; 
    }
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
    .range-group { 
        display: flex; 
        gap: 5px; 
    }
    .search-button { 
        grid-column: span 4; 
        text-align: center; 
    }
</style>
<div style="margin-bottom: 10px;">
    <a href="credit_applications_list.php?toggle_admin=1">管理者モード: <?php echo $_SESSION['is_admin'] ? 'ON' : 'OFF'; ?> (切り替え)</a>
</div>
<div class="table-header">
    <a href="add_credit_applications.php">リース審査追加</a>
</div>
<form method="GET" action="credit_applications_list.php" class="search-form">
    <input type="hidden" name="search" value="1">
    <div class="form-group">
        <label for="application_id">申請ID:</label>
        <input type="text" id="application_id" name="application_id" value="<?php echo htmlspecialchars($search_application_id); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label>受注日:</label>
        <div class="range-group">
            <input type="date" name="order_date_min" value="<?php echo htmlspecialchars($search_order_date_min); ?>" onkeydown="preventEnterSubmit(event)">
            <input type="date" name="order_date_max" value="<?php echo htmlspecialchars($search_order_date_max); ?>" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label for="customer_name">顧客名:</label>
        <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($search_customer_name); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="provider_id">リース会社:</label>
        <select id="provider_id" name="provider_id" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php foreach ($providers as $provider) {
                $selected = $provider['provider_id'] == $search_provider_id ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($provider['provider_id']) . "' $selected>" . htmlspecialchars($provider['provider_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label>申請日:</label>
        <div class="range-group">
            <input type="date" name="application_date_min" value="<?php echo htmlspecialchars($search_application_date_min); ?>" onkeydown="preventEnterSubmit(event)">
            <input type="date" name="application_date_max" value="<?php echo htmlspecialchars($search_application_date_max); ?>" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label>月額 (税抜):</label>
        <div class="range-group">
            <input type="number" name="monthly_fee_min" value="<?php echo htmlspecialchars($search_monthly_fee_min); ?>" placeholder="最小" min="0" onkeydown="preventEnterSubmit(event)">
            <input type="number" name="monthly_fee_max" value="<?php echo htmlspecialchars($search_monthly_fee_max); ?>" placeholder="最大" min="0" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label>回数:</label>
        <div class="range-group">
            <input type="number" name="total_payments_min" value="<?php echo htmlspecialchars($search_total_payments_min); ?>" placeholder="最小" min="1" onkeydown="preventEnterSubmit(event)">
            <input type="number" name="total_payments_max" value="<?php echo htmlspecialchars($search_total_payments_max); ?>" placeholder="最大" min="1" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label for="memo">メモ:</label>
        <input type="text" id="memo" name="memo" value="<?php echo htmlspecialchars($search_memo); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <?php if ($_SESSION['is_admin']) { ?>
    <div class="form-group">
        <label>見積金額 (税込):</label>
        <div class="range-group">
            <input type="number" name="expected_payment_min" value="<?php echo htmlspecialchars($search_expected_payment_min); ?>" placeholder="最小" min="0" onkeydown="preventEnterSubmit(event)">
            <input type="number" name="expected_payment_max" value="<?php echo htmlspecialchars($search_expected_payment_max); ?>" placeholder="最大" min="0" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label>入金予定日:</label>
        <div class="range-group">
            <input type="date" name="expected_payment_date_min" value="<?php echo htmlspecialchars($search_expected_payment_date_min); ?>" onkeydown="preventEnterSubmit(event)">
            <input type="date" name="expected_payment_date_max" value="<?php echo htmlspecialchars($search_expected_payment_date_max); ?>" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <?php } ?>
    <div class="form-group">
        <label for="status">ステータス:</label>
        <select id="status" name="status" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php
            $statuses = ['準備中', '与信中', '条件あり', '与信OK', '特案OK', '与信NG', '手続き待ち', '手続きOK', '承認待ち', '承認完了', '証明書待ち', '入金待ち', '入金完了', '商談保留', '商談キャンセル', '承認後キャンセル'];
            foreach ($statuses as $s) {
                $selected = $s == $search_status ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($s) . "' $selected>" . htmlspecialchars($s) . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="special_case">特案:</label>
        <select id="special_case" name="special_case" onkeydown="preventEnterSubmit(event)">
            <option value="" <?php echo $search_special_case === '' ? 'selected' : ''; ?>>すべて</option>
            <option value=" " <?php echo $search_special_case === ' ' ? 'selected' : ''; ?>>空白</option>
            <option value="補償" <?php echo $search_special_case === '補償' ? 'selected' : ''; ?>>補償</option>
        </select>
    </div>
    <div class="search-button">
        <input type="submit" value="検索">
        <input type="button" value="全リスト表示" onclick="window.location.href='credit_applications_list.php';">
        <input type="button" value="CSVダウンロード" onclick="exportCSV();">
    </div>
</form>
<table>
    <tr>
        <th>申請ID</th>
        <th>受注日</th>
        <th>顧客名</th>
        <th>リース会社</th>
        <th>申請日</th>
        <th>月額 (税抜)</th>
        <th>回数</th>
        <th>メモ</th>
        <?php if ($_SESSION['is_admin']) { ?>
            <th>見積金額 (税込)</th>
            <th>入金予定日</th>
        <?php } ?>
        <th>ステータス</th>
        <th>特案</th>
        <th>アクション</th>
    </tr>
    <?php
    if ($items) {
        foreach ($items as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['application_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['order_date'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['customer_name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['provider_name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['application_date'] ?? '') . "</td>";
            echo "<td>" . number_format($row['monthly_fee'], 0) . " 円</td>";
            echo "<td>" . htmlspecialchars($row['total_payments'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['memo'] ?? '') . "</td>";
            if ($_SESSION['is_admin']) {
                echo "<td>" . number_format($row['expected_payment'], 0) . " 円</td>";
                echo "<td>" . htmlspecialchars($row['expected_payment_date'] ?? '') . "</td>";
            }
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['special_case'] === '補償' ? '補償' : '') . "</td>";
            echo "<td>";
            echo "<a href='edit_credit_applications.php?application_id=" . htmlspecialchars($row['application_id']) . "'>編集</a> | ";
            echo "<a href='delete_credit_applications.php?application_id=" . htmlspecialchars($row['application_id']) . "'>削除</a>";

            try {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM lease_contracts WHERE credit_application_id = :id");
                $stmt->execute([':id' => $row['application_id']]);
                $lease_exists = $stmt->fetchColumn() > 0;
            } catch (Exception $e) {
                error_log("Lease contracts check failed: " . $e->getMessage());
                $lease_exists = false;
            }

            if ($lease_exists) {
                echo " | 追加済み";
            } elseif ($row['status'] === '入金完了' && !empty($row['provider_id'])) {
                echo " | <a href='add_lease_contracts.php?credit_application_id=" . htmlspecialchars($row['application_id']) . "&company_id=" . htmlspecialchars($row['company_id']) . "&customer_name=" . urlencode($row['customer_name']) . "&monthly_fee=" . urlencode($row['monthly_fee']) . "&total_payments=" . urlencode($row['total_payments']) . "&provider_id=" . urlencode($row['provider_id']) . "'>リース契約追加</a>";
            } elseif ($row['status'] === '入金完了') {
                echo " | リース会社未設定";
            }
            echo "</td>";
            echo "</tr>";
        }
    } else {
        $colspan = $_SESSION['is_admin'] ? 12 : 10; // 入金予定日分を追加
        echo "<tr><td colspan='$colspan'>データが見つかりません。</td></tr>";
    }
    ?>
</table>
<a href="add_credit_applications.php">リース審査追加</a>
<script>
function preventEnterSubmit(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
}

function exportCSV() {
    var form = document.querySelector('form');
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
    error_log("Error in credit_applications_list.php: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました: " . htmlspecialchars($e->getMessage()) . "</p>";
}
ob_end_flush();
?>