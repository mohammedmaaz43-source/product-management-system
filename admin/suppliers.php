<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$pageTitle = "Suppliers";
$rootPath = "../";

$message = "";
$error = "";

$supplierAdded = isset($_GET["supplier_added"]);


if (isset($_GET["updated"])) {

    $message = "Supplier updated successfully.";
}


/* =========================================
   ADD SUPPLIER
========================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $supplierName =
        clean($_POST["supplier_name"]);

    $contactNumber =
        clean($_POST["contact_number"]);

    $email =
        clean($_POST["email"]);

    $address =
        clean($_POST["address"]);


    if (empty($supplierName)) {

        $error =
            "Supplier name is required.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO suppliers
            (
                supplier_name,
                contact_number,
                email,
                address
            )
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssss",
            $supplierName,
            $contactNumber,
            $email,
            $address
        );

        if ($stmt->execute()) {

            header("Location: suppliers.php?supplier_added=1");
            exit();

        } else {

            $error =
                "Supplier could not be added.";
        }

        $stmt->close();
    }
}


/* =========================================
   DELETE SUPPLIER
========================================= */

if (isset($_GET["delete"])) {

    $id = (int) $_GET["delete"];

    $conn->query(
        "DELETE FROM suppliers WHERE id = $id"
    );

    header("Location: suppliers.php");
    exit();
}


$suppliers = $conn->query(
    "SELECT * FROM suppliers
     ORDER BY id DESC"
);


include "../includes/header.php";

?>


<?php if ($supplierAdded) { ?>

<div class="action-success-overlay" id="supplierSuccessOverlay">

    <div class="action-success-card">


        <div
            class="action-step"
            id="supplierStepOne"
        >

            <div class="action-icon supplier-icon-animation">

                <div class="supplier-building">

                    <div class="building-window"></div>
                    <div class="building-window"></div>
                    <div class="building-window"></div>

                    <div class="building-door"></div>

                </div>

            </div>

            <h2>
                Adding Supplier...
            </h2>

            <p>
                Creating supplier profile
            </p>

        </div>


        <div
            class="action-step"
            id="supplierStepTwo"
            style="display:none;"
        >

            <div class="action-progress-ring supplier-ring">

                <div class="action-progress-inner">

                    <span id="supplierProgress">
                        0%
                    </span>

                </div>

            </div>

            <h2>
                Verifying Supplier...
            </h2>

            <p>
                Saving supplier information
            </p>

        </div>


        <div
            class="action-step"
            id="supplierStepThree"
            style="display:none;"
        >

            <div class="action-success-check">
                ✓
            </div>

            <h2>
                Supplier Added Successfully
            </h2>

            <p>
                Supplier information has been saved successfully.
            </p>

        </div>


    </div>

</div>

<?php } ?>


<div class="admin-layout">

    <?php include "../includes/sidebar.php"; ?>


    <main class="admin-content">


        <div class="page-header">

            <h1>
                Suppliers
            </h1>

        </div>


        <div class="form-container">


            <h2>
                Add Supplier
            </h2>

            <br>


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
                        Supplier Name
                    </label>

                    <input
                        type="text"
                        name="supplier_name"
                        required
                    >

                </div>


                <div class="form-row">


                    <div class="form-group">

                        <label>
                            Contact Number
                        </label>

                        <input
                            type="text"
                            name="contact_number"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                        >

                    </div>


                </div>


                <div class="form-group">

                    <label>
                        Address
                    </label>

                    <textarea
                        name="address"
                    ></textarea>

                </div>


                <button
                    type="submit"
                    class="btn"
                >
                    Add Supplier
                </button>


            </form>


        </div>


        <br>


        <div class="table-container">


            <table>


                <tr>

                    <th>
                        ID
                    </th>

                    <th>
                        Supplier Name
                    </th>

                    <th>
                        Contact Number
                    </th>

                    <th>
                        Email
                    </th>

                    <th>
                        Address
                    </th>

                    <th>
                        Action
                    </th>

                </tr>


                <?php while (
                    $supplier =
                    $suppliers->fetch_assoc()
                ) { ?>


                    <tr>


                        <td>

                            <?php
                            echo $supplier["id"];
                            ?>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $supplier["supplier_name"]
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $supplier["contact_number"]
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $supplier["email"]
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $supplier["address"]
                            );

                            ?>

                        </td>


                        <td class="action-buttons">


                            <a
                                href="edit_supplier.php?id=<?php
                                echo $supplier["id"];
                                ?>"
                                class="btn-edit"
                            >
                                Edit
                            </a>


                            <a
                                href="suppliers.php?delete=<?php
                                echo $supplier["id"];
                                ?>"
                                class="btn-delete"
                                onclick="return confirm('Are you sure you want to delete this supplier?');"
                            >
                                Delete
                            </a>


                        </td>


                    </tr>


                <?php } ?>


            </table>


        </div>


    </main>

</div>


<?php include "../includes/footer.php"; ?>
