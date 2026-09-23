<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "Checkout";
$rootPath = "../";


// =========================================
// CHECK CUSTOMER
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


if (!$user || $user["role"] !== "user") {

    header("Location: ../login.php");
    exit();
}


// =========================================
// GET CART
// =========================================

$cartQuery = $conn->prepare("
    SELECT
        cart.id AS cart_id,
        cart.product_id,
        cart.quantity AS cart_quantity,

        products.product_name,
        products.price,
        products.quantity AS stock

    FROM cart

    INNER JOIN products
        ON cart.product_id = products.id

    WHERE cart.user_id = ?

    ORDER BY cart.id DESC
");

$cartQuery->bind_param(
    "i",
    $userId
);

$cartQuery->execute();

$cartItems = $cartQuery->get_result();


// =========================================
// CHECK EMPTY CART
// =========================================

if ($cartItems->num_rows === 0) {

    header("Location: cart.php");
    exit();
}


// =========================================
// PREPARE CART DATA
// =========================================

$items = [];

$totalAmount = 0;

$totalItems = 0;

$stockError = "";


while (
    $item = $cartItems->fetch_assoc()
) {

    // Check stock
    if (
        $item["cart_quantity"] >
        $item["stock"]
    ) {

        $stockError =
            "Some products do not have enough stock. Please update your cart.";

        break;
    }


    $itemTotal =
        (float) $item["price"]
        *
        (int) $item["cart_quantity"];


    $totalAmount += $itemTotal;

    $totalItems +=
        (int) $item["cart_quantity"];


    $items[] = $item;
}


// =========================================
// PAYMENT BUTTON
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["proceed_payment"])
) {

    if (!empty($stockError)) {

        $error = $stockError;

    } else {

        /*
        =========================================
        STORE CHECKOUT DATA IN SESSION
        =========================================
        */

        $_SESSION["checkout_amount"] =
            $totalAmount;

        $_SESSION["checkout_items"] =
            $totalItems;


        /*
        =========================================
        GO TO PAYMENT PAGE
        =========================================
        */

        header("Location: payment.php");

        exit();
    }
}


include "../includes/header.php";

?>



<!-- =====================================
     CHECKOUT HEADER
====================================== -->

<section class="customer-page-header">

    <div>

        <span class="hero-label">
            CHECKOUT
        </span>

        <h1>
            Complete Your Order
        </h1>

        <p>
            Review your order before payment.
        </p>

    </div>

</section>



<!-- =====================================
     ERROR
====================================== -->

<?php if (!empty($error)) { ?>

    <div class="customer-error-message">

        <?php
        echo htmlspecialchars($error);
        ?>

    </div>

<?php } ?>



<!-- =====================================
     CHECKOUT CONTENT
====================================== -->

<section class="checkout-section">


    <!-- ORDER ITEMS -->

    <div class="checkout-items">

        <div class="checkout-card">

            <div class="checkout-card-header">

                <span>
                    ORDER DETAILS
                </span>

                <h2>
                    Your Products
                </h2>

            </div>


            <?php foreach ($items as $item) { ?>

                <div class="checkout-item">

                    <div>

                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $item["product_name"]
                            );

                            ?>

                        </h3>

                        <p>

                            ₹<?php

                            echo number_format(
                                (float) $item["price"],
                                2
                            );

                            ?>

                            ×

                            <?php

                            echo $item[
                                "cart_quantity"
                            ];

                            ?>

                        </p>

                    </div>


                    <strong>

                        ₹<?php

                        echo number_format(
                            (float) $item["price"]
                            *
                            (int) $item["cart_quantity"],
                            2
                        );

                        ?>

                    </strong>

                </div>

            <?php } ?>

        </div>



        <!-- CUSTOMER INFORMATION -->

        <div class="checkout-card">

            <div class="checkout-card-header">

                <span>
                    CUSTOMER
                </span>

                <h2>
                    Customer Information
                </h2>

            </div>


            <div class="checkout-customer-info">

                <div>

                    <span>
                        Username
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $user["username"]
                        );

                        ?>

                    </strong>

                </div>


                <div>

                    <span>
                        Total Items
                    </span>

                    <strong>

                        <?php
                        echo $totalItems;
                        ?>

                    </strong>

                </div>

            </div>

        </div>

    </div>



    <!-- ORDER SUMMARY -->

    <aside class="checkout-summary">

        <span>
            ORDER SUMMARY
        </span>

        <h2>
            Total Amount
        </h2>


        <div class="summary-row">

            <span>
                Items
            </span>

            <strong>

                <?php
                echo $totalItems;
                ?>

            </strong>

        </div>


        <div class="summary-row">

            <span>
                Subtotal
            </span>

            <strong>

                ₹<?php

                echo number_format(
                    $totalAmount,
                    2
                );

                ?>

            </strong>

        </div>


        <div class="summary-divider"></div>


        <div class="summary-total">

            <span>
                Total
            </span>

            <strong>

                ₹<?php

                echo number_format(
                    $totalAmount,
                    2
                );

                ?>

            </strong>

        </div>


        <!-- PROCEED TO PAYMENT -->

        <form method="POST">

            <button
                type="submit"
                name="proceed_payment"
                class="customer-primary-btn place-order-btn"

                <?php

                if (!empty($stockError)) {
                    echo "disabled";
                }

                ?>

            >

                Proceed to Payment

            </button>

        </form>


        <a
            href="cart.php"
            class="continue-shopping-btn"
        >

            ← Back to Cart

        </a>

    </aside>


</section>



<?php

include "../includes/footer.php";

?>
