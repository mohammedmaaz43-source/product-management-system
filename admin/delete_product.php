<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

checkLogin();

if (isset($_GET["id"])) {

    $id = (int) $_GET["id"];

    $result = $conn->query(
        "SELECT image FROM products WHERE id = $id"
    );

    if ($result && $result->num_rows == 1) {

        $product = $result->fetch_assoc();

        if (!empty($product["image"])) {

            $imagePath =
                "../uploads/" .
                $product["image"];

            if (file_exists($imagePath)) {

                unlink($imagePath);
            }
        }

        $conn->query(
            "DELETE FROM products WHERE id = $id"
        );
    }
}

header("Location: products.php");

exit();

?>