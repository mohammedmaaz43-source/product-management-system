
<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "My Orders";
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

$userQuery->bind_param(
    "i",
    $userId
);

$userQuery->execute();

$userResult =
    $userQuery->get_result();

$user =
    $userResult->fetch_assoc();

$userQuery->close();


if (
    !$user ||
    $user["role"] !== "user"
) {

    header("Location: ../login.php");
    exit();
}


// =========================================
// GET ORDERS
// =========================================

$orderStmt = $conn->prepare("
    SELECT
        orders.id,
        orders.total_amount,
        orders.status,
        orders.payment_method,
        orders.payment_status,
        orders.transaction_id,
        orders.payment_date,
        orders.created_at

    FROM orders

    WHERE orders.user_id = ?

    ORDER BY orders.id DESC
");

$orderStmt->bind_param(
    "i",
    $userId
);

$orderStmt->execute();

$orders =
    $orderStmt->get_result();

$orderStmt->close();


include "../includes/header.php";

?>


<!-- =====================================
     PAGE HEADER
====================================== -->

<section class="customer-page-header">

    <div>

        <span class="hero-label">
            ORDER HISTORY
        </span>

        <h1>
            My Orders
        </h1>

        <p>
            View your orders, payment details
            and receipts.
        </p>

    </div>

</section>



<!-- =====================================
     ORDERS
====================================== -->

<section class="orders-section">


<?php if ($orders->num_rows > 0) { ?>


    <?php while ($order = $orders->fetch_assoc()) { ?>


        <!-- =================================
             ORDER CARD
        ================================== -->

        <div class="order-card">


            <!-- ORDER HEADER -->

            <div class="order-card-header">

                <div>

                    <span class="order-label">
                        ORDER
                    </span>

                    <h2>

                        #<?php

                        echo (int)
                            $order["id"];

                        ?>

                    </h2>

                </div>


                <div class="order-date">

                    <?php

                    echo date(
                        "d M Y, h:i A",
                        strtotime(
                            $order["created_at"]
                        )
                    );

                    ?>

                </div>

            </div>



            <!-- =================================
                 ORDER SUMMARY
            ================================== -->

            <div class="order-summary-grid">


                <!-- TOTAL -->

                <div class="order-summary-item">

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

                <div class="order-summary-item">

                    <span>
                        Order Status
                    </span>

                    <strong class="order-status">

                        <?php

                        echo htmlspecialchars(
                            $order["status"]
                        );

                        ?>

                    </strong>

                </div>



                <!-- PAYMENT METHOD -->

                <div class="order-summary-item">

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

                <div class="order-summary-item">

                    <span>
                        Payment Status
                    </span>

                    <strong class="order-status">

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

                <div class="order-summary-item">

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

                <div class="order-summary-item">

                    <span>
                        Payment Date
                    </span>

                    <strong>

                        <?php

                        if (
                            !empty(
                                $order["payment_date"]
                            )
                        ) {

                            echo date(
                                "d M Y, h:i A",
                                strtotime(
                                    $order["payment_date"]
                                )
                            );

                        } else {

                            echo "N/A";

                        }

                        ?>

                    </strong>

                </div>


            </div>



            <!-- =================================
                 ORDER ITEMS
            ================================== -->

            <div class="order-items">


                <h3>
                    Products
                </h3>


                <?php

                $itemStmt =
                    $conn->prepare("
                        SELECT
                            order_items.quantity,
                            order_items.price,
                            products.product_name

                        FROM order_items

                        INNER JOIN products
                            ON order_items.product_id =
                               products.id

                        WHERE order_items.order_id = ?

                        ORDER BY order_items.id ASC
                    ");


                $itemStmt->bind_param(
                    "i",
                    $order["id"]
                );

                $itemStmt->execute();

                $items =
                    $itemStmt->get_result();

                ?>


                <?php if ($items->num_rows > 0) { ?>


                    <?php while (
                        $item =
                        $items->fetch_assoc()
                    ) { ?>


                        <div class="order-item-row">


                            <div>

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $item["product_name"]
                                    );

                                    ?>

                                </strong>

                                <span>

                                    Quantity:
                                    <?php

                                    echo (int)
                                        $item["quantity"];

                                    ?>

                                </span>

                            </div>


                            <strong>

                                ₹<?php

                                echo number_format(
                                    (float)
                                    $item["price"]
                                    *
                                    (int)
                                    $item["quantity"],
                                    2
                                );

                                ?>

                            </strong>


                        </div>


                    <?php } ?>


                <?php } else { ?>


                    <p>
                        No product details found.
                    </p>


                <?php } ?>


                <?php

                $itemStmt->close();

                ?>


            </div>



            <!-- =================================
                 ORDER ACTIONS
            ================================== -->

            <div class="order-card-actions">


                <a
                    href="payment_receipt.php?order_id=<?php echo (int) $order["id"]; ?>"
                    class="customer-primary-btn"
                >

                    View Receipt

                </a>


                <a
                    href="order_success.php?order_id=<?php echo (int) $order["id"]; ?>"
                    class="continue-shopping-btn"
                >

                    Order Details

                </a>


            </div>


        </div>


    <?php } ?>


<?php } else { ?>


    <!-- =================================
         EMPTY ORDERS
    ================================== -->

    <div class="empty-orders">

        <div class="empty-orders-icon">
            📦
        </div>

        <h2>
            No Orders Yet
        </h2>

        <p>
            You haven't placed any orders yet.
        </p>

        <a
            href="products.php"
            class="customer-primary-btn"
        >

            Start Shopping

        </a>

    </div>


<?php } ?>


</section>



<?php

include "../includes/footer.php";

?>