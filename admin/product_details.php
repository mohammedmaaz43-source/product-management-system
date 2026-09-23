<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();


/* =========================================
   CHECK PRODUCT ID
========================================= */

if (!isset($_GET["id"])) {

    header("Location: products.php");
    exit();

}


$productId = (int) $_GET["id"];

$message = "";
$error = "";


/* =========================================
   ADD REVIEW
========================================= */

if (isset($_POST["add_review"])) {

    $rating =
        (int) $_POST["rating"];

    $review =
        clean($_POST["review"]);

    $username =
        $_SESSION["username"];


    if (
        $rating < 1 ||
        $rating > 5
    ) {

        $error =
            "Please select a rating between 1 and 5.";

    } elseif (empty($review)) {

        $error =
            "Please enter your review.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO reviews
            (
                product_id,
                username,
                rating,
                review
            )
            VALUES (?, ?, ?, ?)
        ");


        $stmt->bind_param(
            "isis",
            $productId,
            $username,
            $rating,
            $review
        );


        if ($stmt->execute()) {

            header(
                "Location: product_details.php?id=" .
                $productId .
                "&success=1"
            );

            exit();

        } else {

            $error =
                "Review could not be added.";

        }


        $stmt->close();

    }

}


/* =========================================
   SUCCESS MESSAGE
========================================= */

if (isset($_GET["success"])) {

    $message =
        "Review added successfully.";

}


/* =========================================
   GET PRODUCT DETAILS
========================================= */

