
<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "Products";
$rootPath = "../";


// =========================================
// ENABLE CUSTOMER CSS
// =========================================

$isCustomerPageCustom = true;


// =========================================
// CHECK LOGGED-IN CUSTOMER
// =========================================

$userId = (int) $_SESSION["user_id"];

$userQuery = $conn->prepare("
    SELECT id, username, role
    FROM users
    WHERE id = ?
");

$userQuery->bind_param("i", $userId);
$userQuery->execute();

$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();

$userQuery->close();


// =========================================
// SECURITY CHECK
// =========================================

if (!$user || $user["role"] !== "user") {

    header("Location: ../login.php");
    exit();

}


// =========================================
// SEARCH
// =========================================

$search = "";

if (isset($_GET["search"])) {

    $search = trim($_GET["search"]);

}


// =========================================
// CATEGORY FILTER
// =========================================

$categoryId = 0;

if (isset($_GET["category"])) {

    $categoryId = (int) $_GET["category"];

}


// =========================================
// GET CATEGORIES
// =========================================

$categories = $conn->query("
    SELECT
        id,
        category_name
    FROM categories
    ORDER BY category_name ASC
");


// =========================================
// GET PRODUCTS
// =========================================

$sql = "
    SELECT
        products.id,
        products.product_name,
        products.price,
        products.quantity,
        products.description,
        products.image,
        categories.category_name

    FROM products

    LEFT JOIN categories
        ON products.category_id = categories.id

    WHERE products.quantity > 0
";


$params = [];
$types = "";


// =========================================
// SEARCH FILTER
// =========================================

if (!empty($search)) {

    $sql .= "
        AND (
            products.product_name LIKE ?
            OR products.description LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "ss";
}


// =========================================
// CATEGORY FILTER
// =========================================

if ($categoryId > 0) {

    $sql .= "
        AND products.category_id = ?
    ";

    $params[] = $categoryId;

    $types .= "i";
}


// =========================================
// ORDER
// =========================================

$sql .= "
    ORDER BY products.id DESC
";


$stmt = $conn->prepare($sql);


// =========================================
// BIND DYNAMIC PARAMETERS
// =========================================

if (!empty($params)) {

    $bindParams = [];

    $bindParams[] = $types;

    foreach ($params as $key => $value) {

        $bindParams[] = &$params[$key];

    }

    call_user_func_array(
        [$stmt, "bind_param"],
        $bindParams
    );
}


$stmt->execute();

$products = $stmt->get_result();


// =========================================
// HEADER
// =========================================

include "../includes/header.php";

?>

    <!-- =====================================
         PRODUCTS PAGE HEADER
    ====================================== -->

    <section class="customer-page-header">

        <div>

            <span class="hero-label">
                PMS STORE
            </span>

            <h1>
                Explore Products
            </h1>

            <p>
                Find the perfect products from our store.
            </p>

        </div>

    </section>



    <!-- =====================================
         SEARCH & FILTER
    ====================================== -->

    <section class="product-filter-section">

        <form
            method="GET"
            class="product-search-form"
        >


            <!-- SEARCH -->

            <div class="search-box">

                <input
                    type="text"
                    name="search"
                    placeholder="Search products..."
                    value="<?php
                    echo htmlspecialchars($search);
                    ?>"
                >

            </div>



            <!-- CATEGORY -->

            <div class="category-filter">

                <select name="category">

                    <option value="0">

                        All Categories

                    </option>


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

                        <option
                            value="<?php
                            echo $category["id"];
                            ?>"
                            <?php

                            if (
                                $categoryId ==
                                $category["id"]
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

                    <?php

                        }

                    }

                    ?>

                </select>

            </div>



            <!-- SEARCH BUTTON -->

            <button
                type="submit"
                class="customer-primary-btn"
            >
                🔍 Search
            </button>



            <!-- CLEAR BUTTON -->

            <a
                href="products.php"
                class="customer-secondary-btn"
            >
                Clear
            </a>

        </form>

    </section>



    <!-- =====================================
         PRODUCT RESULTS
    ====================================== -->

    <section class="customer-section">


        <!-- SECTION HEADER -->

        <div class="section-title">

            <div>

                <span>
                    STORE
                </span>

                <h2>
                    Available Products
                </h2>

            </div>


            <p class="product-result-count">

                <?php

                echo $products->num_rows;

                ?>

                Products Found

            </p>

        </div>



        <!-- PRODUCT GRID -->

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



                <!-- =================================
                     PRODUCT CARD
                ================================== -->

                <div class="customer-product-card">


                    <!-- PRODUCT IMAGE -->

                    <div class="customer-product-image">


                        <?php

                        if (
                            !empty(
                                $product["image"]
                            )
                        ) {

                        ?>

<img
    src="../images/product_image.png"
    alt="<?php
    echo htmlspecialchars($product["product_name"]);
    ?>"
>

                        <?php

                        } else {

                        ?>

                            <div class="no-product-image">

                                🛍️

                            </div>

                        <?php

                        }


                        ?>


                        <!-- STOCK BADGE -->

                        <span class="stock-badge">

                            <?php

                            if (
                                $product["quantity"] <= 5
                            ) {

                                echo "Only " .
                                    $product["quantity"] .
                                    " Left";

                            } else {

                                echo "In Stock";

                            }

                            ?>

                        </span>


                    </div>



                    <!-- PRODUCT INFORMATION -->

                    <div class="customer-product-info">


                        <!-- CATEGORY -->

                        <p class="product-category">

                            <?php

                            echo htmlspecialchars(
                                $product["category_name"]
                                ?? "Uncategorized"
                            );

                            ?>

                        </p>



                        <!-- PRODUCT NAME -->

                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $product["product_name"]
                            );

                            ?>

                        </h3>



                        <!-- DESCRIPTION -->

                        <p class="product-description">

                            <?php

                            $description =
                                $product["description"]
                                ?? "";

                            if (
                                strlen($description) > 90
                            ) {

                                echo htmlspecialchars(
                                    substr(
                                        $description,
                                        0,
                                        90
                                    ) . "..."
                                );

                            } else {

                                echo htmlspecialchars(
                                    $description
                                );

                            }

                            ?>

                        </p>



                        <!-- PRICE + VIEW -->

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
                                View Details
                            </a>


                        </div>


                    </div>


                </div>


            <?php

                }


            } else {

            ?>


                <!-- =================================
                     NO PRODUCTS
                ================================== -->

                <div class="empty-products">


                    <div class="empty-icon">
                        🔍
                    </div>


                    <h3>
                        No Products Found
                    </h3>


                    <p>
                        Try another search or
                        select a different category.
                    </p>


                    <a
                        href="products.php"
                        class="customer-primary-btn"
                    >
                        View All Products
                    </a>


                </div>


            <?php

            }

            ?>


        </div>


    </section>



    <!-- =====================================
         SELL BANNER
    ====================================== -->

    <section class="sell-banner">


        <div>

            <span>
                SELL YOUR PRODUCT
            </span>

            <h2>
                Want to sell something?
            </h2>

            <p>
                Submit your product and wait
                for admin approval.
            </p>

        </div>


        <a
            href="sell_product.php"
            class="customer-primary-btn"
        >
            + Sell Your Product
        </a>


    </section>


</div>


<?php

include "../includes/footer.php";

?>