<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "My Cart";
$rootPath = "../";


// =========================================
// CHECK CUSTOMER
// =========================================

$userId = (int) $_SESSION["user_id"];

$userQuery = $conn->prepare("
    SELECT id, username, role
    FROM users
    WHERE id = ?
");

$userQuery->bind_param("i", $userId);
$userQuery->execute();

$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();

if (!$user || $user["role"] !== "user") {

    header("Location: ../login.php");
    exit();
}


// =========================================
// MESSAGE
// =========================================

$message = "";
$error = "";


// =========================================
// ADD PRODUCT TO CART
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["add_to_cart"])
) {

    $productId = (int) ($_POST["product_id"] ?? 0);
    $quantity = (int) ($_POST["quantity"] ?? 1);

    if ($quantity < 1) {
        $quantity = 1;
    }


    // Check product

    $productQuery = $conn->prepare("
        SELECT id, quantity
        FROM products
        WHERE id = ?
    ");

    $productQuery->bind_param(
        "i",
        $productId
    );

    $productQuery->execute();

    $productResult =
        $productQuery->get_result();

    $product =
        $productResult->fetch_assoc();


    if (!$product) {

        $error = "Product not found.";

    } elseif ($product["quantity"] <= 0) {

        $error = "This product is out of stock.";

    } else {


        // Check existing cart item

        $cartQuery = $conn->prepare("
            SELECT id, quantity
            FROM cart
            WHERE user_id = ?
            AND product_id = ?
        ");

        $cartQuery->bind_param(
            "ii",
            $userId,
            $productId
        );

        $cartQuery->execute();

        $cartResult =
            $cartQuery->get_result();

        $existingCart =
            $cartResult->fetch_assoc();


        if ($existingCart) {

            $newQuantity =
                $existingCart["quantity"]
                + $quantity;


            if (
                $newQuantity >
                $product["quantity"]
            ) {

                $newQuantity =
                    $product["quantity"];
            }


            $updateCart =
                $conn->prepare("
                    UPDATE cart
                    SET quantity = ?
                    WHERE id = ?
                ");

            $updateCart->bind_param(
                "ii",
                $newQuantity,
                $existingCart["id"]
            );

            $updateCart->execute();

            $message =
                "Product quantity updated in cart.";

        } else {

            if (
                $quantity >
                $product["quantity"]
            ) {

                $quantity =
                    $product["quantity"];
            }


            $insertCart =
                $conn->prepare("
                    INSERT INTO cart
                    (
                        user_id,
                        product_id,
                        quantity
                    )
                    VALUES (?, ?, ?)
                ");

            $insertCart->bind_param(
                "iii",
                $userId,
                $productId,
                $quantity
            );

            if ($insertCart->execute()) {

                $message =
                    "Product added to cart.";

            } else {

                $error =
                    "Product could not be added to cart.";
            }

        }

    }
}


// =========================================
// UPDATE CART QUANTITY
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_cart"])
) {

    $cartId =
        (int) ($_POST["cart_id"] ?? 0);

    $quantity =
        (int) ($_POST["quantity"] ?? 1);


    if ($quantity < 1) {

        $quantity = 1;
    }


    $stockQuery = $conn->prepare("
        SELECT
            cart.id,
            products.quantity AS stock
        FROM cart

        INNER JOIN products
            ON cart.product_id = products.id

        WHERE cart.id = ?
        AND cart.user_id = ?
    ");

    $stockQuery->bind_param(
        "ii",
        $cartId,
        $userId
    );

    $stockQuery->execute();

    $stockResult =
        $stockQuery->get_result();

    $cartItem =
        $stockResult->fetch_assoc();


    if (!$cartItem) {

        $error = "Cart item not found.";

    } else {

        if (
            $quantity >
            $cartItem["stock"]
        ) {

            $quantity =
                $cartItem["stock"];
        }


        if ($quantity <= 0) {

            $deleteCart =
                $conn->prepare("
                    DELETE FROM cart
                    WHERE id = ?
                    AND user_id = ?
                ");

            $deleteCart->bind_param(
                "ii",
                $cartId,
                $userId
            );

            $deleteCart->execute();

        } else {

            $updateCart =
                $conn->prepare("
                    UPDATE cart
                    SET quantity = ?
                    WHERE id = ?
                    AND user_id = ?
                ");

            $updateCart->bind_param(
                "iii",
                $quantity,
                $cartId,
                $userId
            );

            $updateCart->execute();
        }


        $message =
            "Cart updated successfully.";
    }
}


// =========================================
// REMOVE PRODUCT FROM CART
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["remove_from_cart"])
) {

    $cartId =
        (int) ($_POST["cart_id"] ?? 0);


    $deleteCart =
        $conn->prepare("
            DELETE FROM cart
            WHERE id = ?
            AND user_id = ?
        ");

    $deleteCart->bind_param(
        "ii",
        $cartId,
        $userId
    );


    if ($deleteCart->execute()) {

        $message =
            "Product removed from cart.";

    } else {

        $error =
            "Product could not be removed.";
    }
}


// =========================================
// GET CART ITEMS
// =========================================

$cartQuery = $conn->prepare("
    SELECT
        cart.id AS cart_id,
        cart.quantity AS cart_quantity,

        products.id AS product_id,
        products.product_name,
        products.price,
        products.quantity AS stock,
        products.image,

        categories.category_name

    FROM cart

    INNER JOIN products
        ON cart.product_id = products.id

    LEFT JOIN categories
        ON products.category_id = categories.id

    WHERE cart.user_id = ?

    ORDER BY cart.id DESC
");

$cartQuery->bind_param(
    "i",
    $userId
);

$cartQuery->execute();

$cartItems =
    $cartQuery->get_result();


// =========================================
// CALCULATE TOTAL
// =========================================

$cartTotal = 0;

$totalItems = 0;

while (
    $item = $cartItems->fetch_assoc()
) {

    $cartTotal +=
        (float) $item["price"]
        *
        (int) $item["cart_quantity"];

    $totalItems +=
        (int) $item["cart_quantity"];
}


// Reset result pointer

$cartItems->data_seek(0);


include "../includes/header.php";

?>



    <!-- =====================================
         PAGE HEADER
    ====================================== -->

    <section class="customer-page-header">

        <div>

            <span class="hero-label">
                SHOPPING CART
            </span>

            <h1>
                My Cart
            </h1>

            <p>
                Review your selected products
                before checkout.
            </p>

        </div>

    </section>



    <!-- =====================================
         MESSAGES
    ====================================== -->

    <?php if (!empty($message)) { ?>

        <div class="customer-success-message">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php } ?>


    <?php if (!empty($error)) { ?>

        <div class="customer-error-message">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php } ?>



    <!-- =====================================
         CART
    ====================================== -->

    <section class="cart-section">


        <?php

        if (
            $cartItems &&
            $cartItems->num_rows > 0
        ) {

        ?>


            <!-- CART ITEMS -->

            <div class="cart-items-container">


                <div class="cart-heading">

                    <div>

                        <span>
                            YOUR ITEMS
                        </span>

                        <h2>
                            <?php
                            echo $totalItems;
                            ?>
                            Items
                        </h2>

                    </div>

                </div>



                <?php

                while (
                    $item =
                    $cartItems->fetch_assoc()
                ) {

                    $itemTotal =
                        (float)
                        $item["price"]
                        *
                        (int)
                        $item["cart_quantity"];

                ?>


                    <div class="cart-item">


                        <!-- IMAGE -->

                        <div class="cart-item-image">

                            <?php

                            if (
                                !empty(
                                    $item["image"]
                                )
                            ) {

                            ?>

                                <img
                                    src="../uploads/<?php
                                    echo htmlspecialchars(
                                        $item["image"]
                                    );
                                    ?>"
                                    alt="<?php
                                    echo htmlspecialchars(
                                        $item["product_name"]
                                    );
                                    ?>"
                                >

                            <?php

                            } else {

                            ?>

                                <div class="no-product-image">
                                    🛍️
                                </div>

                            <?php

                            }

                            ?>

                        </div>



                        <!-- INFO -->

                        <div class="cart-item-info">

                            <span class="product-category">

                                <?php

                                echo htmlspecialchars(
                                    $item["category_name"]
                                    ??
                                    "Uncategorized"
                                );

                                ?>

                            </span>


                            <h3>

                                <?php

                                echo htmlspecialchars(
                                    $item["product_name"]
                                );

                                ?>

                            </h3>


                            <p>

                                ₹<?php

                                echo number_format(
                                    (float)
                                    $item["price"],
                                    2
                                );

                                ?>

                                each

                            </p>

                        </div>



                        <!-- QUANTITY -->

                        <form
                            method="POST"
                            class="cart-quantity-form"
                        >

                            <input
                                type="hidden"
                                name="cart_id"
                                value="<?php
                                echo $item["cart_id"];
                                ?>"
                            >

                            <label>
                                Quantity
                            </label>

                            <input
                                type="number"
                                name="quantity"
                                min="1"
                                max="<?php
                                echo $item["stock"];
                                ?>"
                                value="<?php
                                echo $item["cart_quantity"];
                                ?>"
                            >

                            <button
                                type="submit"
                                name="update_cart"
                                class="cart-update-btn"
                            >
                                Update
                            </button>

                        </form>



                        <!-- TOTAL -->

                        <div class="cart-item-total">

                            <strong>

                                ₹<?php

                                echo number_format(
                                    $itemTotal,
                                    2
                                );

                                ?>

                            </strong>


                            <form
                                method="POST"
                            >

                                <input
                                    type="hidden"
                                    name="cart_id"
                                    value="<?php
                                    echo $item["cart_id"];
                                    ?>"
                                >

                                <button
                                    type="submit"
                                    name="remove_from_cart"
                                    class="cart-remove-btn"
                                    onclick="return confirm('Remove this product from your cart?');"
                                >
                                    Remove
                                </button>

                            </form>

                        </div>


                    </div>


                <?php

                }

                ?>


            </div>



            <!-- =================================
                 CART SUMMARY
            ================================== -->

            <aside class="cart-summary">

                <span>
                    ORDER SUMMARY
                </span>

                <h2>
                    Cart Summary
                </h2>


                <div class="summary-row">

                    <span>
                        Items
                    </span>

                    <strong>
                        <?php
                        echo $totalItems;
                        ?>
                    </strong>

                </div>


                <div class="summary-row">

                    <span>
                        Subtotal
                    </span>

                    <strong>

                        ₹<?php

                        echo number_format(
                            $cartTotal,
                            2
                        );

                        ?>

                    </strong>

                </div>


                <div class="summary-divider"></div>


                <div class="summary-total">

                    <span>
                        Total
                    </span>

                    <strong>

                        ₹<?php

                        echo number_format(
                            $cartTotal,
                            2
                        );

                        ?>

                    </strong>

                </div>


                <a
                    href="checkout.php"
                    class="customer-primary-btn checkout-btn"
                >
                    Proceed to Checkout
                </a>


                <a
                    href="products.php"
                    class="continue-shopping-btn"
                >
                    ← Continue Shopping
                </a>

            </aside>


        <?php

        } else {

        ?>


            <!-- =================================
                 EMPTY CART
            ================================== -->

            <div class="empty-cart">

                <div class="empty-cart-icon">
                    🛒
                </div>

                <h2>
                    Your Cart is Empty
                </h2>

                <p>
                    You haven't added any products
                    to your cart yet.
                </p>

                <a
                    href="products.php"
                    class="customer-primary-btn"
                >
                    Start Shopping
                </a>

            </div>


        <?php

        }

        ?>


    </section>


</div>


<?php

include "../includes/footer.php";

?>