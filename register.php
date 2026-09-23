
<?php

session_start();

require_once "includes/db.php";


$error = "";
$success = "";

$showLoader = false;


/*
=================================
IF USER IS ALREADY LOGGED IN
=================================
*/

if (isset($_SESSION["user_id"])) {

    if (
        isset($_SESSION["role"]) &&
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
REGISTRATION PROCESS
=================================
*/

if (isset($_POST["register"])) {

    $username =
        trim($_POST["username"]);

    $password =
        $_POST["password"];

    $confirmPassword =
        $_POST["confirm_password"];


    /*
    =============================
    VALIDATION
    =============================
    */

    if (
        empty($username) ||
        empty($password) ||
        empty($confirmPassword)
    ) {

        $error =
            "Please fill in all fields.";

    } elseif (
        $password !== $confirmPassword
    ) {

        $error =
            "Passwords do not match.";

    } elseif (
        strlen($password) < 6
    ) {

        $error =
            "Password must be at least 6 characters.";

    } else {


        /*
        =============================
        CHECK EXISTING USERNAME
        =============================
        */

        $checkUser =
            $conn->prepare(
                "SELECT id
                 FROM users
                 WHERE username = ?"
            );


        $checkUser->bind_param(
            "s",
            $username
        );


        $checkUser->execute();


        $result =
            $checkUser->get_result();


        if (
            $result->num_rows > 0
        ) {

            $error =
                "Username already exists.";

        } else {


            /*
            =============================
            HASH PASSWORD
            =============================
            */

            $hashedPassword =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            /*
            =============================
            NEW ACCOUNT
            =============================
            
            Every account created through
            registration is a normal user.
            
            Only the existing admin account
            should have role = admin.
            */

            $role =
                "user";


            /*
            =============================
            INSERT USER
            =============================
            */

            $stmt =
                $conn->prepare(
                    "INSERT INTO users
                    (
                        username,
                        password,
                        role
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?
                    )"
                );


            $stmt->bind_param(
                "sss",
                $username,
                $hashedPassword,
                $role
            );


            /*
            =============================
            SUCCESSFUL REGISTRATION
            =============================
            */

            if ($stmt->execute()) {


                /*
                =============================
                AUTOMATIC LOGIN
                =============================
                */

                $_SESSION["user_id"] =
                    $conn->insert_id;


                $_SESSION["username"] =
                    $username;


                $_SESSION["role"] =
                    "user";


                /*
                =============================
                SHOW LOADER
                =============================
                */

                $showLoader =
                    true;


            } else {

                $error =
                    "Registration failed. Please try again.";

            }


            $stmt->close();

        }


        $checkUser->close();

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
        Register | Product Management System
    </title>


    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body class="login-page">


<?php if ($showLoader) { ?>


<!-- =========================================
     PMS FUTURISTIC LOADING SCREEN
========================================= -->

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

            <div class="loader-ring-inner">


                <div class="loader-pms">
                    PMS
                </div>


                <div class="loader-system">
                    SYSTEM
                </div>


            </div>

        </div>


        <h2>
            Product Management System
        </h2>


        <p class="loader-subtitle">
            Creating your workspace
        </p>


        <div class="progress-wrapper">


            <div class="loader-progress-container">

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


<!-- =========================================
     REGISTER PAGE
========================================= -->

<div class="login-wrapper">


    <!-- =================================
         LEFT SIDE
    ================================= -->

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
                CREATE ACCOUNT
            </div>


            <h1>

                <span
                    id="typing-text"
                ></span>

            </h1>


            <p>

                Create your account and access the
                product management platform.

            </p>


            <div class="login-feature-list">


                <!-- FEATURE 1 -->

                <div class="login-feature">


                    <div class="feature-check">
                        ✓
                    </div>


                    <div>

                        <strong>
                            Manage Products
                        </strong>

                        <span>
                            View and manage products
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
                            Browse Categories
                        </strong>

                        <span>
                            Access available categories
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
                            Secure Account
                        </strong>

                        <span>
                            Your password is securely protected
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
    ================================= -->

    <div class="login-form-section">


        <a
            href="login.php"
            class="back-home"
        >
            ← Back to Login
        </a>


        <div class="login-card">


            <div class="login-card-header">


                <div class="login-icon">
                    👤
                </div>


                <h2>
                    Create Account
                </h2>


                <p>
                    Register to access the system.
                </p>


            </div>



            <!-- =================================
                 ERROR MESSAGE
            ================================= -->

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
                 REGISTRATION FORM
            ================================= -->

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
                            placeholder="Choose a username"
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
                            placeholder="Create a password"
                            required
                        >


                    </div>


                </div>



                <!-- CONFIRM PASSWORD -->

                <div class="login-input-group">


                    <label>
                        Confirm Password
                    </label>


                    <div class="login-input">


                        <span class="input-icon">
                            🔐
                        </span>


                        <input
                            type="password"
                            name="confirm_password"
                            id="confirmPassword"
                            placeholder="Confirm your password"
                            required
                        >


                    </div>


                </div>



                <!-- CREATE ACCOUNT BUTTON -->

                <button
                    type="submit"
                    name="register"
                    class="login-submit-btn"
                >

                    Create Account

                    <span>
                        →
                    </span>

                </button>


            </form>



            <!-- =================================
                 LOGIN LINK
            ================================= -->

            <div class="login-divider">

                <span>
                    Already have an account?
                </span>

            </div>


            <div class="register-login-link">


                <a
                    href="login.php"
                >
                    Sign In
                </a>


            </div>


        </div>


    </div>


</div>



<script src="js/script.js"></script>


<?php if ($showLoader) { ?>


<script>

/*
=================================
REGISTRATION LOADER
=================================
*/

document.addEventListener(
    "DOMContentLoaded",
    function () {


        const progress =
            document.getElementById(
                "loaderProgress"
            );


        const percentage =
            document.getElementById(
                "loaderPercentage"
            );


        let value = 0;


        const loadingInterval =
            setInterval(
                function () {


                    value += 1;


                    if (progress) {

                        progress.style.width =
                            value + "%";

                    }


                    if (percentage) {

                        percentage.textContent =
                            value + "%";

                    }


                    if (value >= 100) {


                        clearInterval(
                            loadingInterval
                        );


                        setTimeout(
                            function () {


                                /*
                                =============================
                                NEW USERS ALWAYS GO TO
                                CUSTOMER DASHBOARD
                                =============================
                                */

                                window.location.href =
                                    "customer/dashboard.php";


                            },
                            400
                        );

                    }


                },
                25
            );


    }
);

</script>


<?php } ?>


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
            "Join Product Management System";


        const typingElement =
            document.getElementById(
                "typing-text"
            );


        let index = 0;


        function typeText() {


            if (
                typingElement &&
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

</script>


</body>

</html>