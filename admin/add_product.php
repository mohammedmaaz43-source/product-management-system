
<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "Add Product";
$rootPath = "../";

$message = "";
$error = "";
$productAdded = false;

$categories = $conn->query(
    "SELECT * FROM categories ORDER BY category_name ASC"
);

$suppliers = $conn->query(
    "SELECT * FROM suppliers ORDER BY supplier_name ASC"
);


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $productName = clean($_POST["product_name"]);
    $categoryId = (int) $_POST["category_id"];
    $supplierId = (int) $_POST["supplier_id"];
    $price = (float) $_POST["price"];
    $quantity = (int) $_POST["quantity"];
    $description = clean($_POST["description"]);

    $image = "";

    if (!empty($_FILES["image"]["name"])) {

        $image = uploadProductImage(
            $_FILES["image"]
        );

    }


    if (empty($productName)) {

        $error = "Product name is required.";

    } elseif ($categoryId <= 0) {

        $error = "Please select a category.";

    } elseif ($supplierId <= 0) {

        $error = "Please select a supplier.";

    } else {

        $stmt = $conn->prepare("

            INSERT INTO products
            (
                product_name,
                category_id,
                supplier_id,
                price,
                quantity,
                description,
                image
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)

        ");


        $stmt->bind_param(

            "siidiss",

            $productName,
            $categoryId,
            $supplierId,
            $price,
            $quantity,
            $description,
            $image

        );


        if ($stmt->execute()) {

            $productAdded = true;

        } else {

            $error =
                "Product could not be added.";

        }

        $stmt->close();

    }

}


include "../includes/header.php";

?>


<?php if ($productAdded) { ?>


<!-- =========================================
     PRODUCT SCANNER SUCCESS SCREEN
========================================= -->

<div
    id="productScanner"
    class="product-scanner-overlay"
>


    <div class="product-scanner-card">


        <!-- SCANNING CONTENT -->

        <div
            id="scannerContent"
            class="scanner-content"
        >


            <div class="scanner-title">

                SCANNING PRODUCT...

            </div>


            <div class="scanner-box">


                <div class="scanner-corners">

                    <span class="corner top-left"></span>

                    <span class="corner top-right"></span>

                    <span class="corner bottom-left"></span>

                    <span class="corner bottom-right"></span>

                </div>


                <div class="product-scan-icon">

                    📦

                </div>


                <div class="scan-line"></div>


            </div>


            <div class="scanner-status">

                <span
                    id="scannerStatus"
                >

                    VERIFYING DETAILS...

                </span>

            </div>


            <div class="scanner-progress">

                <div
                    id="scannerProgress"
                    class="scanner-progress-bar"
                ></div>

            </div>


        </div>



        <!-- SUCCESS CONTENT -->

        <div
            id="successContent"
            class="product-success-content"
        >


            <div class="success-check">

                ✓

            </div>


            <h2>

                Product Added Successfully

            </h2>


            <p>

                Your product has been added
                to the inventory.

            </p>


        </div>


    </div>


</div>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const progress =
            document.getElementById(
                "scannerProgress"
            );

        const status =
            document.getElementById(
                "scannerStatus"
            );

        const scannerContent =
            document.getElementById(
                "scannerContent"
            );

        const successContent =
            document.getElementById(
                "successContent"
            );


        setTimeout(
            function () {

                status.innerHTML =
                    "VERIFYING PRODUCT DATA...";

                progress.style.width =
                    "50%";

            },
            900
        );


        setTimeout(
            function () {

                status.innerHTML =
                    "SAVING TO INVENTORY...";

                progress.style.width =
                    "100%";

            },
            1800
        );


        setTimeout(
            function () {

                scannerContent.style.display =
                    "none";

                successContent.classList.add(
                    "show-success"
                );

            },
            2600
        );


        setTimeout(
            function () {

                window.location.href =
                    "products.php";

            },
            4200
        );

    }
);

</script>


<?php } else { ?>


<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>


    <main class="admin-content">


        <div class="page-header">

            <h1>

                Add Product

            </h1>


            <a
                href="products.php"
                class="btn"
            >

                Back

            </a>

        </div>



        <div class="form-container">


            <?php if (!empty($error)) { ?>


                <div class="error-message">

                    <?php
                    echo htmlspecialchars(
                        $error
                    );
                    ?>

                </div>


            <?php } ?>



            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <div class="form-group">

                    <label>

                        Product Name

                    </label>


                    <input
                        type="text"
                        name="product_name"
                        required
                    >

                </div>



                <div class="form-row">


                    <div class="form-group">

                        <label>

                            Category

                        </label>


                        <select
                            name="category_id"
                            required
                        >

                            <option value="">

                                Select Category

                            </option>


                            <?php
                            while (
                                $category =
                                $categories->fetch_assoc()
                            ) {
                            ?>


                                <option
                                    value="<?php
                                    echo $category["id"];
                                    ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $category[
                                            "category_name"
                                        ]
                                    );
                                    ?>

                                </option>


                            <?php } ?>


                        </select>

                    </div>



                    <div class="form-group">

                        <label>

                            Supplier

                        </label>


                        <select
                            name="supplier_id"
                            required
                        >

                            <option value="">

                                Select Supplier

                            </option>


                            <?php
                            while (
                                $supplier =
                                $suppliers->fetch_assoc()
                            ) {
                            ?>


                                <option
                                    value="<?php
                                    echo $supplier["id"];
                                    ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $supplier[
                                            "supplier_name"
                                        ]
                                    );
                                    ?>

                                </option>


                            <?php } ?>


                        </select>

                    </div>


                </div>



                <div class="form-row">


                    <div class="form-group">

                        <label>

                            Price

                        </label>


                        <input
                            type="number"
                            name="price"
                            step="0.01"
                            required
                        >

                    </div>



                    <div class="form-group">

                        <label>

                            Quantity

                        </label>


                        <input
                            type="number"
                            name="quantity"
                            required
                        >

                    </div>


                </div>



                <div class="form-group">

                    <label>

                        Description

                    </label>


                    <textarea
                        name="description"
                    ></textarea>

                </div>



                <div class="form-group">

                    <label>

                        Product Image

                    </label>


                    <input
                        type="file"
                        name="image"
                        id="productImage"
                    >


                    <br>


                    <img
                        id="imagePreview"
                        style="
                            display:none;
                            width:150px;
                        "
                        alt="Preview"
                    >


                </div>



                <button
                    type="submit"
                    class="btn"
                >

                    Add Product

                </button>


            </form>


        </div>


    </main>


</div>


<?php } ?>


<?php include "../includes/footer.php"; ?>