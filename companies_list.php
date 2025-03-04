<?php
include_once 'db_connection.php';
ob_start();
$page_title = "顧客企業MASTER";
include_once 'header.php';

try {
    $conn = getDBConnection();

    // CSVインポートの処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
        $file = $_FILES['csv_file']['tmp_name'];
        $filename = $_FILES['csv_file']['name'];
        if (($handle = fopen($file, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ","); // ヘッダー行をスキップ
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (strpos($filename, 'companies_new_template') !== false) {
                    // 新規用シート (20列: company_idを除く)
                    if (count($data) === 20) {
                        $stmt = $conn->prepare("INSERT INTO companies (company_name, business_registration_number, industry_type, address, postal_code, phone_number, email, representative_name, decision_maker_1, decision_maker_2, representative_income, representative_contact, employee_count, capital, revenue, teikoku, tosho, memo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9],
                            $data[10] ?: null, $data[11], $data[12] ?: null, $data[13] ?: null, $data[14] ?: null, $data[15] ?: null,
                            $data[16], $data[17], $data[18], $data[19]
                        ]);
                    }
                } elseif (strpos($filename, 'companies_update_template') !== false) {
                    // 既存変更用シート (21列: company_idを含む)
                    if (count($data) === 21 && !empty($data[0])) {
                        $stmt = $conn->prepare("INSERT INTO companies (company_id, company_name, business_registration_number, industry_type, address, postal_code, phone_number, email, representative_name, decision_maker_1, decision_maker_2, representative_income, representative_contact, employee_count, capital, revenue, teikoku, tosho, memo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE company_name = ?, business_registration_number = ?, industry_type = ?, address = ?, postal_code = ?, phone_number = ?, email = ?, representative_name = ?, decision_maker_1 = ?, decision_maker_2 = ?, representative_income = ?, representative_contact = ?, employee_count = ?, capital = ?, revenue = ?, teikoku = ?, tosho = ?, memo = ?, created_at = ?, updated_at = ?");
                        $stmt->execute([
                            $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9],
                            $data[10], $data[11] ?: null, $data[12], $data[13] ?: null, $data[14] ?: null, $data[15] ?: null, $data[16] ?: null,
                            $data[17], $data[18], $data[19], $data[20], // INSERT用
                            $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9],
                            $data[10], $data[11] ?: null, $data[12], $data[13] ?: null, $data[14] ?: null, $data[15] ?: null, $data[16] ?: null,
                            $data[17], $data[18], $data[19], $data[20] // UPDATE用
                        ]);
                    }
                }
            }
            fclose($handle);
            header('Location: companies_list.php?status=success&message=CSVデータがインポートされました');
            exit;
        } else {
            throw new Exception('CSVファイルの読み込みに失敗しました');
        }
    }

    // 空のCSVテンプレートダウンロード（新規用）
    if (isset($_GET['download_new_template'])) {
        ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="companies_new_template.csv"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
        $headers = ['会社名', '登記番号', '業種', '住所', '郵便番号', '電話番号', 'メール', '代表者名', '決裁者1', '決裁者2', '代表年収(万円)', '代表連絡先', '従業員数', '資本金(万円)', '売上(万円)', '帝国', '東商', 'メモ', '作成日', '更新日'];
        fputcsv($output, $headers);
        fclose($output);
        exit;
    }

    // 既存データのCSVテンプレートダウンロード（既存変更用）
    if (isset($_GET['download_update_template'])) {
        ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="companies_update_template.csv"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
        $headers = ['会社ID', '会社名', '登記番号', '業種', '住所', '郵便番号', '電話番号', 'メール', '代表者名', '決裁者1', '決裁者2', '代表年収(万円)', '代表連絡先', '従業員数', '資本金(万円)', '売上(万円)', '帝国', '東商', 'メモ', '作成日', '更新日'];
        fputcsv($output, $headers);

        $stmt = $conn->query("SELECT company_id, company_name, business_registration_number, industry_type, address, postal_code, phone_number, email, representative_name, decision_maker_1, decision_maker_2, representative_income, representative_contact, employee_count, capital, revenue, teikoku, tosho, memo, created_at, updated_at FROM companies");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data = [
                $row['company_id'],
                $row['company_name'],
                $row['business_registration_number'],
                $row['industry_type'],
                $row['address'],
                $row['postal_code'],
                $row['phone_number'],
                $row['email'],
                $row['representative_name'] ?? '',
                $row['decision_maker_1'] ?? '',
                $row['decision_maker_2'] ?? '',
                $row['representative_income'] !== null ? $row['representative_income'] : '',
                $row['representative_contact'] ?? '',
                $row['employee_count'] !== null ? $row['employee_count'] : '',
                $row['capital'] !== null ? $row['capital'] : '',
                $row['revenue'] !== null ? $row['revenue'] : '',
                $row['teikoku'] !== null ? $row['teikoku'] : '',
                $row['tosho'] ?? '',
                $row['memo'] ?? '',
                $row['created_at'],
                $row['updated_at']
            ];
            fputcsv($output, $data);
        }
        fclose($output);
        exit;
    }

    // 検索条件を取得
    $export_csv = isset($_GET['export_csv']) && $_GET['export_csv'] === '1';
    $company_name = $_GET['company_name'] ?? '';
    $business_registration_number = $_GET['business_registration_number'] ?? '';
    $industry_type = $_GET['industry_type'] ?? '';
    $address = $_GET['address'] ?? '';
    $postal_code = $_GET['postal_code'] ?? '';
    $phone_number = $_GET['phone_number'] ?? '';
    $representative_name = $_GET['representative_name'] ?? '';
    $min_representative_income = $_GET['min_representative_income'] ?? '';
    $max_representative_income = $_GET['max_representative_income'] ?? '';
    $min_employee_count = $_GET['min_employee_count'] ?? '';
    $max_employee_count = $_GET['max_employee_count'] ?? '';
    $min_capital = $_GET['min_capital'] ?? '';
    $max_capital = $_GET['max_capital'] ?? '';
    $min_revenue = $_GET['min_revenue'] ?? '';
    $max_revenue = $_GET['max_revenue'] ?? '';
    $min_teikoku = $_GET['min_teikoku'] ?? '';
    $max_teikoku = $_GET['max_teikoku'] ?? '';
    $tosho = $_GET['tosho'] ?? [];
    $memo = $_GET['memo'] ?? '';
    $created_at_start = $_GET['created_at_start'] ?? '';
    $created_at_end = $_GET['created_at_end'] ?? '';
    $updated_at_start = $_GET['updated_at_start'] ?? '';
    $updated_at_end = $_GET['updated_at_end'] ?? '';

    // 検索クエリ構築
    $query = "SELECT * FROM companies WHERE 1=1";
    $params = [];

    if (!empty($company_name)) {
        $query .= " AND company_name LIKE ?";
        $params[] = "%$company_name%";
    }
    if (!empty($business_registration_number)) {
        $query .= " AND business_registration_number LIKE ?";
        $params[] = "%$business_registration_number%";
    }
    if (!empty($industry_type)) {
        $query .= " AND industry_type LIKE ?";
        $params[] = "%$industry_type%";
    }
    if (!empty($address)) {
        $query .= " AND address LIKE ?";
        $params[] = "%$address%";
    }
    if (!empty($postal_code)) {
        $query .= " AND postal_code LIKE ?";
        $params[] = "%$postal_code%";
    }
    if (!empty($phone_number)) {
        $query .= " AND phone_number LIKE ?";
        $params[] = "%$phone_number%";
    }
    if (!empty($representative_name)) {
        $query .= " AND representative_name LIKE ?";
        $params[] = "%$representative_name%";
    }
    if (!empty($min_representative_income)) {
        $query .= " AND representative_income >= ?";
        $params[] = $min_representative_income;
    }
    if (!empty($max_representative_income)) {
        $query .= " AND representative_income <= ?";
        $params[] = $max_representative_income;
    }
    if (!empty($min_employee_count)) {
        $query .= " AND employee_count >= ?";
        $params[] = $min_employee_count;
    }
    if (!empty($max_employee_count)) {
        $query .= " AND employee_count <= ?";
        $params[] = $max_employee_count;
    }
    if (!empty($min_capital)) {
        $query .= " AND capital >= ?";
        $params[] = $min_capital;
    }
    if (!empty($max_capital)) {
        $query .= " AND capital <= ?";
        $params[] = $max_capital;
    }
    if (!empty($min_revenue)) {
        $query .= " AND revenue >= ?";
        $params[] = $min_revenue;
    }
    if (!empty($max_revenue)) {
        $query .= " AND revenue <= ?";
        $params[] = $max_revenue;
    }
    if (!empty($min_teikoku)) {
        $query .= " AND teikoku >= ?";
        $params[] = $min_teikoku;
    }
    if (!empty($max_teikoku)) {
        $query .= " AND teikoku <= ?";
        $params[] = $max_teikoku;
    }
    if (!empty($tosho) && is_array($tosho)) {
        $placeholders = implode(',', array_fill(0, count($tosho), '?'));
        $query .= " AND tosho IN ($placeholders)";
        $params = array_merge($params, $tosho);
    }
    if (!empty($memo)) {
        $query .= " AND memo LIKE ?";
        $params[] = "%$memo%";
    }
    if (!empty($created_at_start)) {
        $query .= " AND DATE(created_at) >= ?";
        $params[] = $created_at_start;
    }
    if (!empty($created_at_end)) {
        $query .= " AND DATE(created_at) <= ?";
        $params[] = $created_at_end;
    }
    if (!empty($updated_at_start)) {
        $query .= " AND DATE(updated_at) >= ?";
        $params[] = $updated_at_start;
    }
    if (!empty($updated_at_end)) {
        $query .= " AND DATE(updated_at) <= ?";
        $params[] = $updated_at_end;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    // 既存のCSVエクスポート処理
    if ($export_csv) {
        ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="companies_list_' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        $headers = ['会社名', '登記番号', '業種', '住所', '郵便番号', '電話番号', 'メール', '代表者名', '決裁者1', '決裁者2', '代表年収(万円)', '代表連絡先', '従業員数', '資本金(万円)', '売上(万円)', '帝国', '東商', 'メモ', '作成日', '更新日'];
        fputcsv($output, $headers);
        foreach ($items as $row) {
            $data = [
                $row['company_name'],
                $row['business_registration_number'],
                $row['industry_type'],
                $row['address'],
                $row['postal_code'],
                $row['phone_number'],
                $row['email'],
                $row['representative_name'] ?? '未設定',
                $row['decision_maker_1'] ?? '未設定',
                $row['decision_maker_2'] ?? '未設定',
                $row['representative_income'] !== null ? number_format($row['representative_income'], 0) : '未設定',
                $row['representative_contact'] ?? '未設定',
                $row['employee_count'] !== null ? $row['employee_count'] : '未設定',
                $row['capital'] !== null ? number_format($row['capital'], 0) : '未設定',
                $row['revenue'] !== null ? number_format($row['revenue'], 0) : '未設定',
                $row['teikoku'] !== null ? number_format($row['teikoku'], 0) : '未設定',
                $row['tosho'] ?? '未設定',
                $row['memo'] ?? '未設定',
                $row['created_at'],
                $row['updated_at']
            ];
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
    .search-form { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group label { margin-bottom: 5px; font-size: 0.9em; }
    .form-group input, .form-group textarea, .form-group select { padding: 5px; width: 100%; box-sizing: border-box; }
    .range-group { display: flex; gap: 5px; }
    .search-button { grid-column: span 4; text-align: center; }
    .search-button input { margin: 0 10px; }
    .table-header { margin-bottom: 10px; }
    .csv-import { margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .success { color: green; }
    .error { color: red; }
</style>
<form method="GET" action="companies_list.php" class="search-form" id="searchForm">
    <div class="form-group">
        <label for="company_name">会社名:</label>
        <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company_name); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="business_registration_number">登記番号:</label>
        <input type="text" id="business_registration_number" name="business_registration_number" value="<?php echo htmlspecialchars($business_registration_number); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="industry_type">業種:</label>
        <input type="text" id="industry_type" name="industry_type" value="<?php echo htmlspecialchars($industry_type); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="address">住所:</label>
        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="postal_code">郵便番号:</label>
        <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($postal_code); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="phone_number">電話番号:</label>
        <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="representative_name">代表者名:</label>
        <input type="text" id="representative_name" name="representative_name" value="<?php echo htmlspecialchars($representative_name); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label>代表年収(万円):</label>
        <div class="range-group">
            <input type="number" id="min_representative_income" name="min_representative_income" placeholder="最小" value="<?php echo htmlspecialchars($min_representative_income); ?>" step="1" min="0" onkeydown="preventEnterSubmit(event)">
            <input type="number" id="max_representative_income" name="max_representative_income" placeholder="最大" value="<?php echo htmlspecialchars($max_representative_income); ?>" step="1" min="0" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label>従業員数:</label>
        <div class="range-group">
            <input type="number" id="min_employee_count" name="min_employee_count" placeholder="最小" value="<?php echo htmlspecialchars($min_employee_count); ?>" step="1" min="0" onkeydown="preventEnterSubmit(event)">
            <input type="number" id="max_employee_count" name="max_employee_count" placeholder="最大" value="<?php echo htmlspecialchars($max_employee_count); ?>" step="1" min="0" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label>資本金(万円):</label>
        <div class="range-group">
            <input type="number" id="min_capital" name="min_capital" placeholder="最小" value="<?php echo htmlspecialchars($min_capital); ?>" step="1" min="0" onkeydown="preventEnterSubmit(event)">
            <input type="number" id="max_capital" name="max_capital" placeholder="最大" value="<?php echo htmlspecialchars($max_capital); ?>" step="1" min="0" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label>売上(万円):</label>
        <div class="range-group">
            <input type="number" id="min_revenue" name="min_revenue" placeholder="最小" value="<?php echo htmlspecialchars($min_revenue); ?>" step="1" min="0" onkeydown="preventEnterSubmit(event)">
            <input type="number" id="max_revenue" name="max_revenue" placeholder="最大" value="<?php echo htmlspecialchars($max_revenue); ?>" step="1" min="0" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label>帝国:</label>
        <div class="range-group">
            <input type="number" id="min_teikoku" name="min_teikoku" placeholder="最小" value="<?php echo htmlspecialchars($min_teikoku); ?>" step="1" min="0" onkeydown="preventEnterSubmit(event)">
            <input type="number" id="max_teikoku" name="max_teikoku" placeholder="最大" value="<?php echo htmlspecialchars($max_teikoku); ?>" step="1" min="0" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label for="tosho">東商:</label>
        <select id="tosho" name="tosho[]" multiple onkeydown="preventEnterSubmit(event)">
            <option value="W" <?php echo in_array('W', $tosho) ? 'selected' : ''; ?>>W</option>
            <option value="X" <?php echo in_array('X', $tosho) ? 'selected' : ''; ?>>X</option>
            <option value="Y" <?php echo in_array('Y', $tosho) ? 'selected' : ''; ?>>Y</option>
            <option value="Z" <?php echo in_array('Z', $tosho) ? 'selected' : ''; ?>>Z</option>
            <option value="D1" <?php echo in_array('D1', $tosho) ? 'selected' : ''; ?>>D1</option>
            <option value="D2" <?php echo in_array('D2', $tosho) ? 'selected' : ''; ?>>D2</option>
            <option value="D3" <?php echo in_array('D3', $tosho) ? 'selected' : ''; ?>>D3</option>
            <option value="D4" <?php echo in_array('D4', $tosho) ? 'selected' : ''; ?>>D4</option>
        </select>
    </div>
    <div class="form-group">
        <label for="memo">メモ:</label>
        <textarea id="memo" name="memo" onkeydown="preventEnterSubmit(event)"><?php echo htmlspecialchars($memo); ?></textarea>
    </div>
    <div class="form-group">
        <label>作成日:</label>
        <div class="range-group">
            <input type="date" id="created_at_start" name="created_at_start" value="<?php echo htmlspecialchars($created_at_start); ?>" onkeydown="preventEnterSubmit(event)">
            <input type="date" id="created_at_end" name="created_at_end" value="<?php echo htmlspecialchars($created_at_end); ?>" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="form-group">
        <label>更新日:</label>
        <div class="range-group">
            <input type="date" id="updated_at_start" name="updated_at_start" value="<?php echo htmlspecialchars($updated_at_start); ?>" onkeydown="preventEnterSubmit(event)">
            <input type="date" id="updated_at_end" name="updated_at_end" value="<?php echo htmlspecialchars($updated_at_end); ?>" onkeydown="preventEnterSubmit(event)">
        </div>
    </div>
    <div class="search-button">
        <input type="submit" value="検索">
        <input type="button" value="全リスト表示" onclick="window.location.href='companies_list.php';">
        <input type="button" value="CSVダウンロード" onclick="exportCSV();">
    </div>
</form>

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

<!-- CSVインポートフォーム -->
<div class="csv-import">
    <form method="POST" enctype="multipart/form-data">
        <label for="csv_file">CSVファイルを選択:</label>
        <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
        <input type="submit" value="インポート">
    </form>
    <a href="companies_list.php?download_new_template=1">新規用CSVテンプレートをダウンロード</a>
    <br>
    <a href="companies_list.php?download_update_template=1">既存変更用CSVテンプレートをダウンロード</a>
</div>

<div class="table-header">
    <a href="add_companies.php">顧客企業追加</a>
</div>

<table>
    <tr>
        <th>会社名</th>
        <th>登記番号</th>
        <th>業種</th>
        <th>住所</th>
        <th>郵便番号</th>
        <th>電話番号</th>
        <th>メール</th>
        <th>代表者名</th>
        <th>決裁者1</th>
        <th>決裁者2</th>
        <th>代表年収(万円)</th>
        <th>代表連絡先</th>
        <th>従業員数</th>
        <th>資本金(万円)</th>
        <th>売上(万円)</th>
        <th>帝国</th>
        <th>東商</th>
        <th>メモ</th>
        <th>作成日</th>
        <th>更新日</th>
        <th>アクション</th>
    </tr>
    <?php
    if ($items) {
        foreach ($items as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['company_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['business_registration_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['industry_type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['address']) . "</td>";
            echo "<td>" . htmlspecialchars($row['postal_code']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['representative_name'] ?? '未設定') . "</td>";
            echo "<td>" . htmlspecialchars($row['decision_maker_1'] ?? '未設定') . "</td>";
            echo "<td>" . htmlspecialchars($row['decision_maker_2'] ?? '未設定') . "</td>";
            echo "<td>" . ($row['representative_income'] !== null ? number_format($row['representative_income'], 0) : '未設定') . "</td>";
            echo "<td>" . htmlspecialchars($row['representative_contact'] ?? '未設定') . "</td>";
            echo "<td>" . ($row['employee_count'] !== null ? htmlspecialchars($row['employee_count']) : '未設定') . "</td>";
            echo "<td>" . ($row['capital'] !== null ? number_format($row['capital'], 0) : '未設定') . "</td>";
            echo "<td>" . ($row['revenue'] !== null ? number_format($row['revenue'], 0) : '未設定') . "</td>";
            echo "<td>" . ($row['teikoku'] !== null ? number_format($row['teikoku'], 0) : '未設定') . "</td>";
            echo "<td>" . htmlspecialchars($row['tosho'] ?? '未設定') . "</td>";
            echo "<td>" . htmlspecialchars($row['memo'] ?? '未設定') . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "<td>" . htmlspecialchars($row['updated_at']) . "</td>";
            echo "<td><a href='edit_companies.php?company_id=" . htmlspecialchars($row['company_id']) . "'>編集</a> | <a href='delete_companies.php?company_id=" . htmlspecialchars($row['company_id']) . "'>削除</a> | <a href='add_orders.php?company_id=" . htmlspecialchars($row['company_id']) . "'>受注追加</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='21'>顧客企業データが見つかりません。</td></tr>";
    }
    ?>
</table>
<a href="add_companies.php">顧客企業追加</a>
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