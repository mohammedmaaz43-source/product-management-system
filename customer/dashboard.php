
<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";


checkLogin();


$pageTitle =
    "Customer Dashboard";


$rootPath =
    "../";


$isCustomerPageCustom =
    true;

$currentPage = "customer_dashboard.php";


/* =========================================
   GET LOGGED-IN CUSTOMER
========================================= */

$userId =
    (int) $_SESSION["user_id"];


$userQuery =
    $conn->prepare("
        SELECT
            id,
            username,
            role
        FROM users
        WHERE id = ?
    ");


$userQuery->bind_param(
    "i",
    $userId
);


$userQuery->execute();


$userResult =
    $userQuery->get_result();


$user =
    $userResult->fetch_assoc();


/* =========================================
   SECURITY CHECK
========================================= */

if (
    !$user ||
    $user["role"] !== "user"
) {

    header(
        "Location: ../login.php"
    );

    exit();

}


/* =========================================
   GET CATEGORIES
========================================= */

$categories =
    $conn->query("
        SELECT
            categories.id,
            categories.category_name,
            COUNT(products.id)
            AS total_products

        FROM categories

        LEFT JOIN products
            ON categories.id =
               products.category_id

        GROUP BY
            categories.id,
            categories.category_name

        ORDER BY
            categories.category_name ASC
    ");


/* =========================================
   GET PRODUCTS
========================================= */

$products =
    $conn->query("
        SELECT
            products.id,
            products.product_name,
            products.price,
            products.quantity,
            products.description,
            categories.category_name

        FROM products

        LEFT JOIN categories
            ON products.category_id =
               categories.id

        WHERE products.quantity > 0

        ORDER BY
            products.id DESC

        LIMIT 8
    ");


include "../includes/header.php";

?>


<div class="customer-page">


    <!-- =====================================
         HERO SECTION
    ====================================== -->

    <section class="customer-hero">

        <div class="hero-content">

            <p class="hero-label">

                WELCOME TO PMS STORE

            </p>


            <h1>

                Welcome back,

                <?php

                echo htmlspecialchars(
                    $user["username"]
                );

                ?>

            </h1>


            <p>

                Discover products, shop your
                favourites, or sell your own
                products.

            </p>


            <div class="hero-buttons">

                <a
                    href="products.php"
                    class="customer-primary-btn"
                >

                    Browse Products

                </a>


                <a
                    href="sell_product.php"
                    class="customer-secondary-btn"
                >

                    + Sell Your Product

                </a>

            </div>

        </div>


        <div class="hero-card">

            <div class="hero-card-icon">

                🛍️

            </div>


            <h3>

                Shop & Sell

            </h3>


            <p>

                Buy products from our store
                or list your own product for sale.

            </p>

        </div>

    </section>



    <!-- =====================================
         CATEGORY SECTION
    ====================================== -->

    <section class="customer-section">

        <div class="section-title">

            <div>

                <span>

                    EXPLORE

                </span>


                <h2>

                    Shop by Category

                </h2>

            </div>


            <a href="products.php">

                View All →

            </a>

        </div>


        <div class="customer-category-grid">

            <?php

            if (
                $categories &&
                $categories->num_rows > 0
            ) {

                while (
                    $category =
                    $categories->fetch_assoc()
                ) {

            ?>

                <a
                    href="products.php?category=<?php
                    echo $category["id"];
                    ?>"
                    class="customer-category-card"
                >

                    <div class="category-card-icon">

                        C

                    </div>


                    <div>

                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $category[
                                    "category_name"
                                ]
                            );

                            ?>

                        </h3>


                        <p>

                            <?php

                            echo $category[
                                "total_products"
                            ];

                            ?>

                            Products

                        </p>

                    </div>

                </a>

            <?php

                }

            } else {

            ?>

                <div class="empty-category">

                    No categories available.

                </div>

            <?php

            }

            ?>

        </div>

    </section>



    <!-- =====================================
         PRODUCTS SECTION
    ====================================== -->

    <section class="customer-section">

        <div class="section-title">

            <div>

                <span>

                    SHOP NOW

                </span>


                <h2>

                    Available Products

                </h2>

            </div>


            <a href="products.php">

                View All →

            </a>

        </div>


        <div class="customer-product-grid">

            <?php

            if (
                $products &&
                $products->num_rows > 0
            ) {

                while (
                    $product =
                    $products->fetch_assoc()
                ) {

            ?>

                <div class="customer-product-card">


                    <!-- =================================
                         PRODUCT IMAGE
                    ================================== -->

                    <div class="customer-product-image">

                        <img
                            src="../images/product_image.png"
                            alt="<?php
                            echo htmlspecialchars(
                                $product["product_name"]
                            );
                            ?>"
                        >


                        <span class="stock-badge">

                            In Stock

                        </span>

                    </div>



                    <!-- =================================
                         PRODUCT INFORMATION
                    ================================== -->

                    <div class="customer-product-info">

                        <p class="product-category">

                            <?php

                            echo htmlspecialchars(
                                $product[
                                    "category_name"
                                ]
                                ??
                                "Uncategorized"
                            );

                            ?>

                        </p>


                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $product[
                                    "product_name"
                                ]
                            );

                            ?>

                        </h3>


                        <p class="product-description">

                            <?php

                            $description =
                                $product[
                                    "description"
                                ]
                                ??
                                "";


                            echo htmlspecialchars(

                                strlen(
                                    $description
                                ) > 80

                                ?

                                substr(
                                    $description,
                                    0,
                                    80
                                ) . "..."

                                :

                                $description

                            );

                            ?>

                        </p>


                        <div class="product-card-bottom">

                            <strong>

                                ₹<?php

                                echo number_format(
                                    (float)
                                    $product["price"],
                                    2
                                );

                                ?>

                            </strong>


                            <a
                                href="product_details.php?id=<?php
                                echo $product["id"];
                                ?>"
                                class="view-product-btn"
                            >

                                View

                            </a>

                        </div>

                    </div>

                </div>

            <?php

                }

            } else {

            ?>

                <div class="empty-products">

                    <h3>

                        No Products Available

                    </h3>


                    <p>

                        Products added by the admin
                        will appear here.

                    </p>

                </div>

            <?php

            }

            ?>

        </div>

    </section>



    <!-- =====================================
         SELL SECTION
    ====================================== -->

    <section class="sell-banner">

        <div>

            <span>

                SELL ON PMS

            </span>


            <h2>

                Have a product to sell?

            </h2>


            <p>

                List your product and let customers
                discover it.

            </p>

        </div>


        <a
            href="sell_product.php"
            class="customer-primary-btn"
        >

            + Sell Your Product

        </a>

    </section>



    <!-- =====================================
         QUICK ACTIONS
    ====================================== -->

    <section class="quick-actions">


        <a href="cart.php">

            <span>

                🛒

            </span>


            <div>

                <h3>

                    My Cart

                </h3>


                <p>

                    View your selected products

                </p>

            </div>

        </a>



        <a href="orders.php">

            <span>

                📦

            </span>


            <div>

                <h3>

                    My Orders

                </h3>


                <p>

                    Track your purchases

                </p>

            </div>

        </a>



        <a href="my_products.php">

            <span>

                💰

            </span>


            <div>

                <h3>

                    My Listings

                </h3>


                <p>

                    Manage products you are selling

                </p>

            </div>

        </a>


    </section>


</div>


<?php

include "../includes/footer.php";

?>