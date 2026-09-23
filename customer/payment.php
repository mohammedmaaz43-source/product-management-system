<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "Payment";
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
// EMPTY CART CHECK
// =========================================

if ($cartItems->num_rows === 0) {

    header("Location: cart.php");
    exit();
}


// =========================================
// PREPARE ITEMS
// =========================================

$items = [];

$totalAmount = 0;

$totalItems = 0;

$stockError = "";


while (
    $item = $cartItems->fetch_assoc()
) {

    if (
        (int) $item["cart_quantity"]
        >
        (int) $item["stock"]
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
// PAYMENT PROCESSING
// =========================================

$error = "";

$selectedMethod = "";


if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST["make_payment"])
) {

    $selectedMethod =
        $_POST["payment_method"] ?? "";


    // =====================================
    // CHECK PAYMENT METHOD
    // =====================================

    $allowedMethods = [
        "UPI",
        "Card",
        "Cash on Delivery"
    ];


    if (
        !in_array(
            $selectedMethod,
            $allowedMethods,
            true
        )
    ) {

        $error =
            "Please select a valid payment method.";

    } elseif (!empty($stockError)) {

        $error =
            $stockError;

    } else {

        /*
        =====================================
        START TRANSACTION
        =====================================
        */

        $conn->begin_transaction();


        try {

            // =================================
            // CREATE TRANSACTION ID
            // =================================

            if (
                $selectedMethod ===
                "Cash on Delivery"
            ) {

                $paymentStatus = "Pending";

                $transactionId =
                    "COD-" .
                    strtoupper(
                        substr(
                            uniqid(),
                            -8
                        )
                    );

            } else {

                $paymentStatus = "Paid";

                $transactionId =
                    "TXN-" .
                    strtoupper(
                        substr(
                            uniqid(),
                            -10
                        )
                    );
            }


            // =================================
            // CREATE ORDER
            // =================================

            $orderStmt = $conn->prepare("
                INSERT INTO orders
                (
                    user_id,
                    total_amount,
                    status,
                    payment_method,
                    payment_status,
                    transaction_id,
                    payment_date
                )
                VALUES (?, ?, 'Pending', ?, ?, ?, NOW())
            ");


            $orderStmt->bind_param(
                "idsss",
                $userId,
                $totalAmount,
                $selectedMethod,
                $paymentStatus,
                $transactionId
            );


            if (!$orderStmt->execute()) {

                throw new Exception(
                    "Order could not be created."
                );
            }


            $orderId =
                $conn->insert_id;


            $orderStmt->close();


            // =================================
            // ADD ORDER ITEMS
            // =================================

            foreach ($items as $item) {

                $productId =
                    (int) $item["product_id"];

                $quantity =
                    (int) $item["cart_quantity"];

                $price =
                    (float) $item["price"];


                // Latest stock check

                $stockStmt = $conn->prepare("
                    SELECT quantity
                    FROM products
                    WHERE id = ?
                    FOR UPDATE
                ");


                $stockStmt->bind_param(
                    "i",
                    $productId
                );

                $stockStmt->execute();

                $stockResult =
                    $stockStmt->get_result();

                $stockData =
                    $stockResult->fetch_assoc();

                $stockStmt->close();


                if (
                    !$stockData
                    ||
                    (int) $stockData["quantity"]
                    <
                    $quantity
                ) {

                    throw new Exception(
                        "Insufficient stock for " .
                        $item["product_name"]
                    );
                }


                // Insert order item

                $itemStmt = $conn->prepare("
                    INSERT INTO order_items
                    (
                        order_id,
                        product_id,
                        quantity,
                        price
                    )
                    VALUES (?, ?, ?, ?)
                ");


                $itemStmt->bind_param(
                    "iiid",
                    $orderId,
                    $productId,
                    $quantity,
                    $price
                );


                if (!$itemStmt->execute()) {

                    throw new Exception(
                        "Order item could not be created."
                    );
                }


                $itemStmt->close();


                // Reduce stock

                $updateStock = $conn->prepare("
                    UPDATE products
                    SET quantity = quantity - ?
                    WHERE id = ?
                ");


                $updateStock->bind_param(
                    "ii",
                    $quantity,
                    $productId
                );


                if (
                    !$updateStock->execute()
                ) {

                    throw new Exception(
                        "Product stock could not be updated."
                    );
                }


                $updateStock->close();
            }


            // =================================
            // CLEAR CART
            // =================================

            $clearCart = $conn->prepare("
                DELETE FROM cart
                WHERE user_id = ?
            ");


            $clearCart->bind_param(
                "i",
                $userId
            );


            if (!$clearCart->execute()) {

                throw new Exception(
                    "Cart could not be cleared."
                );
            }


            $clearCart->close();


            // =================================
            // COMMIT
            // =================================

            $conn->commit();


            // =================================
            // SAVE ORDER ID
            // =================================

            $_SESSION["order_id"] =
                $orderId;

            $_SESSION["last_order_id"] =
                $orderId;


            /*
            =====================================
            GO TO RECEIPT
            =====================================
            */

            header(
                "Location: payment_receipt.php?order_id="
                . $orderId
            );

            exit();


        } catch (Exception $e) {

            $conn->rollback();

            $error =
                $e->getMessage();
        }
    }
}


include "../includes/header.php";

?>



<!-- =====================================
     PAYMENT HEADER
====================================== -->

<section class="customer-page-header">

    <div>

        <span class="hero-label">
            SECURE CHECKOUT
        </span>

        <h1>
            Complete Payment
        </h1>

        <p>
            Select your preferred payment method.
        </p>

    </div>

</section>



<!-- =====================================
     PAYMENT SECTION
====================================== -->

<section class="checkout-section">


    <!-- PAYMENT METHODS -->

    <div class="checkout-items">

        <div class="checkout-card">

            <div class="checkout-card-header">

                <span>
                    PAYMENT METHOD
                </span>

                <h2>
                    Choose Payment Method
                </h2>

            </div>


            <?php if (!empty($error)) { ?>

                <div class="customer-error-message">

                    <?php
                    echo htmlspecialchars(
                        $error
                    );
                    ?>

                </div>

            <?php } ?>


            <form
                method="POST"
                id="paymentForm"
            >


                <!-- UPI -->

                <label
                    class="payment-method-card"
                >

                    <input
                        type="radio"
                        name="payment_method"
                        value="UPI"
                        <?php

                        if (
                            $selectedMethod ===
                            "UPI"
                        ) {
                            echo "checked";
                        }

                        ?>
                    >

                    <div>

                        <strong>
                            UPI Payment
                        </strong>

                        <span>
                            Pay using UPI
                            / QR payment
                        </span>

                    </div>

                </label>



                <!-- CARD -->

                <label
                    class="payment-method-card"
                >

                    <input
                        type="radio"
                        name="payment_method"
                        value="Card"
                        <?php

                        if (
                            $selectedMethod ===
                            "Card"
                        ) {
                            echo "checked";
                        }

                        ?>
                    >

                    <div>

                        <strong>
                            Debit / Credit Card
                        </strong>

                        <span>
                            Pay using your card
                        </span>

                    </div>

                </label>



                <!-- COD -->

                <label
                    class="payment-method-card"
                >

                    <input
                        type="radio"
                        name="payment_method"
                        value="Cash on Delivery"
                        <?php

                        if (
                            $selectedMethod ===
                            "Cash on Delivery"
                        ) {
                            echo "checked";
                        }

                        ?>
                    >

                    <div>

                        <strong>
                            Cash on Delivery
                        </strong>

                        <span>
                            Pay when your order arrives
                        </span>

                    </div>

                </label>



                <!-- PAY BUTTON -->

                <button
                    type="submit"
                    name="make_payment"
                    class="customer-primary-btn payment-submit-btn"

                    <?php

                    if (!empty($stockError)) {
                        echo "disabled";
                    }

                    ?>

                >

                    Complete Payment

                </button>


            </form>

        </div>

    </div>



    <!-- ORDER SUMMARY -->

    <aside class="checkout-summary">

        <span>
            ORDER SUMMARY
        </span>

        <h2>
            Payment Summary
        </h2>


        <div class="summary-row">

            <span>
                Customer
            </span>

            <strong>

                <?php

                echo htmlspecialchars(
                    $user["username"]
                );

                ?>

            </strong>

        </div>


        <div class="summary-row">

            <span>
                Total Items
            </span>

            <strong>

                <?php
                echo $totalItems;
                ?>

            </strong>

        </div>


        <div class="summary-divider"></div>


        <div class="summary-total">

            <span>
                Total Payable
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


        <a
            href="checkout.php"
            class="continue-shopping-btn"
        >

            ← Back to Checkout

        </a>

    </aside>


</section>



<?php

include "../includes/footer.php";

?>