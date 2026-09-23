<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

checkLogin();

$user_id = $_SESSION["user_id"];


/* =========================
   MONTHLY ORDERS & SPENDING
   ========================= */

$months = [];
$orderData = [];
$spendingData = [];

for ($i = 5; $i >= 0; $i--) {

    $date = strtotime("-" . $i . " months");

    $months[] = date("M Y", $date);

    $month = date("m", $date);
    $year = date("Y", $date);


    /* Monthly Orders */

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM orders
        WHERE user_id = ?
        AND MONTH(created_at) = ?
        AND YEAR(created_at) = ?
    ");

    $stmt->bind_param("iii", $user_id, $month, $year);

    $stmt->execute();

    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    $orderData[] = (int)$row["total"];

    $stmt->close();


    /* Monthly Spending */

    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total_amount), 0) AS total
        FROM orders
        WHERE user_id = ?
        AND MONTH(created_at) = ?
        AND YEAR(created_at) = ?
    ");

    $stmt->bind_param("iii", $user_id, $month, $year);

    $stmt->execute();

    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    $spendingData[] = (float)$row["total"];

    $stmt->close();
}


/* =========================
   ORDER STATUS
   ========================= */

$statusLabels = [];
$statusData = [];

$stmt = $conn->prepare("
    SELECT status, COUNT(*) AS total
    FROM orders
    WHERE user_id = ?
    GROUP BY status
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $statusLabels[] = $row["status"];
    $statusData[] = (int)$row["total"];
}

$stmt->close();


/* =========================
   PAYMENT METHODS
   ========================= */

$paymentLabels = [];
$paymentData = [];

$stmt = $conn->prepare("
    SELECT payment_method, COUNT(*) AS total
    FROM orders
    WHERE user_id = ?
    AND payment_method IS NOT NULL
    AND payment_method != ''
    GROUP BY payment_method
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $paymentLabels[] = $row["payment_method"];
    $paymentData[] = (int)$row["total"];
}

$stmt->close();


/* =========================
   PAGE SETTINGS
   ========================= */

$pageTitle = "Reports";
$rootPath = "../";
$isCustomerPageCustom = true;

include "../includes/header.php";

?>


<div class="reports-page">

    <div class="reports-container">

        <div class="reports-header">

            <h1>Reports & Analytics</h1>

            <p>View your order and spending analytics</p>

        </div>


        <!-- 1. MONTHLY ORDERS LINE GRAPH -->

        <div class="report-card">

            <h2>Monthly Orders</h2>

            <div class="report-chart">

                <canvas id="ordersChart"></canvas>

            </div>

        </div>


        <!-- 2. MONTHLY SPENDING LINE GRAPH -->

        <div class="report-card">

            <h2>Monthly Spending</h2>

            <div class="report-chart">

                <canvas id="spendingChart"></canvas>

            </div>

        </div>


        <!-- 3. ORDERS BAR GRAPH -->

        <div class="report-card">

            <h2>Orders Comparison</h2>

            <div class="report-chart">

                <canvas id="ordersBarChart"></canvas>

            </div>

        </div>


        <!-- 4. ORDER STATUS PIE CHART -->

        <div class="report-card report-pie-card">

            <h2>Order Status</h2>

            <div class="report-pie">

                <canvas id="statusChart"></canvas>

            </div>

        </div>


        <!-- 5. PAYMENT METHOD PIE CHART -->

        <div class="report-card report-pie-card">

            <h2>Payment Methods</h2>

            <div class="report-pie">

                <canvas id="paymentChart"></canvas>

            </div>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

/* PHP DATA */

const months = <?php echo json_encode($months); ?>;

const orderData = <?php echo json_encode($orderData); ?>;

const spendingData = <?php echo json_encode($spendingData); ?>;

const statusLabels = <?php echo json_encode($statusLabels); ?>;

const statusData = <?php echo json_encode($statusData); ?>;

const paymentLabels = <?php echo json_encode($paymentLabels); ?>;

const paymentData = <?php echo json_encode($paymentData); ?>;


/* =========================
   1. MONTHLY ORDERS
   LINE GRAPH
   ========================= */

new Chart(document.getElementById("ordersChart"), {

    type: "line",

    data: {

        labels: months,

        datasets: [{

            label: "Orders",

            data: orderData,

            borderColor: "#6366f1",

            backgroundColor: "rgba(99, 102, 241, 0.15)",

            borderWidth: 3,

            fill: true,

            tension: 0.4,

            pointRadius: 5,

            pointHoverRadius: 7
        }]

    },

    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {
                display: true
            }

        },

        scales: {

            y: {

                beginAtZero: true,

                ticks: {
                    precision: 0
                }

            }

        }

    }

});


/* =========================
   2. MONTHLY SPENDING
   LINE GRAPH
   ========================= */

new Chart(document.getElementById("spendingChart"), {

    type: "line",

    data: {

        labels: months,

        datasets: [{

            label: "Spending",

            data: spendingData,

            borderColor: "#06b6d4",

            backgroundColor: "rgba(6, 182, 212, 0.15)",

            borderWidth: 3,

            fill: true,

            tension: 0.4,

            pointRadius: 5,

            pointHoverRadius: 7
        }]

    },

    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {
                display: true
            },

            tooltip: {

                callbacks: {

                    label: function(context) {

                        return "₹ " +
                            Number(context.raw)
                            .toLocaleString("en-IN");

                    }

                }

            }

        },

        scales: {

            y: {

                beginAtZero: true,

                ticks: {

                    callback: function(value) {

                        return "₹ " +
                            Number(value)
                            .toLocaleString("en-IN");

                    }

                }

            }

        }

    }

});


/* =========================
   3. ORDERS
   BAR GRAPH
   ========================= */

new Chart(document.getElementById("ordersBarChart"), {

    type: "bar",

    data: {

        labels: months,

        datasets: [{

            label: "Orders",

            data: orderData,

            backgroundColor: "#6366f1",

            borderColor: "#4f46e5",

            borderWidth: 1,

            borderRadius: 8
        }]

    },

    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {
                display: true
            }

        },

        scales: {

            y: {

                beginAtZero: true,

                ticks: {
                    precision: 0
                }

            }

        }

    }

});


/* =========================
   4. ORDER STATUS
   PIE CHART
   ========================= */

new Chart(document.getElementById("statusChart"), {

    type: "doughnut",

    data: {

        labels: statusLabels,

        datasets: [{

            data: statusData,

            backgroundColor: [

                "#6366f1",
                "#06b6d4",
                "#10b981",
                "#f59e0b",
                "#ef4444",
                "#8b5cf6"
            ],

            borderColor: "#ffffff",

            borderWidth: 3
        }]

    },

    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {

                position: "bottom"

            }

        }

    }

});


/* =========================
   5. PAYMENT METHODS
   PIE CHART
   ========================= */

new Chart(document.getElementById("paymentChart"), {

    type: "doughnut",

    data: {

        labels: paymentLabels,

        datasets: [{

            data: paymentData,

            backgroundColor: [

                "#6366f1",
                "#06b6d4",
                "#10b981",
                "#f59e0b",
                "#ef4444",
                "#8b5cf6"
            ],

            borderColor: "#ffffff",

            borderWidth: 3
        }]

    },

    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {

                position: "bottom"

            }

        }

    }

});

</script>


<?php

include "../includes/footer.php";

?>