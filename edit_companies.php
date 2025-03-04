<?php
include_once 'db_connection.php';
include_once 'header.php';

$page_title = "顧客企業編集";

try {
    $conn = getDBConnection();
    if (!isset($_GET['company_id'])) {
        throw new Exception('会社IDが指定されていません');
    }
    $company_id = $_GET['company_id'];

    $stmt = $conn->prepare("SELECT * FROM companies WHERE company_id = ?");
    $stmt->execute([$company_id]);
    $company = $stmt->fetch();

    if (!$company) {
        throw new Exception('指定された会社が見つかりません');
    }

    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<form method="POST" action="process_edit_companies.php" onsubmit="return validateForm()">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($company['company_id']); ?>">
    <div class="form-group">
        <label for="company_name" class="required">会社名:</label>
        <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company['company_name']); ?>" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="business_registration_number">登記番号:</label>
        <input type="text" id="business_registration_number" name="business_registration_number" value="<?php echo htmlspecialchars($company['business_registration_number'] ?? ''); ?>" maxlength="13" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="industry_type">業種:</label>
        <input type="text" id="industry_type" name="industry_type" value="<?php echo htmlspecialchars($company['industry_type'] ?? ''); ?>" maxlength="50" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="address" class="required">住所:</label>
        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($company['address']); ?>" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="postal_code">郵便番号:</label>
        <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($company['postal_code'] ?? ''); ?>" maxlength="8" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="phone_number">電話番号:</label>
        <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($company['phone_number'] ?? ''); ?>" maxlength="20" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="email">メール:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>" maxlength="100" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="representative_name">代表者名:</label>
        <input type="text" id="representative_name" name="representative_name" value="<?php echo htmlspecialchars($company['representative_name'] ?? ''); ?>" maxlength="100" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="decision_maker_1">決裁者1:</label>
        <input type="text" id="decision_maker_1" name="decision_maker_1" value="<?php echo htmlspecialchars($company['decision_maker_1'] ?? ''); ?>" maxlength="100" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="decision_maker_2">決裁者2:</label>
        <input type="text" id="decision_maker_2" name="decision_maker_2" value="<?php echo htmlspecialchars($company['decision_maker_2'] ?? ''); ?>" maxlength="100" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="representative_income">代表年収 (万円):</label>
        <input type="number" id="representative_income" name="representative_income" step="1" min="0" value="<?php echo $company['representative_income'] !== null ? number_format($company['representative_income'], 0) : ''; ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="representative_contact">代表連絡先:</label>
        <input type="text" id="representative_contact" name="representative_contact" value="<?php echo htmlspecialchars($company['representative_contact'] ?? ''); ?>" maxlength="50" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="employee_count">従業員数:</label>
        <input type="number" id="employee_count" name="employee_count" min="0" value="<?php echo htmlspecialchars($company['employee_count'] ?? ''); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="capital">資本金 (万円):</label>
        <input type="number" id="capital" name="capital" step="1" min="0" value="<?php echo $company['capital'] !== null ? number_format($company['capital'], 0) : ''; ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="revenue">売上 (万円):</label>
        <input type="number" id="revenue" name="revenue" step="1" min="0" value="<?php echo $company['revenue'] !== null ? number_format($company['revenue'], 0) : ''; ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="teikoku">帝国:</label>
        <input type="number" id="teikoku" name="teikoku" step="1" min="0" value="<?php echo $company['teikoku'] !== null ? number_format($company['teikoku'], 0) : ''; ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="tosho">東商:</label>
        <select id="tosho" name="tosho" onkeydown="preventEnterSubmit(event)">
            <option value="" <?php echo ($company['tosho'] === '') ? 'selected' : ''; ?>> </option>
            <option value="W" <?php echo ($company['tosho'] === 'W') ? 'selected' : ''; ?>>W</option>
            <option value="X" <?php echo ($company['tosho'] === 'X') ? 'selected' : ''; ?>>X</option>
            <option value="Y" <?php echo ($company['tosho'] === 'Y') ? 'selected' : ''; ?>>Y</option>
            <option value="Z" <?php echo ($company['tosho'] === 'Z') ? 'selected' : ''; ?>>Z</option>
            <option value="D1" <?php echo ($company['tosho'] === 'D1') ? 'selected' : ''; ?>>D1</option>
            <option value="D2" <?php echo ($company['tosho'] === 'D2') ? 'selected' : ''; ?>>D2</option>
            <option value="D3" <?php echo ($company['tosho'] === 'D3') ? 'selected' : ''; ?>>D3</option>
            <option value="D4" <?php echo ($company['tosho'] === 'D4') ? 'selected' : ''; ?>>D4</option>
        </select>
    </div>
    <div class="form-group">
        <label for="memo">メモ:</label>
        <textarea id="memo" name="memo" rows="4" cols="50" onkeydown="preventEnterSubmit(event)"><?php echo htmlspecialchars($company['memo'] ?? ''); ?></textarea>
    </div>
    <div style="margin-top: 10px;">
        <input type="submit" value="更新" style="margin-right: 10px;">
        <input type="button" value="キャンセル" onclick="window.location.href='companies_list.php';">
    </div>
</form>

<script>
function preventEnterSubmit(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
}

function validateForm() {
    // 必要に応じて追加のバリデーションをここに
    return true;
}
</script>
</body>
</html>
<?php
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>