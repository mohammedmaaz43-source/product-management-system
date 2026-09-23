<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php";
require_once "../includes/auth.php";

checkLogin();

/* =========================================================
   AUTOMATIC DELIVERY STATUS
========================================================= */

/*
   Paid orders remain Pending for 20 minutes.
   After 20 minutes, their order status becomes Delivered.
*/

$conn->query("
    UPDATE orders
    SET status = 'Delivered'
    WHERE status = 'Pending'
      AND payment_status = 'Paid'
      AND created_at <= NOW() - INTERVAL 20 MINUTE
");

$pageTitle = "Dashboard";
$rootPath = "../";


/* =========================================================
   DASHBOARD STATISTICS
========================================================= */

$totalProducts = 0;
$totalCategories = 0;
$totalSuppliers = 0;
$lowStock = 0;

$totalInventoryValue = 0;
$averageProductPrice = 0;
$stockHealth = 0;


/* =========================================================
   TOTAL PRODUCTS
========================================================= */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM products
");

if ($result) {

    $data = $result->fetch_assoc();

    $totalProducts =
        (int)$data["total"];
}


/* =========================================================
   TOTAL CATEGORIES
========================================================= */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM categories
");

if ($result) {

    $data = $result->fetch_assoc();

    $totalCategories =
        (int)$data["total"];
}


/* =========================================================
   TOTAL SUPPLIERS
========================================================= */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM suppliers
");

if ($result) {

    $data = $result->fetch_assoc();

    $totalSuppliers =
        (int)$data["total"];
}


/* =========================================================
   LOW STOCK COUNT
========================================================= */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM products
    WHERE quantity BETWEEN 1 AND 5
");

if ($result) {

    $data = $result->fetch_assoc();

    $lowStock =
        (int)$data["total"];
}


/* =========================================================
   TOTAL INVENTORY VALUE
========================================================= */

$result = $conn->query("
    SELECT
        COALESCE(
            SUM(price * quantity),
            0
        ) AS total_value
    FROM products
");

if ($result) {

    $data = $result->fetch_assoc();

    $totalInventoryValue =
        (float)$data["total_value"];
}


/* =========================================================
   AVERAGE PRODUCT PRICE
========================================================= */

$result = $conn->query("
    SELECT
        COALESCE(
            AVG(price),
            0
        ) AS average_price
    FROM products
");

if ($result) {

    $data = $result->fetch_assoc();

    $averageProductPrice =
        (float)$data["average_price"];
}


/* =========================================================
   STOCK HEALTH
========================================================= */

if ($totalProducts > 0) {

    $healthyProducts =
        $totalProducts - $lowStock;

    if ($healthyProducts < 0) {

        $healthyProducts = 0;
    }

    $stockHealth =
        round(
            ($healthyProducts / $totalProducts) * 100
        );
}


/* =========================================================
   CUSTOMER STATISTICS
========================================================= */

$totalCustomers = 0;
$totalOrders = 0;
$totalOrderAmount = 0;
$pendingOrders = 0;

$totalCustomerProducts = 0;


/* =========================================================
   TOTAL CUSTOMERS
========================================================= */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'user'
");

if ($result) {

    $data = $result->fetch_assoc();

    $totalCustomers =
        (int)$data["total"];
}


/* =========================================================
   TOTAL CUSTOMER ORDERS
========================================================= */

$result = $conn->query("
    SELECT COUNT(orders.id) AS total

    FROM orders

    INNER JOIN users
        ON orders.user_id = users.id

    WHERE users.role = 'user'
");

if ($result) {

    $data = $result->fetch_assoc();

    $totalOrders =
        (int)$data["total"];
}


/* =========================================================
   TOTAL CUSTOMER SALES
========================================================= */

$result = $conn->query("
    SELECT
        COALESCE(
            SUM(orders.total_amount),
            0
        ) AS total_amount

    FROM orders

    INNER JOIN users
        ON orders.user_id = users.id

    WHERE users.role = 'user'
");

if ($result) {

    $data = $result->fetch_assoc();

    $totalOrderAmount =
        (float)$data["total_amount"];
}


/* =========================================================
   PENDING CUSTOMER ORDERS
========================================================= */

$result = $conn->query("
    SELECT COUNT(orders.id) AS total

    FROM orders

    INNER JOIN users
        ON orders.user_id = users.id

    WHERE users.role = 'user'
    AND orders.status = 'Pending'
");

if ($result) {

    $data = $result->fetch_assoc();

    $pendingOrders =
        (int)$data["total"];
}


/* =========================================================
   TOTAL CUSTOMER PRODUCTS
=========================================================

   Customer products are stored inside the
   products table.

   seller_id identifies the customer who
   submitted the product.

========================================================= */

$result = $conn->query("
    SELECT COUNT(products.id) AS total

    FROM products

    INNER JOIN users
        ON products.seller_id = users.id

    WHERE users.role = 'user'
");

if ($result) {

    $data = $result->fetch_assoc();

    $totalCustomerProducts =
        (int)$data["total"];
}


/* =========================================================
   RECENT CUSTOMER ORDERS
========================================================= */

$recentCustomerOrders = $conn->query("
    SELECT
        orders.id,
        orders.total_amount,
        orders.status,
        orders.created_at,
        users.username

    FROM orders

    INNER JOIN users
        ON orders.user_id = users.id

    WHERE users.role = 'user'

    ORDER BY orders.created_at DESC

    LIMIT 5
");


/* =========================================================
   RECENT CUSTOMER PRODUCTS
=========================================================

   IMPORTANT:

   We do NOT use customer_products table.

   Customer submitted products are taken directly
   from products table using seller_id.

========================================================= */

$recentCustomerProducts = $conn->query("
    SELECT
        products.id,
        products.product_name,
        products.price,
        products.quantity,
        products.created_at,
        users.username,
        categories.category_name

    FROM products

    INNER JOIN users
        ON products.seller_id = users.id

    LEFT JOIN categories
        ON products.category_id = categories.id

    WHERE users.role = 'user'

    ORDER BY products.created_at DESC

    LIMIT 5
");


/* =========================================================
   RECENT PRODUCTS
========================================================= */

$recentProducts = $conn->query("
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

    ORDER BY products.created_at DESC

    LIMIT 5
");


/* =========================================================
   PRODUCTS BY CATEGORY CHART
========================================================= */

$categoryChart = $conn->query("
    SELECT
        categories.category_name,
        COUNT(products.id) AS total_products

    FROM categories

    LEFT JOIN products
        ON categories.id =
           products.category_id

    GROUP BY
        categories.id,
        categories.category_name

    ORDER BY
        total_products DESC
");


/* =========================================================
   STOCK STATUS
========================================================= */

$availableStock = 0;
$lowStockCount = 0;
$outOfStock = 0;


/* AVAILABLE */

$result = $conn->query("
    SELECT COUNT(*) AS total

    FROM products

    WHERE quantity > 5
");

if ($result) {

    $data = $result->fetch_assoc();

    $availableStock =
        (int)$data["total"];
}


/* LOW STOCK */

$result = $conn->query("
    SELECT COUNT(*) AS total

    FROM products

    WHERE quantity BETWEEN 1 AND 5
");

if ($result) {

    $data = $result->fetch_assoc();

    $lowStockCount =
        (int)$data["total"];
}


/* OUT OF STOCK */

$result = $conn->query("
    SELECT COUNT(*) AS total

    FROM products

    WHERE quantity = 0
");

if ($result) {

    $data = $result->fetch_assoc();

    $outOfStock =
        (int)$data["total"];
}


/* =========================================================
   LOW STOCK PRODUCTS
========================================================= */

$lowStockProducts = $conn->query("
    SELECT
        products.*,
        categories.category_name

    FROM products

    LEFT JOIN categories
        ON products.category_id =
           categories.id

    WHERE products.quantity BETWEEN 1 AND 5

    ORDER BY products.quantity ASC

    LIMIT 5
");


/* =========================================================
   LOW STOCK NOTIFICATIONS
========================================================= */

$lowStockNotifications = $conn->query("
    SELECT
        id,
        product_name,
        quantity

    FROM products

    WHERE quantity BETWEEN 1 AND 5

    ORDER BY quantity ASC

    LIMIT 5
");


/* =========================================================
   PAGE HEADER
========================================================= */

include "../includes/header.php";

?>

<style>

/* =========================================================
   MODERN CUSTOMER & SALES OVERVIEW
========================================================= */

.customer-admin-overview {
    position: relative;
    margin-top: 42px;
    margin-bottom: 42px;
}


/* =========================================================
   SECTION TITLE
========================================================= */

.customer-admin-title {
    margin-bottom: 22px;
}

.customer-admin-title h2 {
    margin: 0;

    font-size: 25px;
    font-weight: 750;
    letter-spacing: -0.5px;

    color: #f8fafc;
}

.customer-admin-title p {
    margin: 7px 0 0;

    font-size: 14px;
    line-height: 1.6;

    color: #94a3b8;
}


/* =========================================================
   CUSTOMER STATISTICS GRID
========================================================= */

.customer-admin-grid {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 18px;
}


/* =========================================================
   CUSTOMER STAT CARD
========================================================= */

.customer-admin-card {
    position: relative;

    min-height: 155px;

    padding: 22px;

    overflow: hidden;

    border-radius: 20px;

    background:
        linear-gradient(
            145deg,
            rgba(20, 31, 50, 0.96),
            rgba(12, 22, 38, 0.96)
        );

    border:
        1px solid
        rgba(148, 163, 184, 0.13);

    box-shadow:
        0 10px 30px
        rgba(0, 0, 0, 0.18);

    transition:
        transform 0.25s ease,
        border-color 0.25s ease,
        box-shadow 0.25s ease;
}


/* subtle glow */

.customer-admin-card::before {
    content: "";

    position: absolute;

    width: 110px;
    height: 110px;

    top: -55px;
    right: -45px;

    border-radius: 50%;

    background:
        rgba(59, 130, 246, 0.12);

    filter: blur(3px);

    pointer-events: none;
}


/* bottom highlight */

.customer-admin-card::after {
    content: "";

    position: absolute;

    left: 22px;
    right: 22px;

    bottom: 0;

    height: 2px;

    border-radius: 10px;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(96, 165, 250, 0.7),
            transparent
        );

    opacity: 0;

    transition:
        opacity 0.25s ease;
}


/* hover */

.customer-admin-card:hover {
    transform:
        translateY(-5px);

    border-color:
        rgba(96, 165, 250, 0.35);

    box-shadow:
        0 16px 40px
        rgba(0, 0, 0, 0.28);
}

.customer-admin-card:hover::after {
    opacity: 1;
}


/* =========================================================
   CARD TOP
========================================================= */

.customer-admin-card-top {
    position: relative;

    z-index: 2;

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 18px;
}


/* =========================================================
   CARD LABEL
========================================================= */

.customer-admin-card-label {
    font-size: 13px;

    font-weight: 600;

    letter-spacing: 0.2px;

    color: #94a3b8;
}


/* =========================================================
   CARD ICON
========================================================= */

.customer-admin-card-icon {
    width: 43px;
    height: 43px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 13px;

    font-size: 20px;

    background:
        rgba(59, 130, 246, 0.10);

    border:
        1px solid
        rgba(96, 165, 250, 0.16);

    box-shadow:
        inset 0 0 15px
        rgba(96, 165, 250, 0.04);
}


/* =========================================================
   CARD NUMBER
========================================================= */

.customer-admin-card h3 {
    position: relative;

    z-index: 2;

    margin: 0;

    font-size: 30px;

    line-height: 1.1;

    font-weight: 800;

    letter-spacing: -0.8px;

    color: #f8fafc;
}


/* =========================================================
   CARD DESCRIPTION
========================================================= */

.customer-admin-card p {
    position: relative;

    z-index: 2;

    margin:
        8px 0 0;

    font-size: 12px;

    color: #64748b;
}


/* =========================================================
   CUSTOMER ACTIVITY SECTION
========================================================= */

.customer-activity-section {
    margin-top: 42px;
    margin-bottom: 42px;
}


/* =========================================================
   ACTIVITY GRID
========================================================= */

.customer-activity-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 22px;
}


/* =========================================================
   ACTIVITY CARD
========================================================= */

.customer-activity-card {
    position: relative;

    min-height: 330px;

    padding: 24px;

    border-radius: 22px;

    background:
        linear-gradient(
            145deg,
            rgba(17, 28, 46, 0.97),
            rgba(10, 20, 34, 0.97)
        );

    border:
        1px solid
        rgba(148, 163, 184, 0.12);

    box-shadow:
        0 12px 35px
        rgba(0, 0, 0, 0.16);

    overflow: hidden;
}


/* top glow */

.customer-activity-card::before {
    content: "";

    position: absolute;

    top: -100px;
    right: -80px;

    width: 190px;
    height: 190px;

    border-radius: 50%;

    background:
        rgba(59, 130, 246, 0.06);

    filter: blur(5px);

    pointer-events: none;
}


/* =========================================================
   ACTIVITY HEADER
========================================================= */

.customer-activity-header {
    position: relative;

    z-index: 2;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding-bottom: 18px;

    margin-bottom: 4px;

    border-bottom:
        1px solid
        rgba(148, 163, 184, 0.08);
}

.customer-activity-header h3 {
    margin: 0;

    font-size: 18px;

    font-weight: 750;

    color: #f8fafc;
}

.customer-activity-header a {
    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding:
        7px 11px;

    border-radius: 9px;

    text-decoration: none;

    font-size: 12px;

    font-weight: 600;

    color: #60a5fa;

    background:
        rgba(59, 130, 246, 0.07);

    transition:
        background 0.2s ease,
        color 0.2s ease;
}

.customer-activity-header a:hover {
    background:
        rgba(59, 130, 246, 0.14);

    color: #93c5fd;
}


/* =========================================================
   ACTIVITY ITEM
========================================================= */

.customer-activity-item {
    position: relative;

    z-index: 2;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 18px;

    padding:
        17px 4px;

    border-bottom:
        1px solid
        rgba(148, 163, 184, 0.07);

    transition:
        padding-left 0.2s ease,
        background 0.2s ease;
}

.customer-activity-item:hover {
    padding-left: 9px;

    background:
        rgba(59, 130, 246, 0.025);
}

.customer-activity-item:last-child {
    border-bottom: none;
}


/* =========================================================
   ACTIVITY MAIN
========================================================= */

.customer-activity-main {
    min-width: 0;
}

.customer-activity-main strong {
    display: block;

    margin-bottom: 6px;

    font-size: 14px;

    font-weight: 700;

    color: #f1f5f9;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.customer-activity-main span {
    display: block;

    font-size: 11px;

    color: #64748b;
}


/* =========================================================
   ACTIVITY VALUE
========================================================= */

.customer-activity-value {
    flex-shrink: 0;

    text-align: right;
}

.customer-activity-value strong {
    display: block;

    margin-bottom: 6px;

    font-size: 14px;

    font-weight: 750;

    color: #f8fafc;
}


/* =========================================================
   STATUS BADGES
========================================================= */

.customer-status {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 65px;

    padding:
        4px 9px;

    border-radius: 20px;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 0.2px;
}

.customer-status.pending {
    color: #fbbf24;

    background:
        rgba(245, 158, 11, 0.10);

    border:
        1px solid
        rgba(245, 158, 11, 0.20);
}

.customer-status.submitted {
    color: #60a5fa;

    background:
        rgba(59, 130, 246, 0.10);

    border:
        1px solid
        rgba(59, 130, 246, 0.20);
}


/* =========================================================
   EMPTY STATE
========================================================= */

.customer-empty {
    position: relative;

    z-index: 2;

    min-height: 245px;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-direction: column;

    text-align: center;

    font-size: 13px;

    color: #64748b;
}


/* =========================================================
   EMPTY STATE ICON EFFECT
========================================================= */

.customer-empty::before {
    content: "⌁";

    width: 48px;
    height: 48px;

    display: flex;

    align-items: center;

    justify-content: center;

    margin-bottom: 12px;

    border-radius: 15px;

    font-size: 23px;

    color: #475569;

    background:
        rgba(148, 163, 184, 0.06);

    border:
        1px solid
        rgba(148, 163, 184, 0.08);
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1150px) {

    .customer-admin-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

}


@media (max-width: 850px) {

    .customer-activity-grid {
        grid-template-columns:
            1fr;
    }

}


@media (max-width: 600px) {

    .customer-admin-grid {
        grid-template-columns:
            1fr;
    }


    .customer-admin-card {
        min-height: 140px;

        padding: 20px;
    }


    .customer-activity-card {
        padding: 18px;

        border-radius: 18px;
    }


    .customer-activity-header h3 {
        font-size: 16px;
    }


    .customer-activity-item {
        gap: 10px;

        padding:
            14px 2px;
    }


    .customer-activity-main strong {
        max-width: 150px;
    }


    .customer-admin-title h2 {
        font-size: 21px;
    }

}

/* =========================================================
   HEADER ACTIONS
========================================================= */

.dashboard-header-actions {

    display: flex;

    align-items: center;

    gap: 15px;
}


/* =========================================================
   NOTIFICATION
========================================================= */

.notification-wrapper {

    position: relative;
}


.notification-btn {

    position: relative;

    width: 48px;

    height: 48px;

    display: flex;

    align-items: center;

    justify-content: center;

    text-decoration: none;

    font-size: 22px;

    border: none;

    border-radius: 14px;

    cursor: pointer;

    background:
        rgba(255, 255, 255, 0.06);

    color: inherit;
}


.notification-btn:hover {

    transform:
        translateY(-2px);
}


.notification-count {

    position: absolute;

    top: -7px;

    right: -7px;

    min-width: 22px;

    height: 22px;

    padding: 2px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    font-size: 11px;

    font-weight: bold;

    color: white;

    background:
        #ef4444;
}


.notification-panel {

    position: absolute;

    top: 58px;

    right: 0;

    width: 320px;

    z-index: 100;

    display: none;

    padding: 15px;

    border-radius: 16px;

    background:
        #111827;

    border:
        1px solid
        rgba(148, 163, 184, 0.2);

    box-shadow:
        0 15px 40px
        rgba(0, 0, 0, 0.3);
}


.notification-panel.show {

    display: block;
}


.notification-panel h3 {

    margin:
        0 0 12px;
}


.notification-item {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding:
        12px 0;

    border-bottom:
        1px solid
        rgba(148, 163, 184, 0.1);
}


.notification-item:last-child {

    border-bottom: none;
}


.notification-item span {

    font-size: 13px;
}


.notification-item strong {

    color: #f59e0b;
}


.notification-link {

    display: block;

    text-align: center;

    margin-top: 10px;

    padding-top: 10px;

    text-decoration: none;

    font-weight: 600;

    color: #60a5fa;
}


/* =========================================================
   ANALYTICS
========================================================= */

.analytics-overview {

    margin-top: 30px;

    margin-bottom: 30px;
}


.analytics-title {

    margin-bottom: 18px;
}


.analytics-title h2 {

    margin:
        0 0 6px;
}


.analytics-title p {

    margin: 0;

    opacity: 0.7;
}


.analytics-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;
}


.analytics-card {

    padding: 22px;

    border-radius: 18px;

    background:
        rgba(255, 255, 255, 0.04);

    border:
        1px solid
        rgba(148, 163, 184, 0.12);
}


.analytics-card-label {

    display: block;

    font-size: 14px;

    opacity: 0.7;

    margin-bottom: 10px;
}


.analytics-card-value {

    font-size: 26px;

    font-weight: 700;
}


.analytics-progress {

    width: 100%;

    height: 8px;

    margin-top: 15px;

    overflow: hidden;

    border-radius: 20px;

    background:
        rgba(148, 163, 184, 0.15);
}


.analytics-progress-bar {

    height: 100%;

    border-radius: 20px;

    background:
        #22c55e;
}


/* =========================================================
   LOW STOCK
========================================================= */

#low-stock-alert {

    scroll-margin-top: 25px;
}


/* =========================================================
   LIGHT THEME
========================================================= */

body.light-theme
.notification-panel {

    background: white;

    color: #1e293b;
}


body.light-theme
.analytics-card {

    background: white;

    border-color: #e2e8f0;
}


body.light-theme
.customer-admin-card,

body.light-theme
.customer-activity-card {

    background: white;

    border-color: #e2e8f0;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 1100px) {

    .customer-admin-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }
}


@media (max-width: 900px) {

    .analytics-grid {

        grid-template-columns:
            1fr;
    }


    .customer-activity-grid {

        grid-template-columns:
            1fr;
    }
}


@media (max-width: 600px) {

    .customer-admin-grid {

        grid-template-columns:
            1fr;
    }


    .dashboard-header-actions {

        gap: 10px;
    }


    .notification-panel {

        width: 280px;

        right: -70px;
    }
}



</style>


<div class="admin-layout">


    <?php

    include "../includes/sidebar.php";

    ?>


    <main class="admin-content">


        <!-- =================================================
             PAGE HEADER
        ================================================== -->

        <div class="page-header dashboard-header">

            <div>

                <p class="page-label">
                    DASHBOARD OVERVIEW
                </p>


                <h1>

                    Welcome back,

                    <?php

                    echo htmlspecialchars(
                        $_SESSION["username"]
                    );

                    ?>

                </h1>


                <p>

                    Here's what's happening with your
                    product inventory today.

                </p>

            </div>


            <div class="dashboard-header-actions">


                <!-- NOTIFICATION -->

                <div class="notification-wrapper">


                    <button
                        type="button"
                        class="notification-btn"
                        id="notificationToggle"
                    >

                        🔔


                        <?php

                        if ($lowStock > 0) {

                        ?>

                            <span
                                class="notification-count"
                            >

                                <?php

                                echo $lowStock;

                                ?>

                            </span>

                        <?php

                        }

                        ?>

                    </button>


                    <div
                        class="notification-panel"
                        id="notificationPanel"
                    >

                        <h3>
                            Low Stock Alerts
                        </h3>


                        <?php

                        if (
                            $lowStockNotifications &&
                            $lowStockNotifications->num_rows > 0
                        ) {

                            while (
                                $notificationProduct =
                                $lowStockNotifications->fetch_assoc()
                            ) {

                        ?>

                            <div
                                class="notification-item"
                            >

                                <span>

                                    <?php

                                    echo htmlspecialchars(
                                        $notificationProduct[
                                            "product_name"
                                        ]
                                    );

                                    ?>

                                </span>


                                <strong>

                                    <?php

                                    echo
                                        $notificationProduct[
                                            "quantity"
                                        ];

                                    ?>

                                    left

                                </strong>

                            </div>

                        <?php

                            }

                        } else {

                        ?>

                            <div
                                class="notification-item"
                            >

                                <span>
                                    No low stock alerts.
                                </span>

                            </div>

                        <?php

                        }

                        ?>


                        <a
                            href="#low-stock-alert"
                            class="notification-link"
                            id="viewLowStock"
                        >

                            View Low Stock Products →

                        </a>

                    </div>

                </div>


                <!-- ADD PRODUCT -->

                <a
                    href="add_product.php"
                    class="btn"
                >

                    + Add Product

                </a>


            </div>

        </div>


        <!-- =================================================
             INVENTORY STATISTICS
        ================================================== -->

        <div class="dashboard-cards">


            <!-- TOTAL PRODUCTS -->

            <div class="dashboard-card">

                <div class="dashboard-card-top">

                    <span
                        class="dashboard-card-label"
                    >
                        Total Products
                    </span>


                    <span
                        class="dashboard-card-icon"
                    >
                        📦
                    </span>

                </div>


                <h2>

                    <?php

                    echo $totalProducts;

                    ?>

                </h2>


                <p>
                    Products in inventory
                </p>

            </div>


            <!-- CATEGORIES -->

            <div class="dashboard-card">

                <div class="dashboard-card-top">

                    <span
                        class="dashboard-card-label"
                    >
                        Categories
                    </span>


                    <span
                        class="dashboard-card-icon"
                    >
                        🗂️
                    </span>

                </div>


                <h2>

                    <?php

                    echo $totalCategories;

                    ?>

                </h2>


                <p>
                    Product categories
                </p>

            </div>


            <!-- SUPPLIERS -->

            <div class="dashboard-card">

                <div class="dashboard-card-top">

                    <span
                        class="dashboard-card-label"
                    >
                        Suppliers
                    </span>


                    <span
                        class="dashboard-card-icon"
                    >
                        🚚
                    </span>

                </div>


                <h2>

                    <?php

                    echo $totalSuppliers;

                    ?>

                </h2>


                <p>
                    Registered suppliers
                </p>

            </div>


            <!-- LOW STOCK -->

            <div class="dashboard-card low-stock-card">

                <div class="dashboard-card-top">

                    <span
                        class="dashboard-card-label"
                    >
                        Low Stock
                    </span>


                    <span
                        class="dashboard-card-icon"
                    >
                        ⚠️
                    </span>

                </div>


                <h2>

                    <?php

                    echo $lowStock;

                    ?>

                </h2>


                <p>
                    Products need attention
                </p>

            </div>


        </div>


        <!-- =================================================
             CUSTOMER / SALES OVERVIEW
        ================================================== -->

        <div class="customer-admin-overview">


            <div class="customer-admin-title">

                <h2>
                    Customer & Sales Overview
                </h2>


                <p>
                    Monitor customer activity,
                    orders and customer product listings.
                </p>

            </div>


            <div class="customer-admin-grid">


                <!-- TOTAL CUSTOMERS -->

                <div class="customer-admin-card">

                    <div class="customer-admin-card-top">

                        <span
                            class="customer-admin-card-label"
                        >
                            Total Customers
                        </span>


                        <span
                            class="customer-admin-card-icon"
                        >
                            👥
                        </span>

                    </div>


                    <h3>

                        <?php

                        echo $totalCustomers;

                        ?>

                    </h3>


                    <p>
                        Registered customers
                    </p>

                </div>


                <!-- TOTAL ORDERS -->

                <div class="customer-admin-card">

                    <div class="customer-admin-card-top">

                        <span
                            class="customer-admin-card-label"
                        >
                            Total Orders
                        </span>


                        <span
                            class="customer-admin-card-icon"
                        >
                            🛒
                        </span>

                    </div>


                    <h3>

                        <?php

                        echo $totalOrders;

                        ?>

                    </h3>


                    <p>
                        Customer orders
                    </p>

                </div>


                <!-- TOTAL SALES -->

                <div class="customer-admin-card">

                    <div class="customer-admin-card-top">

                        <span
                            class="customer-admin-card-label"
                        >
                            Total Sales
                        </span>


                        <span
                            class="customer-admin-card-icon"
                        >
                            💰
                        </span>

                    </div>


                    <h3>

                        ₹<?php

                        echo number_format(
                            $totalOrderAmount,
                            2
                        );

                        ?>

                    </h3>


                    <p>
                        Total order value
                    </p>

                </div>


                <!-- CUSTOMER LISTINGS -->

                <div class="customer-admin-card">

                    <div class="customer-admin-card-top">

                        <span
                            class="customer-admin-card-label"
                        >
                            Customer Listings
                        </span>


                        <span
                            class="customer-admin-card-icon"
                        >
                            🏷️
                        </span>

                    </div>


                    <h3>

                        <?php

                        echo $totalCustomerProducts;

                        ?>

                    </h3>


                    <p>
                        Products submitted by customers
                    </p>

                </div>


            </div>

        </div>


        <!-- =================================================
             CUSTOMER ACTIVITY
        ================================================== -->

        <div class="customer-activity-section">


            <div class="section-heading">

                <div>

                    <h2>
                        Customer Activity
                    </h2>


                    <p>
                        Recent customer orders and
                        product submissions
                    </p>

                </div>

            </div>


            <div class="customer-activity-grid">


                <!-- =================================================
                     RECENT ORDERS
                ================================================== -->

                <div class="customer-activity-card">


                    <div class="customer-activity-header">

                        <h3>
                            Recent Orders
                        </h3>


                        <a href="orders.php">
                            View All →
                        </a>

                    </div>


                    <?php

                    if (
                        $recentCustomerOrders &&
                        $recentCustomerOrders->num_rows > 0
                    ) {

                        while (
                            $order =
                            $recentCustomerOrders->fetch_assoc()
                        ) {

                    ?>

                        <div
                            class="customer-activity-item"
                        >


                            <div
                                class="customer-activity-main"
                            >

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $order["username"]
                                    );

                                    ?>

                                </strong>


                                <span>

                                    Order #<?php

                                    echo $order["id"];

                                    ?>

                                    •

                                    <?php

                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $order["created_at"]
                                        )
                                    );

                                    ?>

                                </span>

                            </div>


                            <div
                                class="customer-activity-value"
                            >

                                <strong>

                                    ₹<?php

                                    echo number_format(
                                        (float)
                                        $order[
                                            "total_amount"
                                        ],
                                        2
                                    );

                                    ?>

                                </strong>


                                <span
                                    class="customer-status <?php

                                    echo strtolower(
                                        $order["status"]
                                    );

                                    ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $order["status"]
                                    );

                                    ?>

                                </span>

                            </div>


                        </div>

                    <?php

                        }

                    } else {

                    ?>

                        <div class="customer-empty">

                            No customer orders yet.

                        </div>

                    <?php

                    }

                    ?>

                </div>


                <!-- =================================================
                     RECENT CUSTOMER LISTINGS
                ================================================== -->

                <div class="customer-activity-card">


                    <div class="customer-activity-header">

                        <h3>
                            Recent Customer Listings
                        </h3>


                        <a href="products.php">
                            View Products →
                        </a>

                    </div>


                    <?php

                    if (
                        $recentCustomerProducts &&
                        $recentCustomerProducts->num_rows > 0
                    ) {

                        while (
                            $customerProduct =
                            $recentCustomerProducts->fetch_assoc()
                        ) {

                    ?>

                        <div
                            class="customer-activity-item"
                        >


                            <div
                                class="customer-activity-main"
                            >

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $customerProduct[
                                            "product_name"
                                        ]
                                    );

                                    ?>

                                </strong>


                                <span>

                                    By

                                    <?php

                                    echo htmlspecialchars(
                                        $customerProduct[
                                            "username"
                                        ]
                                    );

                                    ?>

                                    •

                                    <?php

                                    echo htmlspecialchars(
                                        $customerProduct[
                                            "category_name"
                                        ]
                                        ??
                                        "Uncategorized"
                                    );

                                    ?>

                                </span>

                            </div>


                            <div
                                class="customer-activity-value"
                            >

                                <strong>

                                    ₹<?php

                                    echo number_format(
                                        (float)
                                        $customerProduct[
                                            "price"
                                        ],
                                        2
                                    );

                                    ?>

                                </strong>


                                <span
                                    class="customer-status submitted"
                                >
                                    Submitted
                                </span>

                            </div>


                        </div>

                    <?php

                        }

                    } else {

                    ?>

                        <div class="customer-empty">

                            No customer listings yet.

                        </div>

                    <?php

                    }

                    ?>

                </div>


            </div>

        </div>


        <!-- =================================================
             ANALYTICS OVERVIEW
        ================================================== -->

        <div class="analytics-overview">


            <div class="analytics-title">

                <h2>
                    Inventory Analytics
                </h2>


                <p>
                    Quick insights about your inventory
                </p>

            </div>


            <div class="analytics-grid">


                <!-- INVENTORY VALUE -->

                <div class="analytics-card">

                    <span
                        class="analytics-card-label"
                    >
                        Total Inventory Value
                    </span>


                    <div
                        class="analytics-card-value"
                    >

                        ₹<?php

                        echo number_format(
                            $totalInventoryValue,
                            2
                        );

                        ?>

                    </div>

                </div>


                <!-- AVERAGE PRICE -->

                <div class="analytics-card">

                    <span
                        class="analytics-card-label"
                    >
                        Average Product Price
                    </span>


                    <div
                        class="analytics-card-value"
                    >

                        ₹<?php

                        echo number_format(
                            $averageProductPrice,
                            2
                        );

                        ?>

                    </div>

                </div>


                <!-- STOCK HEALTH -->

                <div class="analytics-card">

                    <span
                        class="analytics-card-label"
                    >
                        Stock Health
                    </span>


                    <div
                        class="analytics-card-value"
                    >

                        <?php

                        echo $stockHealth;

                        ?>%

                    </div>


                    <div
                        class="analytics-progress"
                    >

                        <div
                            class="analytics-progress-bar"
                            style="
                                width:
                                <?php

                                echo $stockHealth;

                                ?>%;
                            "
                        ></div>

                    </div>

                </div>


            </div>

        </div>


        <!-- =================================================
             DASHBOARD CHARTS
        ================================================== -->

        <div class="dashboard-charts">


            <!-- PRODUCTS BY CATEGORY -->

            <div class="chart-container">

                <div class="chart-header">

                    <div>

                        <h2>
                            Products by Category
                        </h2>


                        <p>
                            Product distribution
                            across categories
                        </p>

                    </div>

                </div>


                <div class="chart-canvas">

                    <canvas
                        id="categoryChart"
                    ></canvas>

                </div>

            </div>


            <!-- STOCK STATUS -->

            <div class="chart-container">

                <div class="chart-header">

                    <div>

                        <h2>
                            Stock Status
                        </h2>


                        <p>
                            Current inventory overview
                        </p>

                    </div>

                </div>


                <div class="chart-canvas">

                    <canvas
                        id="stockChart"
                    ></canvas>

                </div>

            </div>


        </div>


        <!-- =================================================
             QUICK ACTIONS
        ================================================== -->

        <div class="quick-actions-section">


            <div class="section-heading">

                <div>

                    <h2>
                        Quick Actions
                    </h2>


                    <p>
                        Quickly manage your inventory
                    </p>

                </div>

            </div>


            <div class="quick-actions-grid">


                <a
                    href="add_product.php"
                    class="quick-action-card"
                >

                    <span class="quick-action-icon">
                        +
                    </span>


                    <div>

                        <h3>
                            Add Product
                        </h3>


                        <p>
                            Create a new product
                        </p>

                    </div>

                </a>


                <a
                    href="products.php"
                    class="quick-action-card"
                >

                    <span class="quick-action-icon">
                        📦
                    </span>


                    <div>

                        <h3>
                            Manage Products
                        </h3>


                        <p>
                            View and update products
                        </p>

                    </div>

                </a>


                <a
                    href="categories.php"
                    class="quick-action-card"
                >

                    <span class="quick-action-icon">
                        🗂️
                    </span>


                    <div>

                        <h3>
                            Categories
                        </h3>


                        <p>
                            Manage product categories
                        </p>

                    </div>

                </a>


                <a
                    href="suppliers.php"
                    class="quick-action-card"
                >

                    <span class="quick-action-icon">
                        🚚
                    </span>


                    <div>

                        <h3>
                            Suppliers
                        </h3>


                        <p>
                            Manage your suppliers
                        </p>

                    </div>

                </a>


            </div>

        </div>


        <!-- =================================================
             LOW STOCK ALERT
        ================================================== -->

        <div
            class="dashboard-section"
            id="low-stock-alert"
        >


            <div class="section-heading">

                <div>

                    <h2>
                        ⚠️ Low Stock Alert
                    </h2>


                    <p>
                        Products that require restocking
                    </p>

                </div>


                <a
                    href="products.php?stock=low"
                    class="section-link"
                >
                    View All →
                </a>

            </div>


            <div class="table-container">


                <table>


                    <tr>

                        <th>
                            Product
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Quantity
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>


                    <?php

                    if (
                        $lowStockProducts &&
                        $lowStockProducts->num_rows > 0
                    ) {

                        while (
                            $product =
                            $lowStockProducts->fetch_assoc()
                        ) {

                    ?>

                        <tr>

                            <td>

                                <div
                                    class="product-table-info"
                                >

                                    <img
                                        src="../images/product_image.png"
                                        class="table-image"
                                        alt="Product"
                                    >


                                    <span>

                                        <?php

                                        echo htmlspecialchars(
                                            $product[
                                                "product_name"
                                            ]
                                        );

                                        ?>

                                    </span>

                                </div>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $product[
                                        "category_name"
                                    ]
                                    ??
                                    "N/A"
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo $product[
                                    "quantity"
                                ];

                                ?>

                            </td>


                            <td>

                                <span
                                    class="low-stock"
                                >
                                    Low Stock
                                </span>

                            </td>

                        </tr>

                    <?php

                        }

                    } else {

                    ?>

                        <tr>

                            <td colspan="4">

                                No low stock products.

                            </td>

                        </tr>

                    <?php

                    }

                    ?>

                </table>

            </div>

        </div>


        <!-- =================================================
             RECENT PRODUCTS
        ================================================== -->

        <div class="dashboard-section">


            <div class="section-heading">

                <div>

                    <h2>
                        Recent Products
                    </h2>


                    <p>
                        Latest products added
                        to your inventory
                    </p>

                </div>


                <a
                    href="products.php"
                    class="section-link"
                >
                    View All →
                </a>

            </div>


            <div class="table-container">


                <table>


                    <tr>

                        <th>
                            Product
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Supplier
                        </th>

                        <th>
                            Price
                        </th>

                        <th>
                            Quantity
                        </th>

                    </tr>


                    <?php

                    if (
                        $recentProducts &&
                        $recentProducts->num_rows > 0
                    ) {

                        while (
                            $product =
                            $recentProducts->fetch_assoc()
                        ) {

                    ?>

                        <tr>

                            <td>

                                <div
                                    class="product-table-info"
                                >

                                    <img
                                        src="../images/product_image.png"
                                        class="table-image"
                                        alt="Product"
                                    >


                                    <span>

                                        <?php

                                        echo htmlspecialchars(
                                            $product[
                                                "product_name"
                                            ]
                                        );

                                        ?>

                                    </span>

                                </div>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $product[
                                        "category_name"
                                    ]
                                    ??
                                    "N/A"
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $product[
                                        "supplier_name"
                                    ]
                                    ??
                                    "N/A"
                                );

                                ?>

                            </td>


                            <td>

                                ₹<?php

                                echo number_format(
                                    $product["price"],
                                    2
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo $product[
                                    "quantity"
                                ];

                                ?>

                            </td>

                        </tr>

                    <?php

                        }

                    } else {

                    ?>

                        <tr>

                            <td colspan="5">

                                No products available.

                            </td>

                        </tr>

                    <?php

                    }

                    ?>

                </table>

            </div>

        </div>


    </main>

</div>


<!-- =========================================================
     CHART.JS
========================================================= -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

/* =========================================================
   NOTIFICATION TOGGLE
========================================================= */

const notificationToggle =
    document.getElementById(
        "notificationToggle"
    );


const notificationPanel =
    document.getElementById(
        "notificationPanel"
    );


const viewLowStock =
    document.getElementById(
        "viewLowStock"
    );


if (
    notificationToggle &&
    notificationPanel
) {

    notificationToggle.addEventListener(
        "click",
        function() {

            notificationPanel.classList.toggle(
                "show"
            );

        }
    );

}


if (viewLowStock) {

    viewLowStock.addEventListener(
        "click",
        function() {

            notificationPanel.classList.remove(
                "show"
            );

        }
    );

}


/* =========================================================
   CLOSE NOTIFICATION
========================================================= */

document.addEventListener(
    "click",
    function(event) {

        if (
            notificationPanel &&
            notificationToggle
        ) {

            if (
                !notificationPanel.contains(
                    event.target
                ) &&
                !notificationToggle.contains(
                    event.target
                )
            ) {

                notificationPanel.classList.remove(
                    "show"
                );

            }

        }

    }
);


/* =========================================================
   CATEGORY CHART
========================================================= */

const categoryLabels = [

    <?php

    if (
        $categoryChart &&
        $categoryChart->num_rows > 0
    ) {

        $labels = [];


        while (
            $category =
            $categoryChart->fetch_assoc()
        ) {

            $labels[] =
                json_encode(
                    $category[
                        "category_name"
                    ]
                );

        }


        echo implode(
            ",",
            $labels
        );

    }

    ?>

];


const categoryData = [

    <?php

    $categoryChartData =
        $conn->query("
            SELECT
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
                total_products DESC
        ");


    if (
        $categoryChartData &&
        $categoryChartData->num_rows > 0
    ) {

        $dataValues = [];


        while (
            $category =
            $categoryChartData->fetch_assoc()
        ) {

            $dataValues[] =
                (int)$category[
                    "total_products"
                ];

        }


        echo implode(
            ",",
            $dataValues
        );

    }

    ?>

];


new Chart(

    document.getElementById(
        "categoryChart"
    ),

    {

        type: "bar",


        data: {

            labels:
                categoryLabels,


            datasets: [

                {

                    label:
                        "Products",


                    data:
                        categoryData,


                    backgroundColor:
                        "rgba(59, 130, 246, 0.75)",


                    borderRadius:
                        8,


                    borderSkipped:
                        false

                }

            ]

        },


        options: {

            responsive:
                true,


            maintainAspectRatio:
                false,


            plugins: {

                legend: {

                    display:
                        false

                }

            },


            scales: {

                x: {

                    ticks: {

                        color:
                            "#94a3b8"

                    },


                    grid: {

                        display:
                            false

                    }

                },


                y: {

                    beginAtZero:
                        true,


                    ticks: {

                        color:
                            "#94a3b8",

                        stepSize:
                            1

                    },


                    grid: {

                        color:
                            "rgba(148, 163, 184, 0.08)"

                    }

                }

            }

        }

    }

);


/* =========================================================
   STOCK STATUS CHART
========================================================= */

new Chart(

    document.getElementById(
        "stockChart"
    ),

    {

        type:
            "doughnut",


        data: {

            labels: [

                "Available",

                "Low Stock",

                "Out of Stock"

            ],


            datasets: [

                {

                    data: [

                        <?php

                        echo $availableStock;

                        ?>,


                        <?php

                        echo $lowStockCount;

                        ?>,


                        <?php

                        echo $outOfStock;

                        ?>

                    ],


                    backgroundColor: [

                        "#22c55e",

                        "#f59e0b",

                        "#ef4444"

                    ],


                    borderWidth:
                        0

                }

            ]

        },


        options: {

            responsive:
                true,


            maintainAspectRatio:
                false,


            cutout:
                "72%",


            plugins: {

                legend: {

                    position:
                        "bottom",


                    labels: {

                        color:
                            "#94a3b8",


                        padding:
                            20,


                        usePointStyle:
                            true

                    }

                }

            }

        }

    }

);

</script>


<?php

include "../includes/footer.php";

?>