<?php

session_start();

require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $newPassword =
        $_POST["new_password"];

    $confirmPassword =
        $_POST["confirm_password"];

    if (
        empty($newPassword) ||
        empty($confirmPassword)
    ) {

        $error =
            "Please fill all fields.";

    } elseif (
        $newPassword != $confirmPassword
    ) {

        $error =
            "Passwords do not match.";

    } elseif (
        strlen($newPassword) < 6
    ) {

        $error =
            "Password must contain at least 6 characters.";

    } else {

        $hashedPassword =
            password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );

        $userId =
            $_SESSION["user_id"];

        $stmt = $conn->prepare(
            "UPDATE users
             SET password = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $hashedPassword,
            $userId
        );

        if ($stmt->execute()) {

            $message =
                "Password updated successfully.";

        } else {

            $error =
                "Password update failed.";
        }

    }

}

$pageTitle = "Reset Password";
$rootPath = "";

include "includes/header.php";

?>

<div class="auth-page">

    <div class="auth-box">

        <h1>Reset Password</h1>


        <?php if (!empty($message)) { ?>

            <div class="success-message">

                <?php echo $message; ?>

            </div>

        <?php } ?>


        <?php if (!empty($error)) { ?>

            <div class="error-message">

                <?php echo $error; ?>

            </div>

        <?php } ?>


        <form method="POST">

            <div class="form-group">

                <label>
                    New Password
                </label>

                <input
                    type="password"
                    name="new_password"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Confirm Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn"
            >
                Update Password
            </button>

        </form>

    </div>

</div>

<?php include "includes/footer.php"; ?>