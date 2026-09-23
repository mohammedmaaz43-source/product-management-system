
<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "Sell Product";
$rootPath = "../";

$message = "";
$error = "";


// =========================================
// CHECK CUSTOMER
// =========================================

$userId = (int) $_SESSION["user_id"];

$userQuery = $conn->prepare("
    SELECT id, username, role
    FROM users
    WHERE id = ?
");

$userQuery->bind_param(
    "i",
    $userId
);

$userQuery->execute();

$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();


// =========================================
// SECURITY CHECK
// =========================================

if (
    !$user ||
    $user["role"] !== "user"
) {

    header("Location: ../login.php");
    exit();

}


// =========================================
// GET CATEGORIES
// =========================================

$categories = $conn->query("
    SELECT id, category_name
    FROM categories
    ORDER BY category_name ASC
");


// =========================================
// GET SUPPLIERS
// =========================================

$suppliers = $conn->query("
    SELECT id, supplier_name
    FROM suppliers
    ORDER BY supplier_name ASC
");


// =========================================
// ADD PRODUCT
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["sell_product"])
) {

    $productName =
        clean($_POST["product_name"]);

    $categoryId =
        (int) $_POST["category_id"];

    $supplierId =
        (int) $_POST["supplier_id"];

    $price =
        (float) $_POST["price"];

    $quantity =
        (int) $_POST["quantity"];

    $description =
        clean($_POST["description"]);


    // =====================================
    // VALIDATION
    // =====================================

    if (
        empty($productName) ||
        $categoryId <= 0 ||
        $supplierId <= 0 ||
        $price <= 0 ||
        $quantity <= 0
    ) {

        $error =
            "Please fill all required fields correctly.";

    } else {


        // =================================
        // IMAGE
        // =================================

        $imageName = "";


        if (
            isset($_FILES["product_image"]) &&
            $_FILES["product_image"]["error"]
            === UPLOAD_ERR_OK
        ) {

            $file =
                $_FILES["product_image"];


            $allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];


            if (
                !in_array(
                    $file["type"],
                    $allowedTypes
                )
            ) {

                $error =
                    "Only JPG, PNG and WEBP images are allowed.";

            } else {


                $extension =
                    strtolower(
                        pathinfo(
                            $file["name"],
                            PATHINFO_EXTENSION
                        )
                    );


                $imageName =
                    time()
                    . "_"
                    . uniqid()
                    . "."
                    . $extension;


                $uploadFolder =
                    "../uploads/products/";


                if (
                    !is_dir(
                        $uploadFolder
                    )
                ) {

                    mkdir(
                        $uploadFolder,
                        0777,
                        true
                    );

                }


                $uploadPath =
                    $uploadFolder
                    . $imageName;


                if (
                    !move_uploaded_file(
                        $file["tmp_name"],
                        $uploadPath
                    )
                ) {

                    $error =
                        "Product image could not be uploaded.";

                }

            }

        }


        // =================================
        // INSERT PRODUCT
        // =================================

        if (empty($error)) {

            $stmt = $conn->prepare("
                INSERT INTO products
                (
                    product_name,
                    category_id,
                    supplier_id,
                    seller_id,
                    price,
                    quantity,
                    description,
                    image
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");


            $stmt->bind_param(
                "siiidiss",
                $productName,
                $categoryId,
                $supplierId,
                $userId,
                $price,
                $quantity,
                $description,
                $imageName
            );


            if ($stmt->execute()) {

                $message =
                    "Product submitted successfully.";

            } else {

                $error =
                    "Product could not be submitted.";

            }


            $stmt->close();

        }

    }

}


// =========================================
// HEADER
// =========================================

include "../includes/header.php";

?>


    <!-- =====================================
         PAGE HEADER
    ====================================== -->

    <section class="customer-page-header">

        <div>

            <span class="hero-label">
                SELL YOUR PRODUCT
            </span>

            <h1>
                Sell a Product
            </h1>

            <p>
                Add your product details and make it available
                in the marketplace.
            </p>

        </div>

    </section>


    <!-- =====================================
         SELL PRODUCT FORM
    ====================================== -->

    <section class="sell-product-section">


        <div class="sell-product-card">


            <div class="sell-product-header">

                <span>
                    PRODUCT INFORMATION
                </span>

                <h2>
                    Add Product Details
                </h2>

                <p>
                    Fill in the information below.
                </p>

            </div>


            <!-- SUCCESS -->

            <?php if (!empty($message)) { ?>

                <div class="success-message">

                    <?php

                    echo htmlspecialchars(
                        $message
                    );

                    ?>

                </div>

            <?php } ?>


            <!-- ERROR -->

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
                id="sellProductForm"
            >


                <!-- PRODUCT NAME -->

                <div class="form-group">

                    <label>
                        Product Name
                    </label>

                    <input
                        type="text"
                        name="product_name"
                        placeholder="Enter product name"
                        required
                    >

                </div>


                <!-- CATEGORY + SUPPLIER -->

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

                            if ($categories) {

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

                            <?php

                                }

                            }

                            ?>

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

                            if ($suppliers) {

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

                            <?php

                                }

                            }

                            ?>

                        </select>

                    </div>


                </div>


                <!-- PRICE + QUANTITY -->

                <div class="form-row">


                    <div class="form-group">

                        <label>
                            Price
                        </label>

                        <input
                            type="number"
                            name="price"
                            min="0.01"
                            step="0.01"
                            placeholder="Enter price"
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
                            min="1"
                            placeholder="Enter quantity"
                            required
                        >

                    </div>


                </div>


                <!-- IMAGE -->

                <div class="form-group">

                    <label>
                        Product Image
                    </label>

                    <input
                        type="file"
                        name="product_image"
                        id="productImage"
                        accept=".jpg,.jpeg,.png,.webp"
                    >


                    <div
                        class="image-preview-container"
                    >

                        <img
                            id="imagePreview"
                            src=""
                            alt="Product Preview"
                            style="display:none;"
                        >

                    </div>

                </div>


                <!-- DESCRIPTION -->

                <div class="form-group">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        placeholder="Describe your product..."
                        rows="5"
                    ></textarea>

                </div>


                <!-- BUTTON -->

                <button
                    type="submit"
                    name="sell_product"
                    class="customer-primary-btn"
                >
                    Submit Product
                </button>


            </form>


        </div>


    </section>


</div>



<script>
document.addEventListener("DOMContentLoaded", function () {

    const sellForm =
        document.getElementById("sellProductForm");

    const loader =
        document.getElementById("sellProductLoader");

    const submitButton =
        document.querySelector(
            '#sellProductForm button[name="sell_product"]'
        );


    if (sellForm && loader) {

        sellForm.addEventListener(
            "submit",
            function () {

                /*
                 * IMPORTANT:
                 * Do NOT use preventDefault().
                 * PHP form submission must continue.
                 */

                loader.classList.add("active");


                if (submitButton) {

                    submitButton.disabled = true;

                    submitButton.innerHTML =
                        "Processing...";

                }

            }
        );

    }

});
</script>


<?php

include "../includes/footer.php";

?>