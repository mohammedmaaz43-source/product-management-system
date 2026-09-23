<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();


if (!isset($_GET["id"])) {

    header("Location: suppliers.php");
    exit();
}


$id = (int) $_GET["id"];


/* Get Supplier Details */

$stmt = $conn->prepare("
    SELECT *
    FROM suppliers
    WHERE id = ?
");

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows == 0) {

    header("Location: suppliers.php");
    exit();
}


$supplier = $result->fetch_assoc();

$error = "";


/* Update Supplier */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $supplierName = clean($_POST["supplier_name"]);

    $contactNumber = clean($_POST["contact_number"]);

    $email = clean($_POST["email"]);

    $address = clean($_POST["address"]);


    if (empty($supplierName)) {

        $error = "Supplier name is required.";

    } else {

        $stmt = $conn->prepare("
            UPDATE suppliers
            SET
                supplier_name = ?,
                contact_number = ?,
                email = ?,
                address = ?
            WHERE id = ?
        ");


        $stmt->bind_param(
            "ssssi",
            $supplierName,
            $contactNumber,
            $email,
            $address,
            $id
        );


        if ($stmt->execute()) {

            header("Location: suppliers.php?updated=1");
            exit();

        } else {

            $error = "Supplier could not be updated.";
        }
    }
}


$pageTitle = "Edit Supplier";
$rootPath = "../";

include "../includes/header.php";

?>


<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>


    <main class="admin-content">


        <!-- Page Header -->

        <div class="page-header">

            <div>

                <h1>Edit Supplier</h1>

                <p>
                    Update supplier information.
                </p>

            </div>


            <a
                href="suppliers.php"
                class="btn-reset"
            >
                Back
            </a>

        </div>



        <!-- Edit Form -->

        <div class="form-container supplier-edit-card">


            <div class="form-card-header">

                <div class="form-card-icon">
                    🏢
                </div>


                <div>

                    <h2>
                        Update Supplier
                    </h2>

                    <p>
                        Edit supplier information and save changes.
                    </p>

                </div>

            </div>



            <?php if (!empty($error)) { ?>

                <div class="error-message">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php } ?>



            <form method="POST">


                <!-- Supplier Name -->

                <div class="form-group">

                    <label>
                        Supplier Name
                    </label>

                    <input
                        type="text"
                        name="supplier_name"
                        value="<?php
                        echo htmlspecialchars(
                            $supplier["supplier_name"]
                        );
                        ?>"
                        required
                    >

                </div>



                <!-- Contact and Email -->

                <div class="form-row">


                    <div class="form-group">

                        <label>
                            Contact Number
                        </label>

                        <input
                            type="text"
                            name="contact_number"
                            value="<?php
                            echo htmlspecialchars(
                                $supplier["contact_number"]
                            );
                            ?>"
                        >

                    </div>



                    <div class="form-group">

                        <label>
                            Email Address
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="<?php
                            echo htmlspecialchars(
                                $supplier["email"]
                            );
                            ?>"
                        >

                    </div>


                </div>



                <!-- Address -->

                <div class="form-group">

                    <label>
                        Address
                    </label>

                    <textarea
                        name="address"
                    ><?php
                    echo htmlspecialchars(
                        $supplier["address"]
                    );
                    ?></textarea>

                </div>



                <!-- Buttons -->

                <div class="form-actions">

                    <a
                        href="suppliers.php"
                        class="btn-reset"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="btn"
                    >
                        Update Supplier
                    </button>

                </div>


            </form>


        </div>


    </main>

</div>


<?php include "../includes/footer.php"; ?>