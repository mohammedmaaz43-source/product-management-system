
<?php

$currentPage =
    basename(
        $_SERVER["PHP_SELF"]
    );


$userRole =
    $_SESSION["role"] ?? "user";

?>

<aside class="sidebar">

    <div class="sidebar-logo">

        <img
            src="../images/my_logo.png"
            alt="PMS Logo"
        >

        <div>

            <h2>PMS</h2>

            <p>
                Product Management
            </p>

        </div>

    </div>


    <p class="menu-title">
        MAIN MENU
    </p>


    <nav class="sidebar-menu">


        <!-- DASHBOARD -->

        <a
            href="dashboard.php"
            class="<?php
            echo $currentPage ==
            'dashboard.php'
            ? 'active'
            : '';
            ?>"
        >
            Dashboard
        </a>



        <!-- PRODUCTS -->

        <a
            href="products.php"
            class="<?php
            echo $currentPage ==
            'products.php'
            ? 'active'
            : '';
            ?>"
        >
            Products
        </a>



        <!-- CATEGORIES -->

        <a
            href="categories.php"
            class="<?php
            echo $currentPage ==
            'categories.php'
            ? 'active'
            : '';
            ?>"
        >
            Categories
        </a>



        <!-- SUPPLIERS -->

        <a
            href="suppliers.php"
            class="<?php
            echo $currentPage ==
            'suppliers.php'
            ? 'active'
            : '';
            ?>"
        >
            Suppliers
        </a>



        <!-- ADMIN ONLY -->

        <?php
        if ($userRole === "admin") {
        ?>

            <!-- REPORTS -->

            <a
                href="reports.php"
                class="<?php
                echo $currentPage ==
                'reports.php'
                ? 'active'
                : '';
                ?>"
            >
                Reports
            </a>


        <?php
        }
        ?>


        <!-- PROFILE -->

        <a
            href="profile.php"
            class="<?php
            echo $currentPage ==
            'profile.php'
            ? 'active'
            : '';
            ?>"
        >
            My Profile
        </a>


    </nav>


    <div class="sidebar-bottom">


        <!-- DARK / LIGHT THEME BUTTON -->

        <button
            type="button"
            id="themeToggle"
            class="theme-toggle"
        >
            ☀️ Light Mode
        </button>



        <!-- LOGOUT -->

        <a
            href="../logout.php"
            class="logout"
        >
            Logout
        </a>


    </div>

</aside>