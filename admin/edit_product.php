<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

if (!isset($_GET["id"])) {

    header("Location: products.php");
    exit();
}

$id = (int) $_GET["id"];

$result = $conn->query(
    "SELECT * FROM products WHERE id = $id"
);

if (!$result || $result->num_rows == 0) {

    header("Location: products.php");
    exit();
}

$product = $result->fetch_assoc();

$categories = $conn->query(
    "SELECT * FROM categories ORDER BY category_name ASC"
);

$suppliers = $conn->query(
    "SELECT * FROM suppliers ORDER BY supplier_name ASC"
);

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $productName = clean($_POST["product_name"]);
    $categoryId = (int) $_POST["category_id"];
    $supplierId = (int) $_POST["supplier_id"];
    $price = (float) $_POST["price"];
    $quantity = (int) $_POST["quantity"];
    $description = clean($_POST["description"]);

    $image = $product["image"];

    if (!empty($_FILES["image"]["name"])) {

        $newImage =
            uploadProductImage($_FILES["image"]);

        if (!empty($newImage)) {
            $image = $newImage;
        }
    }

    $stmt = $conn->prepare("
        UPDATE products
        SET
            product_name = ?,
            category_id = ?,
            supplier_id = ?,
            price = ?,
            quantity = ?,
            description = ?,
            image = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "siidissi",
        $productName,
        $categoryId,
        $supplierId,
        $price,
        $quantity,
        $description,
        $image,
        $id
    );

    if ($stmt->execute()) {

        header("Location: products.php");
        exit();

    } else {

        $error = "Product could not be updated.";
    }

}

$pageTitle = "Edit Product";
$rootPath = "../";

include "../includes/header.php";

?>

<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="admin-content">

        <div class="page-header">

            <h1>Edit Product</h1>

            <a href="products.php" class="btn">
                Back
            </a>

        </div>


        <div class="form-container">

            <?php if (!empty($error)) { ?>

                <div class="error-message">
                    <?php echo $error; ?>
                </div>

            <?php } ?>


            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <div class="form-group">

                    <label>Product Name</label>

                    <input
                        type="text"
                        name="product_name"
                        value="<?php echo htmlspecialchars($product["product_name"]); ?>"
                        required
                    >

                </div>


                <div class="form-row">

                    <div class="form-group">

                        <label>Category</label>

                        <select name="category_id">

                            <?php while ($category = $categories->fetch_assoc()) { ?>

                                <option
                                    value="<?php echo $category["id"]; ?>"
                                    <?php
                                    if (
                                        $category["id"] ==
                                        $product["category_id"]
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $category["category_name"]
                                    );
                                    ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>Supplier</label>

                        <select name="supplier_id">

                            <?php while ($supplier = $suppliers->fetch_assoc()) { ?>

                                <option
                                    value="<?php echo $supplier["id"]; ?>"
                                    <?php
                                    if (
                                        $supplier["id"] ==
                                        $product["supplier_id"]
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $supplier["supplier_name"]
                                    );
                                    ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                </div>


                <div class="form-row">

                    <div class="form-group">

                        <label>Price</label>

                        <input
                            type="number"
                            name="price"
                            step="0.01"
                            value="<?php echo $product["price"]; ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Quantity</label>

                        <input
                            type="number"
                            name="quantity"
                            value="<?php echo $product["quantity"]; ?>"
                            required
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label>Description</label>

                    <textarea name="description"><?php
                        echo htmlspecialchars(
                            $product["description"]
                        );
                    ?></textarea>

                </div>


                <div class="form-group">

                    <label>Change Image</label>

                    <input
                        type="file"
                        name="image"
                        id="productImage"
                    >

                    <br>

                    <?php
                    $imagePath = !empty($product["image"])
                        ? "../uploads/" . $product["image"]
                        : "../images/default-product.png";
                    ?>

                    <img
                        id="imagePreview"
                        src="<?php echo $imagePath; ?>"
                        style="width:150px;"
                        alt="Product"
                    >

                </div>


                <button
                    type="submit"
                    class="btn"
                >
                    Update Product
                </button>

            </form>

        </div>

    </main>

</div>

<?php include "../includes/footer.php"; ?>