
<?php

session_start();

require_once "includes/db.php";


$error = "";

$showLoader = false;


/*
=================================
SHOW LOADER AFTER SUCCESSFUL LOGIN
=================================
*/

if (isset($_SESSION["show_loader"])) {

    $showLoader = true;

    unset($_SESSION["show_loader"]);

}


/*
=================================
ALREADY LOGGED IN
=================================
*/

if (
    isset($_SESSION["user_id"])
    &&
    !$showLoader
) {

    if (
        isset($_SESSION["role"])
        &&
        $_SESSION["role"] === "admin"
    ) {

        header(
            "Location: admin/dashboard.php"
        );

    } else {

        header(
            "Location: customer/dashboard.php"
        );

    }

    exit();

}


/*
=================================
LOGIN PROCESS
=================================
*/

if (isset($_POST["login"])) {

    $username =
        trim($_POST["username"]);

    $password =
        $_POST["password"];


    /*
    =============================
    VALIDATION
    =============================
    */

    if (
        empty($username)
        ||
        empty($password)
    ) {

        $error =
            "Please enter username and password.";

    } else {


        /*
        =============================
        GET USER
        =============================
        */

        $stmt = $conn->prepare(
            "SELECT
                id,
                username,
                password,
                role
             FROM users
             WHERE username = ?"
        );


        $stmt->bind_param(
            "s",
            $username
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        /*
        =============================
        CHECK USER
        =============================
        */

        if (
            $result->num_rows === 1
        ) {

            $user =
                $result->fetch_assoc();


            /*
            =============================
            VERIFY PASSWORD
            =============================
            */

            if (
                password_verify(
                    $password,
                    $user["password"]
                )
            ) {


                /*
                =============================
                CREATE USER SESSION
                =============================
                */

                $_SESSION["user_id"] =
                    $user["id"];

                $_SESSION["username"] =
                    $user["username"];

                $_SESSION["role"] =
                    $user["role"];


                /*
                =============================
                SHOW LOADER ONE TIME
                =============================
                */

                $_SESSION["show_loader"] =
                    true;


                /*
                =============================
                RELOAD LOGIN PAGE
                =============================
                */

                header(
                    "Location: login.php"
                );

                exit();


            } else {

                $error =
                    "Invalid username or password.";

            }


        } else {

            $error =
                "Invalid username or password.";

        }


        $stmt->close();

    }

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
        Login | Product Management System
    </title>


    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body class="login-page">


<?php if ($showLoader) { ?>


<!-- =================================
     LOGIN SUCCESS LOADER
================================= -->

<div
    id="loadingScreen"
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


        <div class="loader-ring">


            <div
                class="loader-ring-inner"
            >


                <div class="loader-pms">
                    PMS
                </div>


                <div class="loader-system">
                    SYSTEM
                </div>


            </div>


        </div>


        <h2>
            Login Successful
        </h2>


        <p class="loader-subtitle">
            Preparing your dashboard
        </p>


        <div class="progress-wrapper">


            <div
                class="loader-progress-container"
            >

                <div
                    id="loaderProgress"
                    class="loader-progress"
                ></div>

            </div>


            <span
                id="loaderPercentage"
                class="loader-percentage"
            >
                0%
            </span>


        </div>


        <div class="loading-dots">

            <span></span>

            <span></span>

            <span></span>

        </div>


        <p class="wait-text">
            Please wait a moment...
        </p>


    </div>


</div>


<?php } ?>


<!-- =================================
     NORMAL LOGIN PAGE
================================= -->

<div class="login-wrapper">


    <!-- =================================
         LEFT SIDE
    ================================== -->

    <div class="login-brand-section">


        <a
            href="index.php"
            class="login-logo"
        >


            <div class="login-logo-icon">
                PMS
            </div>


            <div>

                <h2>
                    Product Management
                </h2>

                <span>
                    Smart Inventory System
                </span>

            </div>


        </a>


        <div class="login-brand-content">


            <div class="login-badge">
                USER PORTAL
            </div>


            <h1>

                <span
                    id="typing-text"
                ></span>

            </h1>


            <p>

                Access your account and manage
                products, orders and inventory
                from one centralized platform.

            </p>


            <!-- FEATURE 1 -->

            <div class="login-feature-list">


                <div class="login-feature">


                    <div class="feature-check">
                        ✓
                    </div>


                    <div>

                        <strong>
                            Smart Product Management
                        </strong>

                        <span>
                            Manage products with ease
                        </span>

                    </div>


                </div>


                <!-- FEATURE 2 -->

                <div class="login-feature">


                    <div class="feature-check">
                        ✓
                    </div>


                    <div>

                        <strong>
                            Secure Account Access
                        </strong>

                        <span>
                            Protected access to your account
                        </span>

                    </div>


                </div>


                <!-- FEATURE 3 -->

                <div class="login-feature">


                    <div class="feature-check">
                        ✓
                    </div>


                    <div>

                        <strong>
                            Complete Platform Access
                        </strong>

                        <span>
                            Products, orders and marketplace
                        </span>

                    </div>


                </div>


            </div>


        </div>


        <div class="login-brand-footer">

            © <?php echo date("Y"); ?>

            Product Management System

        </div>


    </div>



    <!-- =================================
         RIGHT SIDE
    ================================== -->

    <div class="login-form-section">


        <a
            href="index.php"
            class="back-home"
        >
            ← Back to Home
        </a>


        <div class="login-card">


            <div class="login-card-header">


                <div class="login-icon">
                    🔐
                </div>


                <h2>
                    Welcome Back
                </h2>


                <p>
                    Sign in to access your account.
                </p>


            </div>



            <!-- =================================
                 ERROR MESSAGE
            ================================== -->

            <?php if (!empty($error)) { ?>


                <div class="login-error">


                    <span>
                        !
                    </span>


                    <?php

                    echo htmlspecialchars(
                        $error
                    );

                    ?>


                </div>


            <?php } ?>



            <!-- =================================
                 LOGIN FORM
            ================================== -->

            <form
                method="POST"
                action=""
            >


                <!-- USERNAME -->

                <div class="login-input-group">


                    <label>
                        Username
                    </label>


                    <div class="login-input">


                        <span class="input-icon">
                            👤
                        </span>


                        <input
                            type="text"
                            name="username"
                            placeholder="Enter your username"
                            required
                        >


                    </div>


                </div>



                <!-- PASSWORD -->

                <div class="login-input-group">


                    <label>
                        Password
                    </label>


                    <div class="login-input">


                        <span class="input-icon">
                            🔒
                        </span>


                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Enter your password"
                            required
                        >


                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword()"
                        >
                            👁
                        </button>


                    </div>


                </div>



                <!-- OPTIONS -->

                <div class="login-options">


                    <label class="remember-option">


                        <input
                            type="checkbox"
                            name="remember"
                        >


                        <span>
                            Remember me
                        </span>


                    </label>


                    <a
                        href="forgot_password.php"
                    >
                        Forgot Password?
                    </a>


                </div>



                <!-- LOGIN BUTTON -->

                <button
                    type="submit"
                    name="login"
                    class="login-submit-btn"
                >

                    Sign In

                    <span>
                        →
                    </span>

                </button>


            </form>



            <!-- =================================
                 REGISTER
            ================================== -->

            <div class="login-divider">

                <span>
                    New to Product Management System?
                </span>

            </div>


            <div class="register-login-link">


                <a
                    href="register.php"
                >
                    Create Account
                </a>


            </div>



            <!-- =================================
                 SECURITY NOTE
            ================================== -->

            <div class="login-divider">

                <span>
                    Secure Account Access
                </span>

            </div>


            <div class="login-security-note">


                <span>
                    🔒
                </span>


                Your login information is securely protected.


            </div>


        </div>


    </div>


</div>



<!-- =================================
     COMMON JAVASCRIPT
================================= -->

<script src="js/script.js"></script>


<script>

/*
=================================
TYPING TEXT
=================================
*/

document.addEventListener(
    "DOMContentLoaded",
    function () {


        const text =
            "Manage your inventory with confidence.";


        const typingElement =
            document.getElementById(
                "typing-text"
            );


        let index = 0;


        function typeText() {


            if (
                typingElement
                &&
                index < text.length
            ) {


                typingElement.innerHTML +=
                    text.charAt(index);


                index++;


                setTimeout(
                    typeText,
                    70
                );


            }

        }


        typeText();


    }
);



/*
=================================
LOGIN LOADER
=================================
*/

const loadingScreen =
    document.getElementById(
        "loadingScreen"
    );


if (loadingScreen) {


    const progress =
        document.getElementById(
            "loaderProgress"
        );


    const percentageText =
        document.getElementById(
            "loaderPercentage"
        );


    let percentage = 0;


    const loaderInterval =
        setInterval(
            function () {


                percentage++;


                progress.style.width =
                    percentage + "%";


                percentageText.innerHTML =
                    percentage + "%";


                if (
                    percentage >= 100
                ) {


                    clearInterval(
                        loaderInterval
                    );


                    setTimeout(
                        function () {


                            /*
                            =============================
                            ROLE BASED REDIRECT
                            =============================
                            */

                            const userRole =
                                "<?php

                                echo isset(
                                    $_SESSION["role"]
                                )
                                    ? htmlspecialchars(
                                        $_SESSION["role"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    )
                                    : "";

                                ?>";


                            if (
                                userRole === "admin"
                            ) {


                                window.location.href =
                                    "admin/dashboard.php";


                            } else {


                                window.location.href =
                                    "customer/dashboard.php";


                            }


                        },
                        400
                    );


                }


            },
            30
        );

}

</script>


</body>

</html>