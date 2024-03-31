<?php
include("inc/config.php");

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    $data = array();

    $statement_sizes = $pdo->prepare("
        SELECT ts.size_id, ts.size_name 
        FROM tbl_product p
        LEFT JOIN tbl_product_size ps ON p.p_id = ps.p_id
        LEFT JOIN tbl_size ts ON ps.size_id = ts.size_id 
        WHERE p.p_id = ?
    ");
    $statement_sizes->execute([$product_id]);
    $result_sizes = $statement_sizes->fetchAll(PDO::FETCH_ASSOC);

    $statement_colors = $pdo->prepare("
        SELECT tc.color_id, tc.color_name 
        FROM tbl_product p
        LEFT JOIN tbl_product_color pc ON p.p_id = pc.p_id
        LEFT JOIN tbl_color tc ON pc.color_id = tc.color_id 
        WHERE p.p_id = ?
    ");
    $statement_colors->execute([$product_id]);
    $result_colors = $statement_colors->fetchAll(PDO::FETCH_ASSOC);

    $data['sizes'] = $result_sizes;
    $data['colors'] = $result_colors;

    header('Content-type: application/json');
    echo json_encode($data);
    exit;
}

?>
