<?php
include_once 'db_connection.php';
ob_start();
$page_title = "リース記録";
include_once 'header.php';

if (isset($_GET['toggle_admin'])) {
    $_SESSION['is_admin'] = !$_SESSION['is_admin'];
    header('Location: lease_contracts_list.php');
    exit;
}
$_SESSION['is_admin'] = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : false;

try {
    $conn = getDBConnection();

    // リクエストパラメータをログに記録
    error_log("Request params: " . json_encode($_GET));

    // 検索パラメータの取得
    $search_company = isset($_GET['company']) ? trim($_GET['company']) : '';
    $search_provider = isset($_GET['provider']) ? trim($_GET['provider']) : '';
    $search_start_date_min = isset($_GET['start_date_min']) ? trim($_GET['start_date_min']) : '';
    $search_start_date_max = isset($_GET['start_date_max']) ? trim($_GET['start_date_max']) : '';
    $search_end_date_min = isset($_GET['end_date_min']) ? trim($_GET['end_date_min']) : '';
    $search_end_date_max = isset($_GET['end_date_max']) ? trim($_GET['end_date_max']) : '';
    $search_remaining_payments_min = isset($_GET['remaining_payments_min']) ? trim($_GET['remaining_payments_min']) : '';
    $search_remaining_payments_max = isset($_GET['remaining_payments_max']) ? trim($_GET['remaining_payments_max']) : '';
    $search_remaining_balance_min = isset($_GET['remaining_balance_min']) ? trim($_GET['remaining_balance_min']) : '';
    $search_remaining_balance_max = isset($_GET['remaining_balance_max']) ? trim($_GET['remaining_balance_max']) : '';
    $search_leased_equipment = isset($_GET['leased_equipment']) ? trim($_GET['leased_equipment']) : '';
    $search_status = isset($_GET['search_status']) ? trim($_GET['search_status']) : '';
    $search_special_case = isset($_GET['special_case']) ? $_GET['special_case'] : ''; // trim を外して生の値を使用

    $export_csv = isset($_GET['export_csv']) && $_GET['export_csv'] === '1';

    // リース会社リストの取得
    $providers_stmt = $conn->query("SELECT provider_id, provider_name FROM lease_providers");
    $providers = $providers_stmt->fetchAll();

    // クエリ構築
    $query = "SELECT lc.*, c.company_name, lp.provider_name, 
                     GREATEST(0, lc.total_payments - IFNULL(lc.payments_made, 0)) AS remaining_payments,
                     (lc.monthly_fee * GREATEST(0, lc.total_payments - IFNULL(lc.payments_made, 0))) AS remaining_balance 
              FROM lease_contracts lc 
              LEFT JOIN companies c ON lc.company_id = c.company_id 
              LEFT JOIN lease_providers lp ON lc.provider_id = lp.provider_id 
              WHERE 1=1";
    $params = [];

    // 検索条件が送信された場合に適用
    if (isset($_GET['search']) && $_GET['search'] === '1') {
        if (!empty($search_company)) {
            $query .= " AND c.company_name LIKE :company";
            $params[':company'] = "%$search_company%";
            error_log("Company filter applied: " . $search_company);
        }
        if (!empty($search_provider)) {
            $query .= " AND lc.provider_id = :provider";
            $params[':provider'] = $search_provider;
            error_log("Provider filter applied: " . $search_provider);
        }
        if (!empty($search_start_date_min)) {
            $query .= " AND lc.start_date >= :start_date_min";
            $params[':start_date_min'] = $search_start_date_min;
            error_log("Start date min filter applied: " . $search_start_date_min);
        }
        if (!empty($search_start_date_max)) {
            $query .= " AND lc.start_date <= :start_date_max";
            $params[':start_date_max'] = $search_start_date_max;
            error_log("Start date max filter applied: " . $search_start_date_max);
        }
        if (!empty($search_end_date_min)) {
            $query .= " AND lc.end_date >= :end_date_min";
            $params[':end_date_min'] = $search_end_date_min;
            error_log("End date min filter applied: " . $search_end_date_min);
        }
        if (!empty($search_end_date_max)) {
            $query .= " AND lc.end_date <= :end_date_max";
            $params[':end_date_max'] = $search_end_date_max;
            error_log("End date max filter applied: " . $search_end_date_max);
        }
        if (!empty($search_leased_equipment)) {
            $query .= " AND EXISTS (SELECT 1 FROM leased_equipment le 
                                    LEFT JOIN equipment_master em ON le.equipment_id = em.equipment_id 
                                    WHERE le.contract_id = lc.contract_id 
                                    AND (em.equipment_name LIKE :leased_equipment OR em.model_number LIKE :leased_equipment))";
            $params[':leased_equipment'] = "%$search_leased_equipment%";
            error_log("Leased equipment filter applied: " . $search_leased_equipment);
        }
        if (!empty($search_status)) {
            $query .= " AND lc.status = :status";
            $params[':status'] = $search_status;
            error_log("Status filter applied: " . $search_status);
        }
        if (isset($_GET['special_case']) && in_array($search_special_case, ['', ' ', '補償'])) {
            if ($search_special_case !== '') { // "すべて"以外の場合に条件を適用
                $special_case_value = $search_special_case === ' ' ? '' : $search_special_case;
                $query .= " AND lc.special_case = :special_case";
                $params[':special_case'] = $special_case_value;
                error_log("Special case filter applied: '" . $special_case_value . "'");
            } else {
                error_log("Special case filter skipped (all selected)");
            }
        }
        if ($search_remaining_payments_min !== '' && is_numeric($search_remaining_payments_min)) {
            $query .= " AND GREATEST(0, lc.total_payments - IFNULL(lc.payments_made, 0)) >= :remaining_payments_min";
            $params[':remaining_payments_min'] = (int)$search_remaining_payments_min;
            error_log("Remaining payments min filter applied: " . $search_remaining_payments_min);
        }
        if ($search_remaining_payments_max !== '' && is_numeric($search_remaining_payments_max)) {
            $query .= " AND GREATEST(0, lc.total_payments - IFNULL(lc.payments_made, 0)) <= :remaining_payments_max";
            $params[':remaining_payments_max'] = (int)$search_remaining_payments_max;
            error_log("Remaining payments max filter applied: " . $search_remaining_payments_max);
        }
        if ($search_remaining_balance_min !== '' && is_numeric($search_remaining_balance_min)) {
            $query .= " AND (lc.monthly_fee * GREATEST(0, lc.total_payments - IFNULL(lc.payments_made, 0))) >= :remaining_balance_min";
            $params[':remaining_balance_min'] = (int)$search_remaining_balance_min;
            error_log("Remaining balance min filter applied: " . $search_remaining_balance_min);
        }
        if ($search_remaining_balance_max !== '' && is_numeric($search_remaining_balance_max)) {
            $query .= " AND (lc.monthly_fee * GREATEST(0, lc.total_payments - IFNULL(lc.payments_made, 0))) <= :remaining_balance_max";
            $params[':remaining_balance_max'] = (int)$search_remaining_balance_max;
            error_log("Remaining balance max filter applied: " . $search_remaining_balance_max);
        }
    }

    // デバッグ情報
    error_log("Query: $query, Params: " . json_encode($params));

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // デバッグ: 取得データ数をログに記録
    error_log("Fetched contracts count: " . count($contracts));
    if (count($contracts) > 0) {
        error_log("Sample contract: " . json_encode($contracts[0]));
    }

    // リース機器の取得
    $equipment_query = "SELECT lc.contract_id, GROUP_CONCAT(CONCAT(em.model_number, ' - ', em.equipment_name) SEPARATOR ', ') AS leased_equipment 
                        FROM lease_contracts lc 
                        LEFT JOIN leased_equipment le ON lc.contract_id = le.contract_id 
                        LEFT JOIN equipment_master em ON le.equipment_id = em.equipment_id 
                        GROUP BY lc.contract_id";
    $equipment_stmt = $conn->query($equipment_query);
    $equipment_data = $equipment_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // ステータス日本語化関数
    function translateStatus($status) {
        switch ($status) {
            case 'contract_active': return '契約中';
            case 'offsetting': return '相殺中';
            case 'early_termination': return '中途解約';
            case 'expired': return '満了';
            case 'lost_to_competitor': return '他社流出';
            default: return $status;
        }
    }

    // CSV エクスポート
    if ($export_csv) {
        ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="lease_contracts_list_' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

        $headers = [
            '契約ID', '顧客企業', 'リース会社', '開始日', '終了日', '月額 (税込)', '回数', 
            '支払済み回数', '残回数', '残債', 'リース機器', 'メモ', 'ステータス', '特案'
        ];
        fputcsv($output, $headers);

        foreach ($contracts as $item) {
            $remaining_payments = max(0, $item['total_payments'] - $item['payments_made']);
            $remaining_balance = $item['monthly_fee'] * $remaining_payments;

            $row = [
                $item['contract_id'],
                $item['company_name'] ?? '未設定',
                $item['provider_name'] ?? '未設定',
                $item['start_date'],
                $item['end_date'],
                number_format($item['monthly_fee'], 0),
                $item['total_payments'],
                $item['payments_made'],
                $remaining_payments,
                number_format($remaining_balance, 0),
                $equipment_data[$item['contract_id']] ?? '',
                $item['memo'] ?? '',
                translateStatus($item['status']),
                $item['special_case'] === '補償' ? '補償' : ''
            ];
            fputcsv($output, $row);
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
    .success { 
        color: green; 
    }
    .error { 
        color: red; 
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
    .date-range { 
        display: flex; 
        gap: 5px; 
        flex-wrap: nowrap; 
    }
    .search-button { 
        grid-column: span 4; 
        text-align: center; 
    }
</style>
<form method="GET" action="lease_contracts_list.php" class="search-form">
    <input type="hidden" name="search" value="1">
    <div class="form-group">
        <label for="company">企業名:</label>
        <input type="text" id="company" name="company" value="<?php echo htmlspecialchars($search_company); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="provider">リース会社:</label>
        <select id="provider" name="provider" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php foreach ($providers as $provider) {
                $selected = $provider['provider_id'] == $search_provider ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($provider['provider_id']) . "' $selected>" . htmlspecialchars($provider['provider_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label>開始日:</label>
        <div class="date-range">
            <input type="date" name="start_date_min" value="<?php echo htmlspecialchars($search_start_date_min); ?>" onkeydown="preventEnterSubmit(event)">
            <input type="date" name="start_date_max" value="<?php echo htmlspecialchars($search_start_date_max); ?>" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label>終了日:</label>
        <div class="date-range">
            <input type="date" name="end_date_min" value="<?php echo htmlspecialchars($search_end_date_min); ?>" onkeydown="preventEnterSubmit(event)">
            <input type="date" name="end_date_max" value="<?php echo htmlspecialchars($search_end_date_max); ?>" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label>残回数:</label>
        <div class="range-group">
            <input type="number" name="remaining_payments_min" value="<?php echo htmlspecialchars($search_remaining_payments_min); ?>" placeholder="最小" min="0" onkeydown="preventEnterSubmit(event)">
            <input type="number" name="remaining_payments_max" value="<?php echo htmlspecialchars($search_remaining_payments_max); ?>" placeholder="最大" min="0" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label>残債:</label>
        <div class="range-group">
            <input type="number" name="remaining_balance_min" value="<?php echo htmlspecialchars($search_remaining_balance_min); ?>" placeholder="最小" min="0" onkeydown="preventEnterSubmit(event)">
            <input type="number" name="remaining_balance_max" value="<?php echo htmlspecialchars($search_remaining_balance_max); ?>" placeholder="最大" min="0" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label for="leased_equipment">リース機器:</label>
        <input type="text" id="leased_equipment" name="leased_equipment" value="<?php echo htmlspecialchars($search_leased_equipment); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="search_status">ステータス:</label>
        <select id="search_status" name="search_status" onkeydown="preventEnterSubmit(event)">
            <option value="">すべて</option>
            <?php
            $statuses = ['contract_active' => '契約中', 'offsetting' => '相殺中', 'early_termination' => '中途解約', 'expired' => '満了', 'lost_to_competitor' => '他社流出'];
            foreach ($statuses as $key => $value) {
                $selected = $key == $search_status ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($key) . "' $selected>" . htmlspecialchars($value) . "</option>";
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
        <input type="button" value="全リスト表示" onclick="window.location.href='lease_contracts_list.php';">
        <input type="button" value="CSVダウンロード" onclick="exportCSV();">
    </div>
</form>
<table>
    <tr>
        <th>契約ID</th>
        <th>顧客企業</th>
        <th>リース会社</th>
        <th>開始日</th>
        <th>終了日</th>
        <th>月額 (税込)</th>
        <th>回数</th>
        <th>支払済み回数</th>
        <th>残回数</th>
        <th>残債</th>
        <th>リース機器</th>
        <th>メモ</th>
        <th>ステータス</th>
        <th>特案</th>
        <th>アクション</th>
    </tr>
    <?php 
    if ($contracts) {
        foreach ($contracts as $item) {
            $remaining_payments = max(0, $item['total_payments'] - $item['payments_made']);
            $remaining_balance = $item['monthly_fee'] * $remaining_payments;
    ?>
    <tr>
        <td><?php echo htmlspecialchars($item['contract_id']); ?></td>
        <td><?php echo htmlspecialchars($item['company_name'] ?? '未設定'); ?></td>
        <td><?php echo htmlspecialchars($item['provider_name'] ?? '未設定'); ?></td>
        <td><?php echo htmlspecialchars($item['start_date']); ?></td>
        <td><?php echo htmlspecialchars($item['end_date']); ?></td>
        <td><?php echo number_format($item['monthly_fee'], 0); ?> 円</td>
        <td><?php echo htmlspecialchars($item['total_payments']); ?></td>
        <td><?php echo htmlspecialchars($item['payments_made']); ?></td>
        <td><?php echo htmlspecialchars($remaining_payments); ?></td>
        <td><?php echo number_format($remaining_balance, 0); ?> 円</td>
        <td><?php echo htmlspecialchars($equipment_data[$item['contract_id']] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($item['memo'] ?? ''); ?></td>
        <td><?php echo translateStatus($item['status']); ?></td>
        <td><?php echo htmlspecialchars($item['special_case'] === '補償' ? '補償' : ''); ?></td>
        <td>
            <a href="edit_lease_contracts.php?contract_id=<?php echo htmlspecialchars($item['contract_id']); ?>">編集</a> | 
            <a href="delete_lease_contracts.php?contract_id=<?php echo htmlspecialchars($item['contract_id']); ?>">削除</a>
        </td>
    </tr>
    <?php 
        }
    } else {
        echo "<tr><td colspan='15'>データが見つかりません。</td></tr>";
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
    error_log("Error in lease_contracts_list.php: " . $e->getMessage());
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
ob_end_flush();
?>