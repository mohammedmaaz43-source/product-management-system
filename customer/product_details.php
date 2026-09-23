
<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "Product Details";
$rootPath = "../";

$isCustomerPageCustom = true;


/* =========================================
   CHECK CUSTOMER
========================================= */

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


if (!$user || $user["role"] !== "user") {

    header("Location: ../login.php");

    exit();

}


/* =========================================
   GET PRODUCT ID
========================================= */

$productId = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


if ($productId <= 0) {

    header("Location: products.php");

    exit();

}


/* =========================================
   GET PRODUCT
========================================= */

$productQuery = $conn->prepare("
    SELECT
        products.id,
        products.product_name,
        products.price,
        products.quantity,
        products.description,
        products.created_at,
        categories.category_name,
        suppliers.supplier_name

    FROM products

    LEFT JOIN categories
        ON products.category_id = categories.id

    LEFT JOIN suppliers
        ON products.supplier_id = suppliers.id

    WHERE products.id = ?
");


$productQuery->bind_param(
    "i",
    $productId
);

$productQuery->execute();

$productResult =
    $productQuery->get_result();

$product =
    $productResult->fetch_assoc();


if (!$product) {

    header("Location: products.php");

    exit();

}


/* =========================================
   GET REVIEWS
========================================= */

$reviewQuery = $conn->prepare("
    SELECT
        username,
        rating,
        review,
        created_at

    FROM reviews

    WHERE product_id = ?

    ORDER BY id DESC
");


$reviewQuery->bind_param(
    "i",
    $productId
);

$reviewQuery->execute();

$reviews =
    $reviewQuery->get_result();


/* =========================================
   GET REVIEW SUMMARY
========================================= */

$ratingQuery = $conn->prepare("
    SELECT
        COUNT(*) AS total_reviews,
        AVG(rating) AS average_rating

    FROM reviews

    WHERE product_id = ?
");


$ratingQuery->bind_param(
    "i",
    $productId
);

$ratingQuery->execute();

$ratingResult =
    $ratingQuery->get_result();

$ratingData =
    $ratingResult->fetch_assoc();


$totalReviews =
    (int) ($ratingData["total_reviews"] ?? 0);

$averageRating =
    (float) ($ratingData["average_rating"] ?? 0);


include "../includes/header.php";

?>


<div class="customer-page">


    <!-- =====================================
         PRODUCT DETAILS
    ====================================== -->

    <section class="product-details-section">


        <!-- =================================
             PRODUCT IMAGE
        ================================== -->

        <div class="product-details-image">

            <img
                src="../images/product_image.png"
                alt="<?php
                echo htmlspecialchars(
                    $product["product_name"]
                );
                ?>"
            >

        </div>



        <!-- =================================
             PRODUCT INFORMATION
        ================================== -->

        <div class="product-details-info">


            <p class="product-category">

                <?php

                echo htmlspecialchars(
                    $product["category_name"]
                    ?? "Uncategorized"
                );

                ?>

            </p>


            <h1>

                <?php

                echo htmlspecialchars(
                    $product["product_name"]
                );

                ?>

            </h1>



            <!-- RATING -->

            <div class="product-rating">

                <span class="stars">

                    <?php

                    $roundedRating =
                        round($averageRating);

                    for (
                        $i = 1;
                        $i <= 5;
                        $i++
                    ) {

                        if (
                            $i <=
                            $roundedRating
                        ) {

                            echo "★";

                        } else {

                            echo "☆";

                        }

                    }

                    ?>

                </span>


                <span>

                    <?php

                    echo number_format(
                        $averageRating,
                        1
                    );

                    ?>

                    (

                    <?php

                    echo $totalReviews;

                    ?>

                    reviews)

                </span>

            </div>



            <!-- PRICE -->

            <div class="product-details-price">

                ₹<?php

                echo number_format(
                    (float)
                    $product["price"],
                    2
                );

                ?>

            </div>



            <!-- STOCK -->

            <div class="product-stock">

                <?php

                if (
                    $product["quantity"] > 0
                ) {

                    echo "✓ " .
                        $product["quantity"] .
                        " available";

                } else {

                    echo "Out of Stock";

                }

                ?>

            </div>



            <!-- DESCRIPTION -->

            <div class="product-details-description">

                <h3>

                    Description

                </h3>


                <p>

                    <?php

                    if (
                        !empty(
                            $product["description"]
                        )
                    ) {

                        echo nl2br(
                            htmlspecialchars(
                                $product["description"]
                            )
                        );

                    } else {

                        echo "No description available.";

                    }

                    ?>

                </p>

            </div>



            <!-- SUPPLIER -->

            <?php

            if (
                !empty(
                    $product["supplier_name"]
                )
            ) {

            ?>

                <div class="product-supplier">

                    <strong>

                        Supplier:

                    </strong>

                    <?php

                    echo htmlspecialchars(
                        $product["supplier_name"]
                    );

                    ?>

                </div>

            <?php

            }

            ?>



            <!-- ACTIONS -->

            <div class="product-details-actions">

                <?php

                if (
                    $product["quantity"] > 0
                ) {

                ?>

                    <form
                        method="POST"
                        action="cart.php"
                        class="add-cart-form"
                    >

                        <input
                            type="hidden"
                            name="product_id"
                            value="<?php
                            echo $product["id"];
                            ?>"
                        >


                        <input
                            type="hidden"
                            name="quantity"
                            value="1"
                        >


                        <button
                            type="submit"
                            name="add_to_cart"
                            class="customer-primary-btn"
                        >

                            🛒 Add to Cart

                        </button>

                    </form>

                <?php

                } else {

                ?>

                    <button
                        class="customer-secondary-btn"
                        disabled
                    >

                        Out of Stock

                    </button>

                <?php

                }

                ?>


                <a
                    href="products.php"
                    class="customer-secondary-btn"
                >

                    ← Back to Products

                </a>

            </div>


        </div>

    </section>



    <!-- =====================================
         REVIEWS
    ====================================== -->

    <section class="customer-section reviews-section">

        <div class="section-title">

            <div>

                <span>

                    CUSTOMER FEEDBACK

                </span>

                <h2>

                    Product Reviews

                </h2>

            </div>

        </div>


        <?php

        if (
            $reviews &&
            $reviews->num_rows > 0
        ) {

        ?>

            <div class="reviews-list">

                <?php

                while (
                    $review =
                    $reviews->fetch_assoc()
                ) {

                ?>

                    <div class="review-card">


                        <div class="review-header">

                            <div>

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $review["username"]
                                    );

                                    ?>

                                </strong>

                            </div>


                            <div class="review-stars">

                                <?php

                                $reviewRating =
                                    (int)
                                    $review["rating"];

                                for (
                                    $i = 1;
                                    $i <= 5;
                                    $i++
                                ) {

                                    echo $i <=
                                        $reviewRating
                                        ? "★"
                                        : "☆";

                                }

                                ?>

                            </div>

                        </div>


                        <p class="review-text">

                            <?php

                            echo nl2br(
                                htmlspecialchars(
                                    $review["review"]
                                )
                            );

                            ?>

                        </p>


                        <small>

                            <?php

                            echo date(
                                "d M Y",
                                strtotime(
                                    $review["created_at"]
                                )
                            );

                            ?>

                        </small>


                    </div>

                <?php

                }

                ?>

            </div>

        <?php

        } else {

        ?>

            <div class="empty-products">

                <div class="empty-icon">

                    ⭐

                </div>


                <h3>

                    No Reviews Yet

                </h3>


                <p>

                    This product has not received
                    any reviews yet.

                </p>

            </div>

        <?php

        }

        ?>

    </section>


</div>


<?php

include "../includes/footer.php";

?>