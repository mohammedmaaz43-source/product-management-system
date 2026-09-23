<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "Payment Receipt";
$rootPath = "../";


// =========================================
// GET CURRENT USER
// =========================================

$userId = (int) $_SESSION["user_id"];


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


// =========================================
// GET ORDER DETAILS
// =========================================

$order = null;

if ($orderId > 0) {

    $orderStmt = $conn->prepare("
        SELECT
            orders.id,
            orders.user_id,
            orders.total_amount,
            orders.status,
            orders.payment_method,
            orders.payment_status,
            orders.transaction_id,
            orders.payment_date,
            orders.created_at,
            users.username

        FROM orders

        INNER JOIN users
            ON orders.user_id = users.id

        WHERE orders.id = ?
        AND orders.user_id = ?

        LIMIT 1
    ");

    $orderStmt->bind_param(
        "ii",
        $orderId,
        $userId
    );

    $orderStmt->execute();

    $orderResult =
        $orderStmt->get_result();

    if (
        $orderResult->num_rows === 1
    ) {

        $order =
            $orderResult->fetch_assoc();

    }

    $orderStmt->close();
}


// =========================================
// ORDER NOT FOUND
// =========================================

if (!$order) {

    include "../includes/header.php";

    ?>

    <div class="customer-layout">

        <main class="customer-content">

            <div class="order-success-container">

                <div class="order-success-card">

                    <h1>
                        Receipt Not Found
                    </h1>

                    <p class="success-message-text">
                        The payment receipt could not be found.
                    </p>

                    <div class="success-actions">

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

    exit();
}


// =========================================
// GET ORDER ITEMS
// =========================================

$itemStmt = $conn->prepare("
    SELECT
        order_items.quantity,
        order_items.price,
        products.product_name

    FROM order_items

    INNER JOIN products
        ON order_items.product_id = products.id

    WHERE order_items.order_id = ?

    ORDER BY order_items.id ASC
");

$itemStmt->bind_param(
    "i",
    $orderId
);

$itemStmt->execute();

$itemResult =
    $itemStmt->get_result();

$orderItems = [];

while (
    $item = $itemResult->fetch_assoc()
) {

    $orderItems[] = $item;

}

$itemStmt->close();


// =========================================
// PAYMENT DATE
// =========================================

$paymentDate = "";

if (!empty($order["payment_date"])) {

    $paymentDate =
        date(
            "d M Y, h:i A",
            strtotime(
                $order["payment_date"]
            )
        );

} else {

    $paymentDate =
        date(
            "d M Y, h:i A",
            strtotime(
                $order["created_at"]
            )
        );
}


// =========================================
// RECEIPT NUMBER
// =========================================

$receiptNumber =
    "REC-" .
    str_pad(
        $order["id"],
        6,
        "0",
        STR_PAD_LEFT
    );


include "../includes/header.php";

?>


<!-- =====================================
     RECEIPT PAGE
====================================== -->

<div class="customer-layout">

    <main class="customer-content">


        <!-- PAGE HEADER -->

        <section class="customer-page-header">

            <div>

                <span class="hero-label">
                    PAYMENT COMPLETE
                </span>

                <h1>
                    Payment Receipt
                </h1>

                <p>
                    Your payment receipt is ready.
                </p>

            </div>

        </section>



        <!-- =================================
             RECEIPT
        ================================== -->

        <section class="payment-receipt-section">


            <div
                class="payment-receipt-card"
                id="paymentReceipt"
            >


                <!-- RECEIPT HEADER -->

                <div class="receipt-header">

                    <div>

                        <span class="receipt-label">
                            PRODUCT MANAGEMENT SYSTEM
                        </span>

                        <h2>
                            Payment Receipt
                        </h2>

                    </div>


                    <div class="receipt-success">

                        <span class="receipt-check">
                            ✓
                        </span>

                        <strong>
                            Payment Successful
                        </strong>

                    </div>

                </div>



                <!-- RECEIPT INFORMATION -->

                <div class="receipt-info-grid">


                    <div class="receipt-info-item">

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


                    <div class="receipt-info-item">

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


                    <div class="receipt-info-item">

                        <span>
                            Customer
                        </span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $order["username"]
                            );

                            ?>

                        </strong>

                    </div>


                    <div class="receipt-info-item">

                        <span>
                            Payment Date
                        </span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $paymentDate
                            );

                            ?>

                        </strong>

                    </div>


                    <div class="receipt-info-item">

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


                    <div class="receipt-info-item">

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

                </div>



                <!-- =================================
                     PRODUCTS
                ================================== -->

                <div class="receipt-products">

                    <div class="receipt-products-header">

                        <span>
                            PRODUCT
                        </span>

                        <span>
                            QTY
                        </span>

                        <span>
                            PRICE
                        </span>

                        <span>
                            TOTAL
                        </span>

                    </div>


                    <?php

                    foreach (
                        $orderItems
                        as $item
                    ) {

                        $itemTotal =
                            (float)
                            $item["price"]
                            *
                            (int)
                            $item["quantity"];

                    ?>

                        <div
                            class="receipt-product-row"
                        >

                            <span>

                                <?php

                                echo htmlspecialchars(
                                    $item["product_name"]
                                );

                                ?>

                            </span>


                            <span>

                                <?php

                                echo (int)
                                    $item["quantity"];

                                ?>

                            </span>


                            <span>

                                ₹<?php

                                echo number_format(
                                    (float)
                                    $item["price"],
                                    2
                                );

                                ?>

                            </span>


                            <strong>

                                ₹<?php

                                echo number_format(
                                    $itemTotal,
                                    2
                                );

                                ?>

                            </strong>

                        </div>

                    <?php

                    }

                    ?>

                </div>



                <!-- =================================
                     TOTAL
                ================================== -->

                <div class="receipt-total-section">

                    <div>

                        <span>
                            Payment Status
                        </span>

                        <strong
                            class="receipt-paid"
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


                    <div class="receipt-grand-total">

                        <span>
                            Total Paid
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

                </div>



                <!-- RECEIPT FOOTER -->

                <div class="receipt-footer">

                    <p>
                        Thank you for shopping with
                        Product Management System.
                    </p>

                    <small>
                        This is a computer-generated receipt.
                    </small>

                </div>


            </div>



            <!-- =================================
                 ACTION BUTTONS
            ================================== -->

            <div class="receipt-actions">

                <button
                    type="button"
                    class="customer-primary-btn"
                    onclick="printReceipt()"
                >

                    Print Receipt

                </button>


                <button
                    type="button"
                    class="customer-secondary-btn"
                    onclick="downloadReceipt()"
                >

                    Download Receipt

                </button>


                <a
                    href="order_success.php?order_id=<?php echo (int) $order["id"]; ?>"
                    class="customer-secondary-btn"
                >

                    Order Details

                </a>


                <a
                    href="orders.php"
                    class="customer-secondary-btn"
                >

                    My Orders

                </a>

            </div>


        </section>


    </main>

