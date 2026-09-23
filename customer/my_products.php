<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "My Listings";
$rootPath = "../";

$message = "";
$error = "";


// =========================================
// CUSTOMER
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


if (
    !$user ||
    $user["role"] !== "user"
) {

    header("Location: ../login.php");
    exit();
}


// =========================================
// DELETE OWN PRODUCT
// =========================================

if (isset($_GET["delete"])) {

    $productId = (int) $_GET["delete"];


    $deleteStmt = $conn->prepare("
        DELETE FROM products
        WHERE id = ?
        AND seller_id = ?
    ");


    $deleteStmt->bind_param(
        "ii",
        $productId,
        $userId
    );


    if ($deleteStmt->execute()) {

        $message =
            "Product removed successfully.";

    } else {

        $error =
            "Product could not be removed.";
    }


    $deleteStmt->close();
}


// =========================================
// GET CUSTOMER PRODUCTS
// =========================================

$productQuery = $conn->prepare("
    SELECT
        products.id,
        products.product_name,
        products.price,
        products.quantity,
        products.description,
        products.image,
        products.created_at,

        categories.category_name,
        suppliers.supplier_name

    FROM products

    LEFT JOIN categories
        ON products.category_id =
           categories.id

    LEFT JOIN suppliers
        ON products.supplier_id =
           suppliers.id

    WHERE products.seller_id = ?

    ORDER BY products.id DESC
");


$productQuery->bind_param(
    "i",
    $userId
);

$productQuery->execute();

$products =
    $productQuery->get_result();


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
                MY LISTINGS
            </span>

            <h1>
                My Products
            </h1>

            <p>
                Manage the products you have added
                to the marketplace.
            </p>

        </div>


        <a
            href="sell_product.php"
            class="customer-primary-btn"
        >
            + Sell New Product
        </a>

    </section>



    <!-- =====================================
         MESSAGES
    ====================================== -->

    <?php if (!empty($message)) { ?>

        <div class="success-message">

            <?php

            echo htmlspecialchars(
                $message
            );

            ?>

        </div>

    <?php } ?>


    <?php if (!empty($error)) { ?>

        <div class="error-message">

            <?php

            echo htmlspecialchars(
                $error
            );

            ?>

        </div>

    <?php } ?>



    <!-- =====================================
         PRODUCT LIST
    ====================================== -->

    <section class="customer-section">


        <div class="section-title">

            <div>

                <span>
                    YOUR PRODUCTS
                </span>

                <h2>
                    Product Listings
                </h2>

            </div>


            <p>

                <?php

                echo $products
                    ? $products->num_rows
                    : 0;

                ?>

                Products

            </p>

        </div>



        <?php

        if (
            $products &&
            $products->num_rows > 0
        ) {

        ?>


            <div class="customer-products-grid">


                <?php

                while (
                    $product =
                    $products->fetch_assoc()
                ) {

                ?>


                    <div class="customer-product-card">


                        <!-- IMAGE -->

                        <div class="customer-product-image">

                            <?php

                            if (
                                !empty(
                                    $product["image"]
                                )
                            ) {

                            ?>

                                <img
                                    src="../uploads/products/<?php
                                    echo htmlspecialchars(
                                        $product["image"]
                                    );
                                    ?>"
                                    alt="<?php
                                    echo htmlspecialchars(
                                        $product["product_name"]
                                    );
                                    ?>"
                                >

                            <?php

                            } else {

                            ?>

                                <div class="no-product-image">
                                    No Image
                                </div>

                            <?php

                            }

                            ?>

                        </div>



                        <!-- PRODUCT INFORMATION -->

                        <div class="customer-product-content">


                            <span class="product-category">

                                <?php

                                echo htmlspecialchars(
                                    $product[
                                        "category_name"
                                    ]
                                    ??
                                    "Uncategorized"
                                );

                                ?>

                            </span>


                            <h3>

                                <?php

                                echo htmlspecialchars(
                                    $product[
                                        "product_name"
                                    ]
                                );

                                ?>

                            </h3>


                            <p class="product-price">

                                ₹<?php

                                echo number_format(
                                    (float)
                                    $product["price"],
                                    2
                                );

                                ?>

                            </p>


                            <div class="product-stock">

                                Stock:

                                <strong>

                                    <?php
                                    echo $product[
                                        "quantity"
                                    ];
                                    ?>

                                </strong>

                            </div>


                            <div class="product-supplier">

                                Supplier:

                                <?php

                                echo htmlspecialchars(
                                    $product[
                                        "supplier_name"
                                    ]
                                    ??
                                    "Not specified"
                                );

                                ?>

                            </div>



                            <!-- ACTIONS -->

                            <div class="product-actions">


                                <a
                                    href="product_details.php?id=<?php
                                    echo $product["id"];
                                    ?>"
                                    class="customer-secondary-btn"
                                >
                                    View
                                </a>


                                <a
                                    href="my_products.php?delete=<?php
                                    echo $product["id"];
                                    ?>"
                                    class="btn-delete"
                                    onclick="return confirm('Are you sure you want to remove this product?');"
                                >
                                    Remove
                                </a>


                            </div>


                        </div>


                    </div>


                <?php

                }

                ?>


            </div>


        <?php

        } else {

        ?>


            <!-- =================================
                 EMPTY STATE
            ================================== -->

            <div class="empty-orders">

                <div class="empty-icon">
                    📦
                </div>


                <h2>
                    No Products Listed
                </h2>


                <p>
                    You haven't listed any products yet.
                    Start selling your product on the marketplace.
                </p>


                <a
                    href="sell_product.php"
                    class="customer-primary-btn"
                >
                    + Sell Your Product
                </a>

            </div>


        <?php

        }

        ?>


    </section>


</div>


<?php

include "../includes/footer.php";

?>