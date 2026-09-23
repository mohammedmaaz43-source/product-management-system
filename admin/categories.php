<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "Categories";
$rootPath = "../";

$message = "";
$error = "";

$categoryAdded = isset($_GET["category_added"]);


/* =========================================
   ADD CATEGORY
========================================= */

if (isset($_POST["add_category"])) {

    $categoryName = clean($_POST["category_name"]);

    if (empty($categoryName)) {

        $error = "Category name is required.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO categories (category_name)
             VALUES (?)"
        );

        $stmt->bind_param(
            "s",
            $categoryName
        );

        if ($stmt->execute()) {

            header("Location: categories.php?category_added=1");
            exit();

        } else {

            $error = "Category could not be added.";
        }

        $stmt->close();
    }
}


/* =========================================
   DELETE CATEGORY
========================================= */

if (isset($_GET["delete"])) {

    $id = (int) $_GET["delete"];

    $conn->query(
        "DELETE FROM categories
         WHERE id = $id"
    );

    header("Location: categories.php");
    exit();
}


/* =========================================
   GET CATEGORIES WITH PRODUCT COUNT
========================================= */

$categories = $conn->query("
    SELECT
        categories.id,
        categories.category_name,
        COUNT(products.id) AS total_products

    FROM categories

    LEFT JOIN products
    ON categories.id = products.category_id

    GROUP BY
        categories.id,
        categories.category_name

    ORDER BY categories.id DESC
");


include "../includes/header.php";

?>


<?php if ($categoryAdded) { ?>

<div class="action-success-overlay" id="categorySuccessOverlay">

    <div class="action-success-card">

        <div
            class="action-step"
            id="categoryStepOne"
        >

            <div class="action-icon category-icon-animation">

                <div class="folder-icon">
                    <span></span>
                </div>

            </div>

            <h2>
                Adding Category...
            </h2>

            <p>
                Creating your new category
            </p>

        </div>


        <div
            class="action-step"
            id="categoryStepTwo"
            style="display:none;"
        >

            <div class="action-progress-ring">

                <div class="action-progress-inner">

                    <span id="categoryProgress">
                        0%
                    </span>

                </div>

            </div>

            <h2>
                Saving Category...
            </h2>

            <p>
                Updating your inventory system
            </p>

        </div>


        <div
            class="action-step"
            id="categoryStepThree"
            style="display:none;"
        >

            <div class="action-success-check">
                ✓
            </div>

            <h2>
                Category Added Successfully
            </h2>

            <p>
                Your category has been added to the system.
            </p>

        </div>

    </div>

</div>

<?php } ?>


<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>


    <main class="admin-content">


        <div class="page-header">

            <div>

                <p class="page-label">
                    INVENTORY MANAGEMENT
                </p>

                <h1>
                    Categories
                </h1>

                <p>
                    Organize and manage your product categories.
                </p>

            </div>

        </div>


        <div class="category-layout">


            <div class="form-container category-form-card">


                <div class="form-section-header">

                    <h2>
                        Add New Category
                    </h2>

                    <p>
                        Create a category to organize your products.
                    </p>

                </div>


                <?php if (!empty($message)) { ?>

                    <div class="success-message">

                        <?php
                        echo htmlspecialchars($message);
                        ?>

                    </div>

                <?php } ?>


                <?php if (!empty($error)) { ?>

                    <div class="error-message">

                        <?php
                        echo htmlspecialchars($error);
                        ?>

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
                            placeholder="Enter category name"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        name="add_category"
                        class="btn"
                    >
                        + Add Category
                    </button>


                </form>


            </div>


            <div class="category-summary-card">

                <span class="summary-label">
                    TOTAL CATEGORIES
                </span>

                <h2>

                    <?php
                    echo $categories
                        ? $categories->num_rows
                        : 0;
                    ?>

                </h2>

                <p>
                    Categories currently available
                    in your inventory system.
                </p>

            </div>


        </div>


        <div class="dashboard-section">


            <div class="section-heading">

                <div>

                    <h2>
                        Manage Categories
                    </h2>

                    <p>
                        View all available product categories.
                    </p>

                </div>

            </div>


            <div class="table-container">


                <table>


                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Category Name
                        </th>

                        <th>
                            Products
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>


                    <?php

                    if (
                        $categories &&
                        $categories->num_rows > 0
                    ) {

                    ?>


                        <?php while (
                            $category =
                            $categories->fetch_assoc()
                        ) { ?>


                            <tr>


                                <td>

                                    #<?php
                                    echo $category["id"];
                                    ?>

                                </td>


                                <td>

                                    <div class="category-name-cell">

                                        <span class="category-icon">
                                            C
                                        </span>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $category["category_name"]
                                            );
                                            ?>

                                        </strong>

                                    </div>

                                </td>


                                <td>

                                    <span class="product-count">

                                        <?php
                                        echo $category[
                                            "total_products"
                                        ];
                                        ?>

                                        Products

                                    </span>

                                </td>


                                <td>

                                    <a
                                        href="categories.php?delete=<?php
                                        echo $category["id"];
                                        ?>"
                                        class="btn-delete"
                                        onclick="return confirm('Are you sure you want to delete this category?');"
                                    >
                                        Delete
                                    </a>

                                </td>


                            </tr>


                        <?php } ?>


                    <?php } else { ?>


                        <tr>

                            <td colspan="4">

                                No categories available.

                            </td>

                        </tr>


                    <?php } ?>


                </table>


            </div>


        </div>


    </main>

</div>


<?php include "../includes/footer.php"; ?>