</div>



<!-- =====================================
     PRINT / DOWNLOAD JAVASCRIPT
====================================== -->

<script>

function printReceipt() {

    const receipt =
        document.getElementById(
            "paymentReceipt"
        );

    if (!receipt) {
        return;
    }


    const printWindow =
        window.open(
            "",
            "_blank",
            "width=900,height=700"
        );


    printWindow.document.write(`
        <html>

        <head>

            <title>
                Payment Receipt
            </title>

            <style>

                body {
                    font-family: Arial, sans-serif;
                    padding: 30px;
                    background: white;
                    color: #111;
                }

                * {
                    box-sizing: border-box;
                }

                .receipt-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 2px solid #ddd;
                    padding-bottom: 20px;
                    margin-bottom: 25px;
                }

                .receipt-label {
                    font-size: 12px;
                    letter-spacing: 2px;
                    font-weight: bold;
                }

                .receipt-header h2 {
                    margin: 8px 0 0;
                }

                .receipt-success {
                    font-weight: bold;
                }

                .receipt-check {
                    margin-right: 6px;
                }

                .receipt-info-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 18px;
                    margin-bottom: 30px;
                }

                .receipt-info-item {
                    border-bottom: 1px solid #ddd;
                    padding-bottom: 10px;
                }

                .receipt-info-item span {
                    display: block;
                    font-size: 12px;
                    color: #666;
                    margin-bottom: 5px;
                }

                .receipt-info-item strong {
                    font-size: 14px;
                }

                .receipt-products {
                    border: 1px solid #ddd;
                }

                .receipt-products-header,
                .receipt-product-row {
                    display: grid;
                    grid-template-columns: 2fr 1fr 1fr 1fr;
                    gap: 10px;
                    padding: 12px 15px;
                }

                .receipt-products-header {
                    background: #f3f3f3;
                    font-weight: bold;
                    font-size: 12px;
                }

                .receipt-product-row {
                    border-top: 1px solid #ddd;
                    font-size: 13px;
                }

                .receipt-total-section {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-top: 25px;
                    padding-top: 20px;
                    border-top: 2px solid #ddd;
                }

                .receipt-total-section span {
                    display: block;
                    font-size: 12px;
                    color: #666;
                    margin-bottom: 5px;
                }

                .receipt-grand-total strong {
                    font-size: 24px;
                }

                .receipt-footer {
                    text-align: center;
                    margin-top: 35px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                }

                .receipt-footer p {
                    margin-bottom: 8px;
                }

                .receipt-footer small {
                    color: #666;
                }

            </style>

        </head>

        <body>

            ${receipt.innerHTML}

        </body>

        </html>
    `);


    printWindow.document.close();

    printWindow.focus();

    setTimeout(function () {

        printWindow.print();

        printWindow.close();

    }, 500);
}



function downloadReceipt() {

    const receipt =
        document.getElementById(
            "paymentReceipt"
        );

    if (!receipt) {
        return;
    }


    const receiptHTML = `
        <!DOCTYPE html>

        <html>

        <head>

            <meta charset="UTF-8">

            <title>
                Payment Receipt
            </title>

        </head>

        <body>

            ${receipt.innerHTML}

        </body>

        </html>
    `;


    const blob =
        new Blob(
            [receiptHTML],
            {
                type: "text/html"
            }
        );


    const url =
        URL.createObjectURL(
            blob
        );


    const link =
        document.createElement(
            "a"
        );


    link.href = url;

    link.download =
        "Payment-Receipt-<?php echo (int) $order["id"]; ?>.html";


    document.body.appendChild(
        link
    );

    link.click();

    document.body.removeChild(
        link
    );


    URL.revokeObjectURL(
        url
    );
}

</script>



<?php

include "../includes/footer.php";

?>