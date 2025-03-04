<?php
$page_title = "リース会社追加";
include_once 'header.php';
?>
<form method="POST" action="process_add_lease_providers.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <div class="form-group">
        <label for="provider_name" class="required">会社名:</label>
        <input type="text" id="provider_name" name="provider_name" required>
    </div>
    <div class="form-group">
        <label for="business_registration_number">登記番号:</label>
        <input type="text" id="business_registration_number" name="business_registration_number">
    </div>
    <div class="form-group">
        <label for="industry_type">業種:</label>
        <input type="text" id="industry_type" name="industry_type">
    </div>
    <div class="form-group">
        <label for="address" class="required">住所:</label>
        <textarea id="address" name="address" required></textarea>
    </div>
    <div class="form-group">
        <label for="postal_code">郵便番号:</label>
        <input type="text" id="postal_code" name="postal_code" pattern="\d{3}-?\d{4}">
    </div>
    <div class="form-group">
        <label for="phone_number">電話番号:</label>
        <input type="tel" id="phone_number" name="phone_number" pattern="\d{2,4}-?\d{2,4}-?\d{3,4}">
    </div>
    <div class="form-group">
        <label for="email">メールアドレス:</label>
        <input type="email" id="email" name="email">
    </div>
    <div style="margin-top: 10px;">
        <input type="submit" value="追加" style="margin-right: 10px;">
        <input type="button" value="キャンセル" onclick="window.location.href='lease_providers_list.php';">
    </div>
</form>
</body>
</html>