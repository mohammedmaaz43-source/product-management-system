
<?php

/*
=========================================
PAGE TITLE
=========================================
*/

if (!isset($pageTitle)) {

    $pageTitle =
        "Product Management System";

}


/*
=========================================
CURRENT SCRIPT
=========================================
*/

$currentScript =
    $_SERVER["SCRIPT_NAME"] ?? "";


/*
=========================================
ROOT PATH
=========================================
*/

if (!isset($rootPath) || $rootPath === "") {

    /*
    -------------------------------------
    CUSTOMER FOLDER
    -------------------------------------
    */

    if (
        strpos(
            $currentScript,
            "/customer/"
        ) !== false
    ) {

        $rootPath = "../";

    }


    /*
    -------------------------------------
    ADMIN FOLDER
    -------------------------------------
    */

    elseif (
        strpos(
            $currentScript,
            "/admin/"
        ) !== false
    ) {

        $rootPath = "../";

    }


    /*
    -------------------------------------
    ROOT
    -------------------------------------
    */

    else {

        $rootPath = "";

    }

}


/*
=========================================
CUSTOMER PAGE CHECK
=========================================
*/

$isCustomerPage = false;


/*
=========================================
MANUAL CUSTOMER PAGE CHECK
=========================================
*/

if (
    isset($isCustomerPageCustom) &&
    $isCustomerPageCustom === true
) {

    $isCustomerPage = true;

}


/*
=========================================
AUTO CUSTOMER FOLDER DETECTION
=========================================
*/

if (
    strpos(
        $currentScript,
        "/customer/"
    ) !== false
) {

    $isCustomerPage = true;

}


/*
=========================================
CURRENT PAGE
=========================================
*/

$currentPage =
    basename(
        $_SERVER["PHP_SELF"]
    );


/*
=========================================
DASHBOARD LOADER
=========================================
*/

$showDashboardLoader = false;


if (
    isset($_SESSION["dashboard_loader"]) &&
    $_SESSION["dashboard_loader"] === true
) {

    $showDashboardLoader = true;


    unset(
        $_SESSION["dashboard_loader"]
    );

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <title>

        <?php

        echo htmlspecialchars(
            $pageTitle
        );

        ?>

    </title>


    <!-- =================================
         MAIN CSS
    ================================= -->

    <link
        rel="stylesheet"
        href="<?php
        echo $rootPath;
        ?>css/style.css"
    >


    <!-- =================================
         CUSTOMER CSS
    ================================= -->

    <?php

    if ($isCustomerPage) {

    ?>

        <link
            rel="stylesheet"
            href="<?php
            echo $rootPath;
            ?>css/customer.css"
        >

    <?php

    }

    ?>

</head>


<body
    class="<?php

    echo $isCustomerPage

        ? "customer-body"

        : "dark-theme";

    ?>"
>


<?php

/*
=========================================
CUSTOMER TOP NAVBAR
=========================================
*/

if ($isCustomerPage) {

?>

<nav class="customer-navbar">


    <!-- =================================
         BRAND
    ================================= -->

    <div class="customer-navbar-brand">

        <a
            href="<?php
            echo $rootPath;
            ?>customer/dashboard.php"
        >


            <div class="customer-navbar-logo">

                <img
                    src="<?php
                    echo $rootPath;
                    ?>images/my_logo.png"
                    alt="PMS Logo"
                >

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
         MAIN NAVIGATION
    ================================= -->

    <div class="customer-navbar-links">


        <!-- DASHBOARD -->

        <a
            href="<?php
            echo $rootPath;
            ?>customer/dashboard.php"

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



        <!-- PRODUCTS -->

        <a
            href="<?php
            echo $rootPath;
            ?>customer/products.php"

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



        <!-- CART -->

        <a
            href="<?php
            echo $rootPath;
            ?>customer/cart.php"

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



        <!-- ORDERS -->

        <a
            href="<?php
            echo $rootPath;
            ?>customer/orders.php"

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



        <!-- SELL PRODUCT -->

        <a
            href="<?php
            echo $rootPath;
            ?>customer/sell_product.php"

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



        <!-- MY LISTINGS -->

        <a
            href="<?php
            echo $rootPath;
            ?>customer/my_products.php"

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
        
        <!-- REPORTS -->

<a 
    href="<?php 
    echo $rootPath; 
    ?>customer/reports.php"
    class="<?php 

    echo $currentPage === "reports.php"
        ? "active"
        : "";

    ?>"
>

    <span>
        ◫
    </span>

    <span>
        Reports
    </span>

</a>


    </div>



    <!-- =================================
         RIGHT SIDE
    ================================= -->

    <div class="customer-navbar-right">


        <!-- PROFILE -->

        <a
            href="<?php
            echo $rootPath;
            ?>customer/profile.php"

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



        <!-- LOGOUT -->

        <a
            href="<?php
            echo $rootPath;
            ?>logout.php"

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

<?php

}


/*
=========================================
DASHBOARD LOADER
=========================================
*/

if ($showDashboardLoader) {

?>

<div
    id="dashboardLoadingScreen"
    class="loading-screen"
>


    <div
        class="background-orb orb-one"
    ></div>


    <div
        class="background-orb orb-two"
    ></div>


    <div
        class="background-orb orb-three"
    ></div>


    <div
        class="futuristic-loader-card"
    >


        <div
            class="loader-ring"
        >


            <div
                class="loader-ring-inner"
            >


                <div
                    class="loader-pms"
                >
                    PMS
                </div>


                <div
                    class="loader-system"
                >
                    SYSTEM
                </div>


            </div>


        </div>


        <h2>
            Loading Dashboard
        </h2>


        <p
            class="loader-subtitle"
        >
            Preparing your workspace
        </p>


        <div
            class="progress-wrapper"
        >


            <div
                class="loader-progress-container"
            >

                <div
                    id="dashboardLoaderProgress"
                    class="loader-progress"
                ></div>

            </div>


            <span
                id="dashboardLoaderPercentage"
                class="loader-percentage"
            >
                0%
            </span>


        </div>


        <div
            class="loading-dots"
        >

            <span></span>

            <span></span>

            <span></span>

        </div>


    </div>


</div>

<?php

}

?>