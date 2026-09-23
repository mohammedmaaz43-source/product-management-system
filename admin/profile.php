<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

checkLogin();

$pageTitle = "My Profile";
$rootPath = "../";

$userId = $_SESSION["user_id"];

$result = $conn->query(
    "SELECT id, username
     FROM users
     WHERE id = $userId"
);

$user = $result->fetch_assoc();

$message = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username =
        trim($_POST["username"]);


    if (empty($username)) {

        $error =
            "Username cannot be empty.";

    } else {

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

        if ($stmt->execute()) {

            $_SESSION["username"] =
                $username;

            $user["username"] =
                $username;

            $message =
                "Profile updated successfully.";

        } else {

            $error =
                "Profile could not be updated.";
        }

    }

}


include "../includes/header.php";

?>

<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="admin-content">

        <div class="page-header">

            <h1>My Profile</h1>

        </div>


        <div class="form-container">


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

                    <label>User ID</label>

                    <input
                        type="text"
                        value="<?php echo $user["id"]; ?>"
                        disabled
                    >

                </div>


                <div class="form-group">

                    <label>Username</label>

                    <input
                        type="text"
                        name="username"
                        value="<?php echo htmlspecialchars($user["username"]); ?>"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn"
                >
                    Update Profile
                </button>

            </form>


            <br>


            <a
                href="../reset_password.php"
                class="btn"
            >
                Change Password
            </a>

        </div>

    </main>

</div>

<?php include "../includes/footer.php"; ?>