<?php
include_once 'db_connection.php';
ob_start();

try {
    $conn = getDBConnection();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('不正なリクエストです');

    $id = $_POST['id'] ?? '';
    $customer_name = $_POST['customer_name'] ?? '';
    $customer_type = $_POST['customer_type'] ?? '';
    $order_date = $_POST['order_date'] ?? '';
    $monthly_fee = $_POST['monthly_fee'] ?? '';
    $total_payments = $_POST['total_payments'] ?? '';
    $negotiation_status = !empty($_POST['negotiation_status']) ? $_POST['negotiation_status'] : null;
    $construction_status = !empty($_POST['construction_status']) ? $_POST['construction_status'] : null;
    $credit_status = !empty($_POST['credit_status']) ? $_POST['credit_status'] : null;
    $document_status = !empty($_POST['document_status']) ? $_POST['document_status'] : null;
    $rewrite_status = !empty($_POST['rewrite_status']) ? $_POST['rewrite_status'] : null;
    $seal_certificate_status = !empty($_POST['seal_certificate_status']) ? $_POST['seal_certificate_status'] : null;
    $shipping_status = !empty($_POST['shipping_status']) ? $_POST['shipping_status'] : null;
    $memo = !empty($_POST['memo']) ? $_POST['memo'] : null;
    $sales_rep_id = !empty($_POST['sales_rep_id']) ? $_POST['sales_rep_id'] : null;
    $sales_rep_id_2 = !empty($_POST['sales_rep_id_2']) ? $_POST['sales_rep_id_2'] : null;
    $sales_rep_id_3 = !empty($_POST['sales_rep_id_3']) ? $_POST['sales_rep_id_3'] : null;
    $sales_rep_id_4 = !empty($_POST['sales_rep_id_4']) ? $_POST['sales_rep_id_4'] : null;
    $appointment_rep_id_1 = !empty($_POST['appointment_rep_id_1']) ? $_POST['appointment_rep_id_1'] : null;
    $appointment_rep_id_2 = !empty($_POST['appointment_rep_id_2']) ? $_POST['appointment_rep_id_2'] : null;
    $rewriting_person_id = !empty($_POST['rewriting_person_id']) ? $_POST['rewriting_person_id'] : null;

    if (empty($id)) throw new Exception('受注IDが指定されていません');

    // バリデーション追加
    if ($construction_status && !in_array($construction_status, ['待ち', '与信待ち', '残あり', '完了', '回収待ち', '回収完了'])) {
        throw new Exception('無効な工事ステータスです: ' . $construction_status);
    }

    error_log("POST data: " . print_r($_POST, true));
    error_log("Prepared values: id=$id, sales_rep_id_3=" . ($sales_rep_id_3 === null ? 'NULL' : $sales_rep_id_3));

    $stmt = $conn->prepare("UPDATE orders SET 
        customer_name = ?, customer_type = ?, order_date = ?, monthly_fee = ?, total_payments = ?, 
        negotiation_status = ?, construction_status = ?, credit_status = ?, document_status = ?, 
        rewrite_status = ?, seal_certificate_status = ?, shipping_status = ?, memo = ?, 
        sales_rep_id = ?, sales_rep_id_2 = ?, sales_rep_id_3 = ?, sales_rep_id_4 = ?, 
        appointment_rep_id_1 = ?, appointment_rep_id_2 = ?, rewriting_person_id = ? 
        WHERE id = ?");
    $stmt->execute([
        $customer_name, $customer_type, $order_date, $monthly_fee, $total_payments, 
        $negotiation_status, $construction_status, $credit_status, $document_status, 
        $rewrite_status, $seal_certificate_status, $shipping_status, $memo, 
        $sales_rep_id, $sales_rep_id_2, $sales_rep_id_3, $sales_rep_id_4, 
        $appointment_rep_id_1, $appointment_rep_id_2, $rewriting_person_id, $id
    ]);

    error_log("Order updated successfully: ID = $id");
    ob_end_clean();
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Location: orders_list.php?status=success&message=受注が更新されました");
    exit;
} catch (Exception $e) {
    ob_end_clean();
    error_log("Error in process_edit_orders.php: " . $e->getMessage());
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Location: edit_orders.php?id=" . urlencode($id) . "&status=error&message=" . urlencode($e->getMessage()));
    exit;
}
?>