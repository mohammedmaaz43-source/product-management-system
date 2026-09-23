<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();


if (!isset($_GET["id"])) {

    header("Location: categories.php");
    exit();
}


$id = (int) $_GET["id"];


/* Get Category */

$stmt = $conn->prepare("
    SELECT *
    FROM categories
    WHERE id = ?
");

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows == 0) {

    header("Location: categories.php");
    exit();
}


$category = $result->fetch_assoc();


$error = "";


/* Update Category */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $categoryName = clean($_POST["category_name"]);


    if (empty($categoryName)) {

        $error = "Category name is required.";

    } else {

        $stmt = $conn->prepare("
            UPDATE categories
            SET category_name = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "si",
            $categoryName,
            $id
        );


        if ($stmt->execute()) {

            header("Location: categories.php?updated=1");
            exit();

        } else {

            $error = "Category could not be updated.";
        }
    }
}


$pageTitle = "Edit Category";
$rootPath = "../";

include "../includes/header.php";

?>


<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>


    <main class="admin-content">


        <div class="page-header">

            <div>

                <h1>Edit Category</h1>

                <p>
                    Update your category information.
                </p>

            </div>


            <a
                href="categories.php"
                class="btn-reset"
            >
                Back
            </a>

        </div>



        <div class="form-container category-edit-card">


            <div class="form-card-header">

                <div class="form-card-icon">
                    ✏️
                </div>

                <div>

                    <h2>
                        Update Category
                    </h2>

                    <p>
                        Edit the category name and save changes.
                    </p>

                </div>

            </div>



            <?php if (!empty($error)) { ?>

                <div class="error-message">

                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php } ?>



            <form method="POST">


                <div class="form-group">

                    <label>
                        Category Name
                    </label>

                    <input
                        type="text"
                        name="category_name"
                        value="<?php
                        echo htmlspecialchars(
                            $category["category_name"]
                        );
                        ?>"
                        required
                    >

                </div>



                <div class="form-actions">

                    <a
                        href="categories.php"
                        class="btn-reset"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="btn"
                    >
                        Update Category
                    </button>

                </div>


            </form>


        </div>


    </main>

</div>


<?php include "../includes/footer.php"; ?>