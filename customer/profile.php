<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "My Profile";
$rootPath = "../";
$isCustomerPageCustom = true;

$userId = (int) $_SESSION["user_id"];

$message = "";
$error = "";


/* =========================================================
   UPDATE PROFILE
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $newPassword = trim($_POST["new_password"] ?? "");


    /* =====================================================
       BASIC VALIDATION
       ===================================================== */

    if ($username === "") {

        $error = "Username cannot be empty.";

    } elseif (strlen($username) < 3) {

        $error = "Username must contain at least 3 characters.";

    } else {


        /* =================================================
           CHECK DUPLICATE USERNAME
        ================================================= */

        $checkStmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE username = ?
            AND id != ?
            LIMIT 1
        ");

        $checkStmt->bind_param(
            "si",
            $username,
            $userId
        );

        $checkStmt->execute();

        $checkResult = $checkStmt->get_result();


        if ($checkResult->num_rows > 0) {

            $error = "This username is already taken.";

        }


        $checkStmt->close();


        /* =================================================
           UPDATE PROFILE
        ================================================= */

        if ($error === "") {


            /* =============================================
               UPDATE USERNAME + PASSWORD
            ============================================= */

            if ($newPassword !== "") {


                if (strlen($newPassword) < 6) {

                    $error =
                        "New password must contain at least 6 characters.";

                } else {


                    $hashedPassword =
                        password_hash(
                            $newPassword,
                            PASSWORD_DEFAULT
                        );


                    $stmt = $conn->prepare("
                        UPDATE users
                        SET
                            username = ?,
                            password = ?
                        WHERE id = ?
                    ");

                    $stmt->bind_param(
                        "ssi",
                        $username,
                        $hashedPassword,
                        $userId
                    );

                }


            } else {


                /* =========================================
                   UPDATE USERNAME ONLY
                ========================================= */

                $stmt = $conn->prepare("
                    UPDATE users
                    SET username = ?
                    WHERE id = ?
                ");

                $stmt->bind_param(
                    "si",
                    $username,
                    $userId
                );

            }


            /* =============================================
               EXECUTE UPDATE
            ============================================= */

            if ($error === "") {


                if ($stmt->execute()) {


                    $_SESSION["username"] = $username;


                    $message =
                        "Profile updated successfully.";


                } else {


                    $error =
                        "Unable to update profile.";

                }


                $stmt->close();

            }

        }

    }

}


/* =========================================================
   GET CURRENT USER
   ========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        username,
        role
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $userId
);

$stmt->execute();

$result =
    $stmt->get_result();

$user =
    $result->fetch_assoc();

$stmt->close();


/* =========================================================
   USER NOT FOUND
   ========================================================= */

if (!$user) {

    header("Location: ../logout.php");
    exit();

}


/* =========================================================
   FIRST LETTER FOR AVATAR
   ========================================================= */

$firstLetter =
    strtoupper(
        substr(
            $user["username"],
            0,
            1
        )
    );


/* =========================================================
   HEADER
   ========================================================= */

include "../includes/header.php";

?>


<!-- =========================================================
     CUSTOMER PROFILE PAGE
     NO SIDEBAR
     ========================================================= -->

<main class="customer-content">


    <!-- =================================================
         PAGE HEADER
         ================================================= -->

    <div class="customer-page-header">

        <div>

            <h1>
                My Profile
            </h1>

            <p>
                Manage your account information and password.
            </p>

        </div>

    </div>


    <!-- =================================================
         PROFILE WRAPPER
         ================================================= -->

    <div class="customer-profile-wrapper">


        <!-- =================================================
             MAIN PROFILE CARD
             ================================================= -->

        <div class="customer-profile-card">


            <!-- PROFILE HEADER -->

            <div class="customer-profile-top">


                <div class="customer-profile-avatar">

                    <?php

                    echo htmlspecialchars(
                        $firstLetter
                    );

                    ?>

                </div>


                <div class="customer-profile-heading">

                    <h2>

                        <?php

                        echo htmlspecialchars(
                            $user["username"]
                        );

                        ?>

                    </h2>


                    <p>
                        Customer Account
                    </p>

                </div>


            </div>


            <!-- =================================================
                 SUCCESS MESSAGE
                 ================================================= -->

            <?php if ($message !== "") { ?>

                <div class="profile-success-message">

                    <span>
                        ✓
                    </span>

                    <?php

                    echo htmlspecialchars(
                        $message
                    );

                    ?>

                </div>

            <?php } ?>


            <!-- =================================================
                 ERROR MESSAGE
                 ================================================= -->

            <?php if ($error !== "") { ?>

                <div class="profile-error-message">

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


            <!-- =================================================
                 PROFILE FORM
                 ================================================= -->

            <form
                method="POST"
                action="profile.php"
                class="customer-profile-form"
            >


                <!-- ACCOUNT INFORMATION -->

                <div class="profile-section-title">

                    <span>
                        Account Information
                    </span>

                </div>


                <!-- USER ID -->

                <div class="profile-form-group">

                    <label>
                        User ID
                    </label>


                    <input
                        type="text"
                        value="#<?php
                            echo htmlspecialchars(
                                $user["id"]
                            );
                        ?>"
                        readonly
                    >

                </div>


                <!-- USERNAME -->

                <div class="profile-form-group">

                    <label for="username">
                        Username
                    </label>


                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?php

                            echo htmlspecialchars(
                                $user["username"]
                            );

                        ?>"
                        minlength="3"
                        required
                        autocomplete="username"
                    >

                </div>


                <!-- ACCOUNT TYPE -->

                <div class="profile-form-group">

                    <label>
                        Account Type
                    </label>


                    <input
                        type="text"
                        value="Customer"
                        readonly
                    >

                </div>


                <!-- =================================================
                     CHANGE PASSWORD
                     ================================================= -->

                <div
                    class="profile-section-title password-title"
                >

                    <span>
                        Change Password
                    </span>


                    <small>
                        Leave blank if you don't want to change it.
                    </small>

                </div>


                <!-- NEW PASSWORD -->

                <div class="profile-form-group">

                    <label for="new_password">
                        New Password
                    </label>


                    <div class="profile-password-box">


                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            placeholder="Enter new password"
                            minlength="6"
                            autocomplete="new-password"
                        >


                        <button
                            type="button"
                            onclick="toggleProfilePassword()"
                            class="profile-password-toggle"
                        >
                            Show
                        </button>


                    </div>

                </div>


                <!-- =================================================
                     ACTION BUTTONS
                     ================================================= -->

                <div class="profile-actions">


                    <button
                        type="submit"
                        class="profile-save-btn"
                    >
                        Save Changes
                    </button>


                    <a
                        href="dashboard.php"
                        class="profile-cancel-btn"
                    >
                        Cancel
                    </a>


                </div>


            </form>


        </div>


    </div>


</main>


<!-- =========================================================
     PROFILE JAVASCRIPT
     ========================================================= -->

<script>

function toggleProfilePassword()
{
    const password =
        document.getElementById("new_password");

    const button =
        document.querySelector(
            ".profile-password-toggle"
        );


    if (!password || !button) {
        return;
    }


    if (password.type === "password") {

        password.type = "text";

        button.textContent = "Hide";

    } else {

        password.type = "password";

        button.textContent = "Show";

    }
}

</script>


<?php

include "../includes/footer.php";

?>