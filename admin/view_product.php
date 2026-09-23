<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

checkLogin();

if (!isset($_GET["id"])) {

    header("Location: products.php");
    exit();
}

$id = (int) $_GET["id"];


/* Get Product Details */

$stmt = $conn->prepare("
    SELECT
        products.*,
        categories.category_name,
        suppliers.supplier_name,
        suppliers.contact_number,
        suppliers.email,
        suppliers.address

    FROM products

    LEFT JOIN categories
    ON products.category_id = categories.id

    LEFT JOIN suppliers
    ON products.supplier_id = suppliers.id

    WHERE products.id = ?
");

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows == 0) {

    header("Location: products.php");
    exit();
}


$product = $result->fetch_assoc();


$pageTitle = "Product Details";
$rootPath = "../";

include "../includes/header.php";

?>


<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>


    <main class="admin-content">


        <!-- Page Header -->

        <div class="page-header">

            <div>

                <h1>
                    Product Details
                </h1>

                <p>
                    View complete information about this product.
                </p>

            </div>


            <div class="page-header-actions">

                <a
                    href="edit_product.php?id=<?php echo $product["id"]; ?>"
                    class="btn"
                >
                    Edit Product
                </a>


                <a
                    href="products.php"
                    class="btn-reset"
                >
                    Back
                </a>

            </div>

        </div>



        <!-- Product Details -->

        <div class="product-details-card">


            <!-- Product Image -->

            <div class="product-details-image">

                <?php

                $image = !empty($product["image"])
                    ? "../uploads/" . $product["image"]
                    : "../images/default-product.png";

                ?>

                <img
                    src="<?php echo $image; ?>"
                    alt="Product Image"
                >

            </div>



            <!-- Product Information -->

            <div class="product-details-info">


                <div class="product-details-title">

                    <h2>

                        <?php
                        echo htmlspecialchars(
                            $product["product_name"]
                        );
                        ?>

                    </h2>


                    <?php

                    if ($product["quantity"] == 0) {

                        echo '<span class="out-stock">
                            Out of Stock
                        </span>';

                    } elseif ($product["quantity"] <= 5) {

                        echo '<span class="low-stock">
                            Low Stock
                        </span>';

                    } else {

                        echo '<span class="in-stock">
                            Available
                        </span>';
                    }

                    ?>

                </div>



                <div class="product-info-grid">


                    <div class="product-info-item">

                        <span class="info-label">
                            Product ID
                        </span>

                        <strong>
                            #<?php echo $product["id"]; ?>
                        </strong>

                    </div>



                    <div class="product-info-item">

                        <span class="info-label">
                            Category
                        </span>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $product["category_name"] ?? "N/A"
                            );
                            ?>

                        </strong>

                    </div>



                    <div class="product-info-item">

                        <span class="info-label">
                            Supplier
                        </span>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $product["supplier_name"] ?? "N/A"
                            );
                            ?>

                        </strong>

                    </div>



                    <div class="product-info-item">

                        <span class="info-label">
                            Price
                        </span>

                        <strong class="product-price">

                            ₹<?php
                            echo number_format(
                                $product["price"],
                                2
                            );
                            ?>

                        </strong>

                    </div>



                    <div class="product-info-item">

                        <span class="info-label">
                            Quantity
                        </span>

                        <strong>
                            <?php
                            echo $product["quantity"];
                            ?>
                        </strong>

                    </div>



                    <div class="product-info-item">

                        <span class="info-label">
                            Added On
                        </span>

                        <strong>

                            <?php

                            if (!empty($product["created_at"])) {

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $product["created_at"]
                                    )
                                );

                            } else {

                                echo "N/A";
                            }

                            ?>

                        </strong>

                    </div>


                </div>



                <!-- Description -->

                <div class="product-description">

                    <span class="info-label">
                        Description
                    </span>


                    <p>

                        <?php

                        echo !empty(
                            $product["description"]
                        )

                        ? nl2br(
                            htmlspecialchars(
                                $product["description"]
                            )
                        )

                        : "No description available.";

                        ?>

                    </p>

                </div>


            </div>


        </div>



        <!-- Supplier Information -->

        <div class="supplier-details-card">


            <div class="section-title">

                <h2>
                    Supplier Information
                </h2>

                <p>
                    Contact details of the product supplier.
                </p>

            </div>


            <div class="supplier-info-grid">


                <div class="supplier-info-item">

                    <span>
                        Supplier Name
                    </span>

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $product["supplier_name"] ?? "N/A"
                        );
                        ?>

                    </strong>

                </div>



                <div class="supplier-info-item">

                    <span>
                        Contact Number
                    </span>

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $product["contact_number"] ?? "N/A"
                        );
                        ?>

                    </strong>

                </div>



                <div class="supplier-info-item">

                    <span>
                        Email
                    </span>

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $product["email"] ?? "N/A"
                        );
                        ?>

                    </strong>

                </div>



                <div class="supplier-info-item">

                    <span>
                        Address
                    </span>

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $product["address"] ?? "N/A"
                        );
                        ?>

                    </strong>

                </div>


            </div>


        </div>


    </main>

</div>


<?php include "../includes/footer.php"; ?>