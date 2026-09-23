
<?php
$pageTitle = "Product Management System";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $pageTitle; ?></title>

    <!-- HOME CSS -->
    <link rel="stylesheet" href="css/home.css?v=2">
</head>

<body>

    <!-- ================= NAVBAR ================= -->

    <header class="navbar">

        <div class="brand">

            <img
        src="images/my_logo.png"
        alt="Product Management System Logo"
        class="brand-logo"
        width="45"
        height="45"
    >

            <div class="brand-text">

                <h3>
                    Product Management System
                </h3>

                <span>
                    Smart Product & Inventory Management
                </span>

            </div>

        </div>


        <div class="nav-buttons">

            <a href="login.php" class="login-btn">
                Login
            </a>

            <a href="register.php" class="signup-btn">
                Sign Up
            </a>

        </div>

    </header>


    <!-- ================= HERO SECTION ================= -->

    <section class="hero">

        <div class="hero-content">

            <div class="badge">
                PRODUCT MANAGEMENT SYSTEM
            </div>

            <h1>
                Manage Your
                <span>Products</span>
                Easily & Efficiently
            </h1>

            <p>
                Product Management System is a modern platform
                designed to simplify product, inventory, supplier,
                customer and order management in one place.
            </p>


            <div class="hero-actions">

                <a href="login.php" class="primary-btn">
                    Login to System
                    <span>→</span>
                </a>

                <a href="register.php" class="outline-btn">
                    Create Account
                </a>

            </div>

        </div>


        <!-- ================= DASHBOARD PREVIEW ================= -->

        <div class="dashboard-preview">

            <div class="preview-header">

                <div>

                    <span>
                        PRODUCT MANAGEMENT
                    </span>

                    <h3>
                        System Overview
                    </h3>

                </div>

                <div class="preview-dot"></div>

            </div>


            <div class="overview-cards">

                <div class="overview-card">

                    <div class="card-icon">
                        P
                    </div>

                    <div>
                        <small>Products</small>
                        <strong>Manage</strong>
                    </div>

                </div>


                <div class="overview-card">

                    <div class="card-icon">
                        I
                    </div>

                    <div>
                        <small>Inventory</small>
                        <strong>Control</strong>
                    </div>

                </div>


                <div class="overview-card">

                    <div class="card-icon">
                        O
                    </div>

                    <div>
                        <small>Orders</small>
                        <strong>Track</strong>
                    </div>

                </div>


                <div class="overview-card">

                    <div class="card-icon">
                        C
                    </div>

                    <div>
                        <small>Customers</small>
                        <strong>Connect</strong>
                    </div>

                </div>

            </div>


            <div class="preview-bottom">

                <div class="activity-title">
                    System Management
                </div>

                <div class="activity-line">

                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>

                </div>

            </div>

        </div>

    </section>


    <!-- ================= INFORMATION ================= -->

    <section class="information">

        <div class="section-title">

            <span>
                ABOUT THE SYSTEM
            </span>

            <h2>
                Everything You Need
                <br>
                For Product Management
            </h2>

            <p>
                A centralized system that provides simple and
                organized management of products and business
                operations.
            </p>

        </div>


        <div class="info-grid">

            <div class="info-card">

                <div class="info-number">
                    01
                </div>

                <div class="info-icon">
                    ▣
                </div>

                <h3>
                    Product Management
                </h3>

                <p>
                    Products can be added, updated, viewed and
                    managed through an organized management system.
                </p>

            </div>


            <div class="info-card">

                <div class="info-number">
                    02
                </div>

                <div class="info-icon">
                    ◫
                </div>

                <h3>
                    Inventory Management
                </h3>

                <p>
                    Product quantities and inventory information
                    can be managed efficiently from the system.
                </p>

            </div>


            <div class="info-card">

                <div class="info-number">
                    03
                </div>

                <div class="info-icon">
                    ◇
                </div>

                <h3>
                    Supplier Management
                </h3>

                <p>
                    Supplier information can be organized and
                    maintained for better product management.
                </p>

            </div>


            <div class="info-card">

                <div class="info-number">
                    04
                </div>

                <div class="info-icon">
                    ◎
                </div>

                <h3>
                    Order Management
                </h3>

                <p>
                    Customer orders can be managed and their
                    status can be tracked through the system.
                </p>

            </div>

        </div>

    </section>


    <!-- ================= USER TYPES ================= -->

    <section class="user-section">

        <div class="user-content">

            <span class="section-label">
                SYSTEM ACCESS
            </span>

            <h2>
                Designed For
                <span>Different Users</span>
            </h2>

            <p>
                The system provides separate functionality for
                administrators and customers according to their
                requirements.
            </p>

        </div>


        <div class="user-grid">

            <div class="user-card">

                <div class="user-icon">
                    A
                </div>

                <h3>
                    Administrator
                </h3>

                <p>
                    Administrators can manage products, categories,
                    suppliers, inventory, reports and other system
                    operations.
                </p>

                <div class="user-features">

                    <span>✓ Product Management</span>
                    <span>✓ Category Management</span>
                    <span>✓ Supplier Management</span>
                    <span>✓ Reports</span>

                </div>

            </div>


            <div class="user-card">

                <div class="user-icon">
                    C
                </div>

                <h3>
                    Customer
                </h3>

                <p>
                    Customers can browse products, view product
                    information, manage their cart and place orders.
                </p>

                <div class="user-features">

                    <span>✓ Browse Products</span>
                    <span>✓ Product Details</span>
                    <span>✓ Shopping Cart</span>
                    <span>✓ Order Management</span>

                </div>

            </div>

        </div>

    </section>


    <!-- ================= FINAL INFORMATION ================= -->

    <section class="final-section">

        <div class="final-box">

            <span>
                PRODUCT MANAGEMENT SYSTEM
            </span>

            <h2>
                Simple. Organized. Efficient.
            </h2>

            <p>
                Manage your products and business operations
                through one centralized management system.
            </p>


            <div class="final-buttons">

                <a href="login.php" class="primary-btn">

                    Login

                    <span>→</span>

                </a>

                <a href="register.php" class="outline-btn">
                    Sign Up
                </a>

            </div>

        </div>

    </section>


    <!-- ================= FOOTER ================= -->

<footer class="footer">

    <div class="footer-brand">

        <img
            src="images/my_logo.png"
            alt="Product Management System Logo"
            width="32"
            height="32"
        >

        <span>
            Product Management System
        </span>

    </div>

    <p>
        © <?php echo date("Y"); ?>
        Product Management System. All Rights Reserved.
    </p>

</footer>


</body>

</html>