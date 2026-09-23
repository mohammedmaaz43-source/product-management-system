<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

checkLogin();


$pageTitle = "Reports";
$rootPath = "../";


/* =========================================
   REPORT SUMMARY
========================================= */

$totalProducts = 0;
$totalQuantity = 0;
$totalValue = 0;
$lowStockCount = 0;


$result = $conn->query("

    SELECT

        COUNT(*) AS total_products,

        SUM(quantity) AS total_quantity,

        SUM(price * quantity) AS total_value

    FROM products

");


if ($result) {

    $report =
        $result->fetch_assoc();


    $totalProducts =
        $report["total_products"] ?? 0;


    $totalQuantity =
        $report["total_quantity"] ?? 0;


    $totalValue =
        $report["total_value"] ?? 0;

}


/* =========================================
   LOW STOCK PRODUCTS
========================================= */

$result = $conn->query("

    SELECT COUNT(*) AS total

    FROM products

    WHERE quantity <= 5

");


if ($result) {

    $data =
        $result->fetch_assoc();


    $lowStockCount =
        $data["total"] ?? 0;

}


/* =========================================
   PRODUCTS BY CATEGORY
========================================= */

$categoryReport = $conn->query("

    SELECT

        categories.category_name,

        COUNT(products.id)
        AS total_products

    FROM categories


    LEFT JOIN products

    ON categories.id =
        products.category_id


    GROUP BY

        categories.id,

        categories.category_name


    ORDER BY
        total_products DESC

");


/* =========================================
   INVENTORY VALUE BY CATEGORY
========================================= */

$categoryValueReport = $conn->query("

    SELECT

        categories.category_name,


        COALESCE(

            SUM(
                products.price *
                products.quantity
            ),

            0

        ) AS total_value


    FROM categories


    LEFT JOIN products

    ON categories.id =
        products.category_id


    GROUP BY

        categories.id,

        categories.category_name


    ORDER BY
        total_value DESC

");


/* =========================================
   GET CHART DATA
========================================= */

$chartLabels = [];

$chartProductValues = [];

$chartValueLabels = [];

$chartValues = [];


/* PRODUCTS BY CATEGORY DATA */

if ($categoryReport) {

    while (

        $category =
        $categoryReport->fetch_assoc()

    ) {

        $chartLabels[] =
            $category["category_name"];


        $chartProductValues[] =
            (int)
            $category["total_products"];

    }

}


/* INVENTORY VALUE DATA */

if ($categoryValueReport) {

    while (

        $categoryValue =
        $categoryValueReport->fetch_assoc()

    ) {

        $chartValueLabels[] =
            $categoryValue["category_name"];


        $chartValues[] =
            (float)
            $categoryValue["total_value"];

    }

}


/* =========================================
   RELOAD CATEGORY REPORT FOR TABLE
========================================= */

$categoryReport = $conn->query("

    SELECT

        categories.category_name,

        COUNT(products.id)
        AS total_products

    FROM categories


    LEFT JOIN products

    ON categories.id =
        products.category_id


    GROUP BY

        categories.id,

        categories.category_name


    ORDER BY
        total_products DESC

");


include "../includes/header.php";

?>


<style>

/* =========================================
   REPORT SPARKLINE DESIGN
========================================= */

.reports-sparkline-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 25px;

    margin-top: 30px;

    margin-bottom: 30px;

}


.sparkline-report-card {

    padding: 25px;

    border-radius: 18px;

    background:
        var(--card-bg, #111827);

    border:
        1px solid
        rgba(148, 163, 184, 0.15);

}


.sparkline-report-header {

    margin-bottom: 20px;

}


.sparkline-report-header h2 {

    margin: 0 0 8px;

    font-size: 21px;

}


.sparkline-report-header p {

    margin: 0;

    color:
        #94a3b8;

    font-size: 14px;

}


.sparkline-box {

    position: relative;

    height: 180px;

    width: 100%;

}


/* LIGHT THEME */

body.light-theme
.sparkline-report-card {

    background:
        #ffffff;

    border-color:
        #e2e8f0;

}


body.light-theme
.sparkline-report-header p {

    color:
        #64748b;

}


/* MOBILE */

@media (max-width: 900px) {

    .reports-sparkline-grid {

        grid-template-columns:
            1fr;

    }

}


@media (max-width: 600px) {

    .sparkline-report-card {

        padding: 20px;

    }


    .sparkline-box {

        height: 150px;

    }

}

</style>



<div class="admin-layout">


    <?php
    include "../includes/sidebar.php";
    ?>


    <main class="admin-content">


        <!-- =========================================
             PAGE HEADER
        ========================================= -->

        <div class="page-header">

            <div>

                <p class="page-label">

                    INVENTORY MANAGEMENT

                </p>


                <h1>

                    Reports

                </h1>


                <p>

                    View your product inventory
                    reports and analytics.

                </p>

            </div>

        </div>



        <!-- =========================================
             REPORT CARDS
        ========================================= -->

        <div class="dashboard-cards">


            <div class="dashboard-card">

                <h3>

                    Total Products

                </h3>


                <h2>

                    <?php
                    echo $totalProducts;
                    ?>

                </h2>

            </div>



            <div class="dashboard-card">

                <h3>

                    Total Quantity

                </h3>


                <h2>

                    <?php
                    echo $totalQuantity;
                    ?>

                </h2>

            </div>



            <div class="dashboard-card">

                <h3>

                    Stock Value

                </h3>


                <h2>

                    ₹<?php

                    echo number_format(
                        $totalValue,
                        2
                    );

                    ?>

                </h2>

            </div>



            <div class="dashboard-card">

                <h3>

                    Low Stock Products

                </h3>


                <h2>

                    <?php
                    echo $lowStockCount;
                    ?>

                </h2>

            </div>


        </div>



        <!-- =========================================
             SPARKLINE GRAPHS
        ========================================= -->

        <div class="reports-sparkline-grid">


            <!-- PRODUCTS BY CATEGORY -->

            <div class="sparkline-report-card">


                <div
                    class="sparkline-report-header"
                >

                    <h2>

                        Products by Category

                    </h2>


                    <p>

                        Number of products available
                        in each category.

                    </p>

                </div>


                <div class="sparkline-box">

                    <canvas
                        id="categoryProductsChart"
                    ></canvas>

                </div>


            </div>



            <!-- INVENTORY VALUE -->

            <div class="sparkline-report-card">


                <div
                    class="sparkline-report-header"
                >

                    <h2>

                        Inventory Value by Category

                    </h2>


                    <p>

                        Total inventory value based
                        on product price and quantity.

                    </p>

                </div>


                <div class="sparkline-box">

                    <canvas
                        id="categoryValueChart"
                    ></canvas>

                </div>


            </div>


        </div>



        <!-- =========================================
             CATEGORY TABLE
        ========================================= -->

        <div class="table-container">


            <h2>

                Products by Category

            </h2>


            <br>


            <table>


                <tr>

                    <th>

                        Category

                    </th>


                    <th>

                        Total Products

                    </th>

                </tr>



                <?php

                if (

                    $categoryReport &&

                    $categoryReport->num_rows > 0

                ) {

                ?>


                    <?php

                    while (

                        $category =
                        $categoryReport->fetch_assoc()

                    ) {

                    ?>


                        <tr>


                            <td>

                                <?php

                                echo htmlspecialchars(

                                    $category[
                                        "category_name"
                                    ]

                                );

                                ?>

                            </td>



                            <td>

                                <?php

                                echo $category[
                                    "total_products"
                                ];

                                ?>

                            </td>


                        </tr>


                    <?php } ?>


                <?php } else { ?>


                    <tr>

                        <td colspan="2">

                            No report data available.

                        </td>

                    </tr>


                <?php } ?>


            </table>


        </div>


    </main>


</div>



<!-- =========================================
     CHART.JS
========================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/chart.js"
></script>


<script>


/* =========================================
   PRODUCTS BY CATEGORY SPARKLINE
========================================= */

const categoryNames =

    <?php
    echo json_encode(
        $chartLabels
    );
    ?>;


const categoryProductValues =

    <?php
    echo json_encode(
        $chartProductValues
    );
    ?>;


const categoryProductsChart =

    document.getElementById(
        "categoryProductsChart"
    );


new Chart(

    categoryProductsChart,

    {

        type:
            "line",


        data: {

            labels:
                categoryNames,


            datasets: [

                {

                    data:
                        categoryProductValues,


                    borderColor:
                        "#3b82f6",


                    backgroundColor:
                        "rgba(59, 130, 246, 0.15)",


                    borderWidth:
                        3,


                    tension:
                        0.45,


                    fill:
                        true,


                    pointRadius:
                        4,


                    pointHoverRadius:
                        7,


                    pointBackgroundColor:
                        "#3b82f6"

                }

            ]

        },


        options: {

            responsive:
                true,


            maintainAspectRatio:
                false,


            plugins: {

                legend: {

                    display:
                        false

                },


                tooltip: {

                    enabled:
                        true,


                    callbacks: {

                        title:
                        function(context) {

                            return context[0].label;

                        },


                        label:
                        function(context) {

                            return (
                                "Products: " +
                                context.raw
                            );

                        }

                    }

                }

            },


            scales: {

                x: {

                    display:
                        false,


                    grid: {

                        display:
                            false

                    }

                },


                y: {

                    display:
                        false,


                    beginAtZero:
                        true,


                    grid: {

                        display:
                            false

                    }

                }

            }

        }

    }

);



/* =========================================
   INVENTORY VALUE SPARKLINE
========================================= */

const categoryValueNames =

    <?php
    echo json_encode(
        $chartValueLabels
    );
    ?>;


const categoryValues =

    <?php
    echo json_encode(
        $chartValues
    );
    ?>;


const categoryValueChart =

    document.getElementById(
        "categoryValueChart"
    );


new Chart(

    categoryValueChart,

    {

        type:
            "line",


        data: {

            labels:
                categoryValueNames,


            datasets: [

                {

                    data:
                        categoryValues,


                    borderColor:
                        "#8b5cf6",


                    backgroundColor:
                        "rgba(139, 92, 246, 0.15)",


                    borderWidth:
                        3,


                    tension:
                        0.45,


                    fill:
                        true,


                    pointRadius:
                        4,


                    pointHoverRadius:
                        7,


                    pointBackgroundColor:
                        "#8b5cf6"

                }

            ]

        },


        options: {

            responsive:
                true,


            maintainAspectRatio:
                false,


            plugins: {

                legend: {

                    display:
                        false

                },


                tooltip: {

                    enabled:
                        true,


                    callbacks: {

                        title:
                        function(context) {

                            return context[0].label;

                        },


                        label:
                        function(context) {

                            return (

                                "₹" +

                                Number(
                                    context.raw
                                ).toLocaleString(
                                    "en-IN"
                                )

                            );

                        }

                    }

                }

            },


            scales: {

                x: {

                    display:
                        false,


                    grid: {

                        display:
                            false

                    }

                },


                y: {

                    display:
                        false,


                    beginAtZero:
                        true,


                    grid: {

                        display:
                            false

                    }

                }

            }

        }

    }

);


</script>



<?php

include "../includes/footer.php";

?>