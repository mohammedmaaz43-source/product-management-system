<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

checkLogin();

$pageTitle = "Manage Products";
$rootPath = "../";

/* Products per page */
$productsPerPage = 10;

/* Current page */
$page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;

if ($page < 1) {
    $page = 1;
}

/* Filter values */
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";

$categoryId = isset($_GET["category_id"])
    ? (int) $_GET["category_id"]
    : 0;

$supplierId = isset($_GET["supplier_id"])
    ? (int) $_GET["supplier_id"]
    : 0;

$stock = isset($_GET["stock"])
    ? $_GET["stock"]
    : "";

/* Categories */
$categories = $conn->query("
    SELECT *
    FROM categories
    ORDER BY category_name ASC
");

/* Suppliers */
$suppliers = $conn->query("
    SELECT *
    FROM suppliers
    ORDER BY supplier_name ASC
");

/* WHERE condition */
$where = " WHERE 1=1 ";

/* Search */
if (!empty($search)) {

    $safeSearch = $conn->real_escape_string($search);

    $where .= "
        AND products.product_name
        LIKE '%$safeSearch%'
    ";
}

/* Category filter */
if ($categoryId > 0) {

    $where .= "
        AND products.category_id = $categoryId
    ";
}

/* Supplier filter */
if ($supplierId > 0) {

    $where .= "
        AND products.supplier_id = $supplierId
    ";
}

/* Stock filter */
if ($stock == "low") {

    $where .= "
        AND products.quantity BETWEEN 1 AND 5
    ";

} elseif ($stock == "available") {

    $where .= "
        AND products.quantity > 5
    ";

} elseif ($stock == "out") {

    $where .= "
        AND products.quantity = 0
    ";
}

/* Count total filtered products */
$countSql = "
    SELECT COUNT(*) AS total
    FROM products
    $where
";

$countResult = $conn->query($countSql);
$countData = $countResult->fetch_assoc();

$totalProducts = $countData["total"];

/* Total pages */
$totalPages = ceil($totalProducts / $productsPerPage);

if ($totalPages > 0 && $page > $totalPages) {
    $page = $totalPages;
}

/* Starting position */
$start = ($page - 1) * $productsPerPage;

/* Get products */
$sql = "
    SELECT products.*,
           categories.category_name,
           suppliers.supplier_name

    FROM products

    LEFT JOIN categories
    ON products.category_id = categories.id

    LEFT JOIN suppliers
    ON products.supplier_id = suppliers.id

    $where

    ORDER BY products.id DESC

    LIMIT $start, $productsPerPage
";

$products = $conn->query($sql);

include "../includes/header.php";

?>

<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="admin-content">

        <div class="page-header">

            <div>

                <h1>Products</h1>

                <p>
                    Manage all your products from here.
                </p>

            </div>

            <a href="add_product.php" class="btn">
                Add Product
            </a>

        </div>


        <!-- Search and Filters -->

        <div class="filter-container">

            <form method="GET" class="filter-form">

                <div class="filter-group">

                    <label>Search Product</label>

                    <input
                        type="text"
                        name="search"
                        placeholder="Enter product name"
                        value="<?php echo htmlspecialchars($search); ?>"
                    >

                </div>


                <div class="filter-group">

                    <label>Category</label>

                    <select name="category_id">

                        <option value="0">
                            All Categories
                        </option>

                        <?php while ($category = $categories->fetch_assoc()) { ?>

                            <option
                                value="<?php echo $category["id"]; ?>"

                                <?php
                                if ($categoryId == $category["id"]) {
                                    echo "selected";
                                }
                                ?>

                            >

                                <?php
                                echo htmlspecialchars(
                                    $category["category_name"]
                                );
                                ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>


                <div class="filter-group">

                    <label>Supplier</label>

                    <select name="supplier_id">

                        <option value="0">
                            All Suppliers
                        </option>

                        <?php while ($supplier = $suppliers->fetch_assoc()) { ?>

                            <option
                                value="<?php echo $supplier["id"]; ?>"

                                <?php
                                if ($supplierId == $supplier["id"]) {
                                    echo "selected";
                                }
                                ?>

                            >

                                <?php
                                echo htmlspecialchars(
                                    $supplier["supplier_name"]
                                );
                                ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>


                <div class="filter-group">

                    <label>Stock Status</label>

                    <select name="stock">

                        <option value="">
                            All Stock
                        </option>

                        <option
                            value="available"

                            <?php
                            if ($stock == "available") {
                                echo "selected";
                            }
                            ?>

                        >
                            Available
                        </option>

                        <option
                            value="low"

                            <?php
                            if ($stock == "low") {
                                echo "selected";
                            }
                            ?>

                        >
                            Low Stock
                        </option>

                        <option
                            value="out"

                            <?php
                            if ($stock == "out") {
                                echo "selected";
                            }
                            ?>

                        >
                            Out of Stock
                        </option>

                    </select>

                </div>


                <div class="filter-buttons">

                    <button type="submit" class="btn">
                        Search
                    </button>

                    <a
                        href="products.php"
                        class="btn-reset"
                    >
                        Reset
                    </a>

                </div>

            </form>

        </div>


        <!-- Products Table -->

        <div class="table-container">

            <table>

                <tr>

                    <th>Image</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Action</th>

                </tr>


                <?php if ($products && $products->num_rows > 0) { ?>

                    <?php while ($product = $products->fetch_assoc()) { ?>

                        <tr>

                            <td>

                                <?php

                                $image = !empty($product["image"])
                                    ? "../uploads/" . $product["image"]
                                    : "../images/product_image.png";

                                ?>

                                <img
                                    src="<?php echo $image; ?>"
                                    class="table-image"
                                    alt="Product Image"
                                >

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $product["product_name"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $product["category_name"] ?? "N/A"
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $product["supplier_name"] ?? "N/A"
                                );
                                ?>

                            </td>


                            <td>

                                ₹<?php
                                echo number_format(
                                    $product["price"],
                                    2
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo $product["quantity"];
                                ?>

                            </td>


                            <td>

                                <?php

                                if ($product["quantity"] == 0) {

                                    echo '<span class="out-stock">
                                        Out of Stock
                                    </span>';

                                } elseif ($product["quantity"] <= 5) {

                                    echo '<span class="low-stock">
                                        Low Stock
                                    </span>';

                                } else {

                                    echo '<span class="in-stock">
                                        Available
                                    </span>';
                                }

                                ?>

                            </td>


<td class="action-buttons">

    <a
        href="product_details.php?id=<?php echo $product["id"]; ?>"
        class="btn-view"
    >
        View Details
    </a>


    <a
        href="edit_product.php?id=<?php echo $product["id"]; ?>"
        class="btn-edit"
    >
        Edit
    </a>


    <a
        href="delete_product.php?id=<?php echo $product["id"]; ?>"
        class="btn-delete"
        onclick="return confirm('Are you sure you want to delete this product?');"
    >
        Delete
    </a>

</td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>

                        <td colspan="8">
                            No products found.
                        </td>

                    </tr>

                <?php } ?>

            </table>

        </div>


        <!-- Pagination -->

        <?php if ($totalPages > 1) { ?>

            <div class="pagination">


                <!-- Previous -->

                <?php if ($page > 1) { ?>

                    <a
                        href="?search=<?php echo urlencode($search); ?>&category_id=<?php echo $categoryId; ?>&supplier_id=<?php echo $supplierId; ?>&stock=<?php echo urlencode($stock); ?>&page=<?php echo $page - 1; ?>"
                    >
                        Previous
                    </a>

                <?php } ?>


                <!-- Page Numbers -->

                <?php for ($i = 1; $i <= $totalPages; $i++) { ?>

                    <a
                        href="?search=<?php echo urlencode($search); ?>&category_id=<?php echo $categoryId; ?>&supplier_id=<?php echo $supplierId; ?>&stock=<?php echo urlencode($stock); ?>&page=<?php echo $i; ?>"

                        class="<?php
                        if ($page == $i) {
                            echo "active-page";
                        }
                        ?>"
                    >

                        <?php echo $i; ?>

                    </a>

                <?php } ?>


                <!-- Next -->

                <?php if ($page < $totalPages) { ?>

                    <a
                        href="?search=<?php echo urlencode($search); ?>&category_id=<?php echo $categoryId; ?>&supplier_id=<?php echo $supplierId; ?>&stock=<?php echo urlencode($stock); ?>&page=<?php echo $page + 1; ?>"
                    >
                        Next
                    </a>

                <?php } ?>


            </div>

        <?php } ?>


    </main>

</div>

<?php include "../includes/footer.php"; ?>