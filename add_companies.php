<?php
include_once 'db_connection.php';
include_once 'header.php';

$page_title = "顧客企業追加";

try {
    $conn = getDBConnection();
    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<form method="POST" action="process_add_companies.php" onsubmit="return validateForm()">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <div class="form-group">
        <label for="company_name" class="required">会社名:</label>
        <input type="text" id="company_name" name="company_name" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="business_registration_number">登記番号:</label>
        <input type="text" id="business_registration_number" name="business_registration_number" maxlength="13" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="industry_type">業種:</label>
        <input type="text" id="industry_type" name="industry_type" maxlength="50" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="address" class="required">住所:</label>
        <input type="text" id="address" name="address" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="postal_code">郵便番号:</label>
        <input type="text" id="postal_code" name="postal_code" maxlength="8" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="phone_number">電話番号:</label>
        <input type="text" id="phone_number" name="phone_number" maxlength="20" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="email">メール:</label>
        <input type="email" id="email" name="email" maxlength="100" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="representative_name">代表者名:</label>
        <input type="text" id="representative_name" name="representative_name" maxlength="100" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="decision_maker_1">決裁者1:</label>
        <input type="text" id="decision_maker_1" name="decision_maker_1" maxlength="100" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="decision_maker_2">決裁者2:</label>
        <input type="text" id="decision_maker_2" name="decision_maker_2" maxlength="100" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="representative_income">代表年収 (万円):</label>
        <input type="number" id="representative_income" name="representative_income" step="1" min="0" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="representative_contact">代表連絡先:</label>
        <input type="text" id="representative_contact" name="representative_contact" maxlength="50" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="employee_count">従業員数:</label>
        <input type="number" id="employee_count" name="employee_count" min="0" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="capital">資本金 (万円):</label>
        <input type="number" id="capital" name="capital" step="1" min="0" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="revenue">売上 (万円):</label>
        <input type="number" id="revenue" name="revenue" step="1" min="0" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="teikoku">帝国:</label>
        <input type="number" id="teikoku" name="teikoku" step="1" min="0" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="tosho">東商:</label>
        <select id="tosho" name="tosho" onkeydown="preventEnterSubmit(event)">
            <option value=""> </option>
            <option value="W">W</option>
            <option value="X">X</option>
            <option value="Y">Y</option>
            <option value="Z">Z</option>
            <option value="D1">D1</option>
            <option value="D2">D2</option>
            <option value="D3">D3</option>
            <option value="D4">D4</option>
        </select>
    </div>
    <div class="form-group">
        <label for="memo">メモ:</label>
        <textarea id="memo" name="memo" rows="4" cols="50" onkeydown="preventEnterSubmit(event)"></textarea>
    </div>
    <div style="margin-top: 10px;">
        <input type="submit" value="追加" style="margin-right: 10px;">
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
    echo "<p class='error'>エラーが発生しました。</p>";
}
?>