$stmt = $conn->prepare("
    SELECT
        products.*,
        categories.category_name,
        suppliers.supplier_name

    FROM products

    LEFT JOIN categories
    ON products.category_id =
        categories.id

    LEFT JOIN suppliers
    ON products.supplier_id =
        suppliers.id

    WHERE products.id = ?
");


$stmt->bind_param(
    "i",
    $productId
);


$stmt->execute();


$result =
    $stmt->get_result();


if ($result->num_rows == 0) {

    header("Location: products.php");
    exit();

}


$product =
    $result->fetch_assoc();


$stmt->close();


/* =========================================
   GET REVIEWS
========================================= */

$reviews = $conn->query("
    SELECT *
    FROM reviews

    WHERE product_id = $productId

    ORDER BY created_at DESC
");


/* =========================================
   GET AVERAGE RATING
========================================= */

$ratingResult = $conn->query("
    SELECT

        AVG(rating)
        AS average_rating,

        COUNT(id)
        AS total_reviews

    FROM reviews

    WHERE product_id =
        $productId
");


$ratingData =
    $ratingResult->fetch_assoc();


$averageRating =
    $ratingData["average_rating"] ?? 0;


$totalReviews =
    $ratingData["total_reviews"] ?? 0;


/* =========================================
   PAGE SETTINGS
========================================= */

$pageTitle =
    "Product Details";

$rootPath =
    "../";


include "../includes/header.php";

?>


<div class="admin-layout">

    <?php
    include "../includes/sidebar.php";
    ?>


    <main class="admin-content">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <div>

                <h1>
                    Product Details
                </h1>

                <p>
                    Complete product information,
                    ratings and reviews.
                </p>

            </div>


            <a
                href="products.php"
                class="btn"
            >
                ← Back to Products
            </a>

        </div>



        <!-- PRODUCT DETAILS -->

        <div class="product-details-card">


            <!-- PRODUCT IMAGE -->

            <div class="product-details-image">

                <?php

                $imagePath =
                    !empty($product["image"])
                    ? "../uploads/" .
                    $product["image"]
                    : "../images/product_image.png";

                ?>


                <img
                    src="<?php
                    echo htmlspecialchars(
                        $imagePath
                    );
                    ?>"
                    alt="Product Image"
                >

            </div>



            <!-- PRODUCT INFORMATION -->

            <div class="product-details-info">


                <h2>

                    <?php
                    echo htmlspecialchars(
                        $product["product_name"]
                    );
                    ?>

                </h2>



                <!-- RATING -->

                <div class="product-rating">

                    <span class="rating-stars">

                        <?php

                        if (
                            $totalReviews > 0
                        ) {

                            echo "⭐";

                        } else {

                            echo "☆";

                        }

                        ?>

                    </span>


                    <strong>

                        <?php
                        echo number_format(
                            $averageRating,
                            1
                        );
                        ?>

                        / 5

                    </strong>


                    <span>

                        (
                        <?php
                        echo $totalReviews;
                        ?>
                        Reviews)

                    </span>

                </div>



                <!-- PRICE -->

                <h3 class="product-price">

                    ₹<?php
                    echo number_format(
                        $product["price"],
                        2
                    );
                    ?>

                </h3>



                <!-- PRODUCT INFORMATION -->

                <div class="product-info-grid">


                    <div>

                        <strong>
                            Category
                        </strong>

                        <span>

                            <?php

                            echo htmlspecialchars(
                                $product[
                                    "category_name"
                                ]
                                ?? "Not Available"
                            );

                            ?>

                        </span>

                    </div>



                    <div>

                        <strong>
                            Supplier
                        </strong>

                        <span>

                            <?php

                            echo htmlspecialchars(
                                $product[
                                    "supplier_name"
                                ]
                                ?? "Not Available"
                            );

                            ?>

                        </span>

                    </div>



                    <div>

                        <strong>
                            Available Stock
                        </strong>

                        <span>

                            <?php
                            echo $product[
                                "quantity"
                            ];
                            ?>

                        </span>

                    </div>



                    <div>

                        <strong>
                            Product ID
                        </strong>

                        <span>

                            #<?php
                            echo $product[
                                "id"
                            ];
                            ?>

                        </span>

                    </div>


                </div>


            </div>


        </div>



        <!-- PRODUCT DESCRIPTION -->

        <div class="product-description-card">


            <h2>
                Product Description
            </h2>


            <p>

                <?php

                if (
                    !empty(
                        $product["description"]
                    )
                ) {

                    echo nl2br(
                        htmlspecialchars(
                            $product[
                                "description"
                            ]
                        )
                    );

                } else {

                    echo
                    "No description available for this product.";

                }

                ?>

            </p>


        </div>



        <!-- ADD REVIEW -->

        <div class="add-review-card">


            <h2>
                Add Your Review
            </h2>


            <?php
            if (!empty($message)) {
            ?>

                <div class="success-message">

                    <?php
                    echo htmlspecialchars(
                        $message
                    );
                    ?>

                </div>

            <?php
            }
            ?>


            <?php
            if (!empty($error)) {
            ?>

                <div class="error-message">

                    <?php
                    echo htmlspecialchars(
                        $error
                    );
                    ?>

                </div>

            <?php
            }
            ?>


            <form method="POST">


                <!-- RATING -->

                <div class="form-group">

                    <label>
                        Your Rating
                    </label>


                    <select
                        name="rating"
                        required
                    >

                        <option value="">
                            Select Rating
                        </option>

                        <option value="5">
                            ⭐⭐⭐⭐⭐ Excellent
                        </option>

                        <option value="4">
                            ⭐⭐⭐⭐ Very Good
                        </option>

                        <option value="3">
                            ⭐⭐⭐ Good
                        </option>

                        <option value="2">
                            ⭐⭐ Average
                        </option>

                        <option value="1">
                            ⭐ Poor
                        </option>

                    </select>

                </div>



                <!-- REVIEW -->

                <div class="form-group">

                    <label>
                        Your Review
                    </label>


                    <textarea
                        name="review"
                        rows="5"
                        placeholder="Write your review here..."
                        required
                    ></textarea>

                </div>



                <button
                    type="submit"
                    name="add_review"
                    class="btn"
                >
                    Submit Review
                </button>


            </form>


        </div>



        <!-- CUSTOMER REVIEWS -->

        <div class="reviews-section">


            <div class="section-heading">

                <div>

                    <h2>
                        Customer Reviews
                    </h2>

                    <p>

                        See what users think
                        about this product.

                    </p>

                </div>

            </div>



            <div class="reviews-list">


                <?php

                if (
                    $reviews &&
                    $reviews->num_rows > 0
                ) {

                    while (
                        $review =
                        $reviews->fetch_assoc()
                    ) {

                ?>


                    <div class="review-card">


                        <div class="review-header">


                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $review[
                                        "username"
                                    ]
                                );

                                ?>

                            </strong>



                            <span
                                class="review-rating"
                            >

                                <?php

                                echo str_repeat(
                                    "⭐",
                                    $review[
                                        "rating"
                                    ]
                                );

                                ?>

                            </span>


                        </div>



                        <p>

                            <?php

                            echo nl2br(
                                htmlspecialchars(
                                    $review[
                                        "review"
                                    ]
                                )
                            );

                            ?>

                        </p>



                        <small>

                            <?php

                            echo date(
                                "d M Y",
                                strtotime(
                                    $review[
                                        "created_at"
                                    ]
                                )
                            );

                            ?>

                        </small>


                    </div>


                <?php

                    }

                } else {

                ?>


                    <div class="no-reviews">

                        No reviews available yet.

                        Be the first person
                        to review this product.

                    </div>


                <?php

                }

                ?>


            </div>


        </div>


    </main>

</div>


<?php
include "../includes/footer.php";
?>