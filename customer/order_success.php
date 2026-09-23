
<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "Order Successful";
$rootPath = "../";


// =========================================
// GET ORDER ID
// =========================================

$orderId = 0;

if (isset($_GET["order_id"])) {

    $orderId = (int) $_GET["order_id"];

}

if (
    $orderId <= 0 &&
    isset($_SESSION["order_id"])
) {

    $orderId =
        (int) $_SESSION["order_id"];

}

if (
    $orderId <= 0 &&
    isset($_SESSION["last_order_id"])
) {

    $orderId =
        (int) $_SESSION["last_order_id"];

}


// =========================================
// GET CURRENT USER ID
// =========================================

$userId =
    (int) $_SESSION["user_id"];


// =========================================
// GET ORDER
// =========================================

$order = null;

if ($orderId > 0) {

    $stmt = $conn->prepare("
        SELECT
            id,
            user_id,
            total_amount,
            status,
            payment_method,
            payment_status,
            transaction_id,
            payment_date,
            created_at

        FROM orders

        WHERE id = ?
        AND user_id = ?

        LIMIT 1
    ");

    $stmt->bind_param(
        "ii",
        $orderId,
        $userId
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    if (
        $result->num_rows === 1
    ) {

        $order =
            $result->fetch_assoc();

    }

    $stmt->close();
}


// =========================================
// PAYMENT DATE
// =========================================

$paymentDate = "";

if (
    $order &&
    !empty($order["payment_date"])
) {

    $paymentDate =
        date(
            "d M Y, h:i A",
            strtotime(
                $order["payment_date"]
            )
        );

}


// =========================================
// RECEIPT NUMBER
// =========================================

$receiptNumber = "";

if ($order) {

    $receiptNumber =
        "REC-" .
        str_pad(
            $order["id"],
            6,
            "0",
            STR_PAD_LEFT
        );

}


// =========================================
// HEADER
// =========================================

include "../includes/header.php";

?>


<div class="customer-layout">


    <main class="customer-content">


        <div class="order-success-container">


            <div class="order-success-card">


                <!-- SUCCESS ICON -->

                <div class="success-icon">

                    ✓

                </div>


                <!-- SUCCESS TITLE -->

                <h1>

                    Order Placed Successfully!

                </h1>


                <p class="success-message-text">

                    Thank you for your order.
                    Your order has been successfully placed.

                </p>



                <?php if ($order) { ?>


                    <!-- =================================
                         ORDER DETAILS
                    ================================== -->

                    <div class="order-success-info">


                        <!-- ORDER ID -->

                        <div class="success-info-item">

                            <span>
                                Order ID
                            </span>

                            <strong>

                                #<?php

                                echo (int)
                                    $order["id"];

                                ?>

                            </strong>

                        </div>



                        <!-- TOTAL AMOUNT -->

                        <div class="success-info-item">

                            <span>
                                Total Amount
                            </span>

                            <strong>

                                ₹<?php

                                echo number_format(
                                    (float)
                                    $order["total_amount"],
                                    2
                                );

                                ?>

                            </strong>

                        </div>



                        <!-- ORDER STATUS -->

                        <div class="success-info-item">

                            <span>
                                Order Status
                            </span>

                            <strong
                                class="order-status"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $order["status"]
                                );

                                ?>

                            </strong>

                        </div>



                        <!-- ORDER DATE -->

                        <div class="success-info-item">

                            <span>
                                Order Date
                            </span>

                            <strong>

                                <?php

                                echo date(
                                    "d M Y, h:i A",
                                    strtotime(
                                        $order["created_at"]
                                    )
                                );

                                ?>

                            </strong>

                        </div>



                        <!-- PAYMENT METHOD -->

                        <div class="success-info-item">

                            <span>
                                Payment Method
                            </span>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $order["payment_method"]
                                    ??
                                    "N/A"
                                );

                                ?>

                            </strong>

                        </div>



                        <!-- PAYMENT STATUS -->

                        <div class="success-info-item">

                            <span>
                                Payment Status
                            </span>

                            <strong
                                class="order-status"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $order["payment_status"]
                                    ??
                                    "N/A"
                                );

                                ?>

                            </strong>

                        </div>



                        <!-- TRANSACTION ID -->

                        <div class="success-info-item">

                            <span>
                                Transaction ID
                            </span>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $order["transaction_id"]
                                    ??
                                    "N/A"
                                );

                                ?>

                            </strong>

                        </div>



                        <!-- PAYMENT DATE -->

                        <div class="success-info-item">

                            <span>
                                Payment Date
                            </span>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $paymentDate
                                    ?: "N/A"
                                );

                                ?>

                            </strong>

                        </div>


                    </div>



                    <!-- =================================
                         RECEIPT NUMBER
                    ================================== -->

                    <div
                        class="success-receipt-box"
                    >

                        <span>
                            Receipt Number
                        </span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $receiptNumber
                            );

                            ?>

                        </strong>

                    </div>



                <?php } else { ?>


                    <!-- ORDER NOT FOUND -->

                    <div class="error-message">

                        Order details could not be found.

                    </div>


                <?php } ?>



                <!-- =================================
                     ACTION BUTTONS
                ================================== -->

                <div class="success-actions">


                    <?php if ($order) { ?>

                        <a
                            href="payment_receipt.php?order_id=<?php echo (int) $order["id"]; ?>"
                            class="btn"
                        >

                            View Payment Receipt

                        </a>

                    <?php } ?>


                    <a
                        href="orders.php"
                        class="btn"
                    >

                        View My Orders

                    </a>


                    <a
                        href="products.php"
                        class="btn-secondary"
                    >

                        Continue Shopping

                    </a>


                </div>


            </div>


        </div>


    </main>


</div>



<?php

include "../includes/footer.php";

?>