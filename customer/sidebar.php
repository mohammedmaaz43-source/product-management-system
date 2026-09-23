
<?php

// =========================================
// CUSTOMER NAVBAR
// =========================================

$currentPage =
    basename($_SERVER["PHP_SELF"]);

?>

<nav class="customer-navbar">

    <!-- =================================
         LOGO
    ================================= -->

    <div class="customer-navbar-brand">

        <a href="dashboard.php">

            <div class="customer-navbar-logo">
                ◈
            </div>

            <div class="customer-navbar-brand-text">

                <strong>
                    PMS Store
                </strong>

                <span>
                    Customer Panel
                </span>

            </div>

        </a>

    </div>


    <!-- =================================
         NAVIGATION
    ================================= -->

    <div class="customer-navbar-links">

        <a
            href="dashboard.php"
            class="<?php
                echo $currentPage === "dashboard.php"
                    ? "active"
                    : "";
            ?>"
        >

            <span>
                ⌂
            </span>

            <span>
                Dashboard
            </span>

        </a>


        <a
            href="products.php"
            class="<?php
                echo $currentPage === "products.php"
                    ? "active"
                    : "";
            ?>"
        >

            <span>
                ▣
            </span>

            <span>
                Products
            </span>

        </a>


        <a
            href="cart.php"
            class="<?php
                echo $currentPage === "cart.php"
                    ? "active"
                    : "";
            ?>"
        >

            <span>
                🛒
            </span>

            <span>
                My Cart
            </span>

        </a>


        <a
            href="orders.php"
            class="<?php
                echo $currentPage === "orders.php"
                    ? "active"
                    : "";
            ?>"
        >

            <span>
                ▤
            </span>

            <span>
                My Orders
            </span>

        </a>


        <a
            href="sell_product.php"
            class="<?php
                echo $currentPage === "sell_product.php"
                    ? "active"
                    : "";
            ?>"
        >

            <span>
                ＋
            </span>

            <span>
                Sell Product
            </span>

        </a>


        <a
            href="my_products.php"
            class="<?php
                echo $currentPage === "my_products.php"
                    ? "active"
                    : "";
            ?>"
        >

            <span>
                ▦
            </span>

            <span>
                My Listings
            </span>

        </a>

    </div>


    <!-- =================================
         RIGHT SIDE
    ================================= -->

    <div class="customer-navbar-right">


        <a
            href="profile.php"
            class="<?php
                echo $currentPage === "profile.php"
                    ? "active"
                    : "";
            ?>"
        >

            <span>
                ◉
            </span>

            <span>
                Profile
            </span>

        </a>


        <a
            href="../logout.php"
            class="customer-navbar-logout"
        >

            <span>
                ↪
            </span>

            <span>
                Logout
            </span>

        </a>


    </div>

</nav>