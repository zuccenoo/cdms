<?php
session_start();
require_once '../inv_dbconn.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

///////////////////////////////////////////////////TITLE AND DATE CONDITION////////////////////////////////////////////////
$period = isset($_GET['period']) ? $_GET['period'] : 'day';

switch ($period) {
    case 'week':
        $title = "Consumption Log for This Week";
        $date_condition = "YEARWEEK(l.consumed_at, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'month':
        $title = "Consumption Log for This Month";
        $date_condition = "MONTH(l.consumed_at) = MONTH(CURDATE()) AND YEAR(l.consumed_at) = YEAR(CURDATE())";
        break;
    case 'year':
        $title = "Consumption Log for This Year";
        $date_condition = "YEAR(l.consumed_at) = YEAR(CURDATE())";
        break;
    case 'all':
        $title = "Consumption Log (All Time)";
        $date_condition = "1";
        break;
    default:
        $title = "Consumption Log for Today";
        $date_condition = "DATE(l.consumed_at) = CURDATE()";
        break;
}

////////////////////////////////////////////////FORMAT DATE FUNCTION////////////////////////////////////////////////
function formatDate($date) {
    if (!empty($date)) {
        $dateTime = new DateTime($date, new DateTimeZone('UTC'));
        $dateTime->setTimezone(new DateTimeZone('Asia/Singapore'));
        return $dateTime->format('F j, Y, g:i a');
    }
    return "N/A";
}

////////////////////////////////////////////////FETCH DATA FUNCTION////////////////////////////////////////////////
// Fetch consumption log based on the selected period
$sql = "SELECT l.*, i.item_name, i.category, i.unit, i.location_name
    FROM inventory_consumption_log l
    JOIN inventory i ON l.inv_id = i.inv_id
    WHERE $date_condition
    ORDER BY l.consumed_at DESC";
$result = mysqli_query($conn, $sql);

// Fetch stock levels for all items
$stockType = $_GET['stockType'] ?? 'all';
$stockPeriod = $_GET['stockPeriod'] ?? 'all';

$where = [];
if ($stockType == 'consumable') {
    $where[] = "is_consumable = 1";
} elseif ($stockType == 'nonconsumable') {
    $where[] = "is_consumable = 0";
}

// Period condition (using date_added or last_updated)
switch ($stockPeriod) {
    case 'day':
        $where[] = "DATE(last_updated) = CURDATE()";
        break;
    case 'week':
        $where[] = "YEARWEEK(last_updated, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'month':
        $where[] = "MONTH(last_updated) = MONTH(CURDATE()) AND YEAR(last_updated) = YEAR(CURDATE())";
        break;
    case 'year':
        $where[] = "YEAR(last_updated) = YEAR(CURDATE())";
        break;
    // 'all' or default: no date filter
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stock_sql = "SELECT inv_id, item_name, category, quantity, unit, location_name, supplier, is_consumable, last_updated FROM inventory $where_sql ORDER BY category, item_name";
$stock_result = mysqli_query($conn, $stock_sql);
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CDMS - Inventory Reports</title>

        <!-- DataTables CSS -->
        <!--<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">-->
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
        <!-- DataTables Select extension CSS/JS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css">
        <!-- Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
        
        <!-- ICONS/FONTS -->
        <!-- fontawesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <!-- Google Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
        <!-- Boxicons CSS -->
        <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />

        <!-- Custom Styles -->
        <link rel="stylesheet" href="css_inv_reports.css">
        <link rel="icon" type="image/x-icon" href="/cdms/img/favicon_io/favicon.ico">
    </head>
    <body>
        <!-- MAIN CONTAINER -->
        <div class="main-container my-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../inv_index/inv_index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
                <h1 class="h4 mb-2 mb-md-0 sub-header"><i class="bi bi-file-earmark-bar-graph"></i> Inventory Reports</h1>
                <form action="/cdms/inventory_cdms/inv_reports/inv_reports.php" method="GET" class="mb-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-bar-graph"></i> Inventory Reports
                    </button>
                </form>
            </div>
        </div>

        <!-- CONSUMPTION LOG -->
        <!-- TABS -->
        <div class="tab-container">
            <ul class="nav nav-tabs" id="categoryTabs-consumption" role="tablist">
                <!-- Inventory Items -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-category="allInventory" type="button" role="tab">All</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Food & Beverage" type="button" role="tab">Food & Beverage</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Cleaning Materials" type="button" role="tab">Cleaning Materials</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Linens" type="button" role="tab">Linens</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Appliances" type="button" role="tab">Appliances</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Lights & Sounds" type="button" role="tab">Lights & Sounds</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Tables & Chairs" type="button" role="tab">Tables & Chairs</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Utensils" type="button" role="tab">Utensils</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Others" type="button" role="tab">Others</button>
                </li>
                <!-- Staff Items -->
                <li class="nav-item dropdown" role="presentation">
                    <button class="nav-link dropdown-toggle" data-bs-toggle="dropdown" type="button" role="tab">Staff Items</button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" data-category="Uniform" href="#">Uniform</a></li>
                        <li><a class="dropdown-item" data-category="Staff Supplies" href="#">Staff Supplies</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="main-container bg-white rounded shadow p-4">
            <!-- Period Filter and Title (Same Line) -->
            <div class="d-flex justify-content-between align-items-center my-4 flex-wrap gap-2">
                <h3 class="mb-0"><?= $title ?><span class="text-muted ms-3">
                    (<?= htmlspecialchars(formatDate(date('Y-m-d H:i:s'))) ?>)
                </span></h3>
                <form method="get" class="mb-0" id="periodForm">
                    <label for="period" class="me-2 fw-bold">Show:</label>
                    <select name="period" id="period" class="form-select d-inline-block w-auto">
                        <option value="day" <?= $period == 'day' ? 'selected' : '' ?>>Today</option>
                        <option value="week" <?= $period == 'week' ? 'selected' : '' ?>>This Week</option>
                        <option value="month" <?= $period == 'month' ? 'selected' : '' ?>>This Month</option>
                        <option value="year" <?= $period == 'year' ? 'selected' : '' ?>>This Year</option>
                        <option value="all" <?= $period == 'all' ? 'selected' : '' ?>>All Time</option>
                    </select>
                    <button type="submit" class="btn btn-primary ms-2">Filter</button>
                </form>
            </div>
            <div class="table-responsive">
                <table id="consumptionTable" class="table table-striped table-hover table-bordered align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>Date Consumed</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Quantity Consumed</th>
                            <th>Consumed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) === 0): ?>
                        <tr>
                            <td class="text-center text-muted">No items consumed today.</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars(formatDate($row['consumed_at'])) ?></td>
                                <td><?= htmlspecialchars($row['inv_id']) ?></td>
                                <td><?= htmlspecialchars($row['item_name']) ?></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= htmlspecialchars($row['location_name']) ?></td>
                                <td><?= htmlspecialchars($row['quantity_consumed']) . ' ' . htmlspecialchars($row['unit']) ?></td>
                                <td><?= htmlspecialchars($row['consumed_by']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- STOCK LEVEL LOG -->
        <!-- TABS -->
        <div class="tab-container">
            <ul class="nav nav-tabs" id="categoryTabs-stock" role="tablist">
                <!-- Inventory Items -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-category="allInventory" type="button" role="tab">All</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Food & Beverage" type="button" role="tab">Food & Beverage</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Cleaning Materials" type="button" role="tab">Cleaning Materials</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Linens" type="button" role="tab">Linens</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Appliances" type="button" role="tab">Appliances</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Lights & Sounds" type="button" role="tab">Lights & Sounds</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Tables & Chairs" type="button" role="tab">Tables & Chairs</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Utensils" type="button" role="tab">Utensils</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-category="Others" type="button" role="tab">Others</button>
                </li>
                <!-- Staff Items -->
                <li class="nav-item dropdown" role="presentation">
                    <button class="nav-link dropdown-toggle" data-bs-toggle="dropdown" type="button" role="tab">Staff Items</button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" data-category="Uniform" href="#">Uniform</a></li>
                        <li><a class="dropdown-item" data-category="Staff Supplies" href="#">Staff Supplies</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="main-container bg-white rounded shadow p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h3 class="text-success mb-0">Stock Level Report</h3>
                <form method="get" class="mb-0 d-flex align-items-center flex-wrap gap-2 text-end">
                    <label for="stockType" class="me-2 fw-bold mb-0">Show:</label>
                    <select name="stockType" id="stockType" class="form-select d-inline-block w-auto">
                        <option value="all" <?= ($stockType == 'all') ? 'selected' : '' ?>>All Items</option>
                        <option value="consumable" <?= ($stockType == 'consumable') ? 'selected' : '' ?>>Consumables Only</option>
                        <option value="nonconsumable" <?= ($stockType == 'nonconsumable') ? 'selected' : '' ?>>Non-Consumables Only</option>
                    </select>
                    <label for="stockPeriod" class="ms-3 me-2 fw-bold mb-0">Period:</label>
                    <select name="stockPeriod" id="stockPeriod" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                        <option value="all" <?= ($stockPeriod == 'all') ? 'selected' : '' ?>>All Time</option>
                        <option value="day" <?= ($stockPeriod == 'day') ? 'selected' : '' ?>>Today</option>
                        <option value="week" <?= ($stockPeriod == 'week') ? 'selected' : '' ?>>This Week</option>
                        <option value="month" <?= ($stockPeriod == 'month') ? 'selected' : '' ?>>This Month</option>
                        <option value="year" <?= ($stockPeriod == 'year') ? 'selected' : '' ?>>This Year</option>
                    </select>
                    <button type="submit" class="btn btn-primary ms-2">Filter</button>
                </form>
            </div>
            <p class="mt-2 text-danger">*Rows highlighted in red are low stock (≤ 5 units).</p>
            <div class="table-responsive">
                <table id="stockLevelTable" class="table table-striped table-hover table-bordered align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Location</th>
                            <th>Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($stock_result)): ?>
                        <tr<?= ($row['quantity'] <= 5 ? ' class="table-danger"' : '') ?>>
                            <td><?= htmlspecialchars($row['inv_id']) ?></td>
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                            <td><?= htmlspecialchars($row['quantity']) . ' ' . htmlspecialchars($row['unit']) ?></td>
                            <td><?= htmlspecialchars($row['location_name']) ?></td>
                            <td><?= htmlspecialchars($row['supplier']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        
        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
        <!-- DataTables Buttons extension CSS/JS -->
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
        <!-- Bootstrap JS -->
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- For PDF export (uses pdfmake) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
        <!-- For Excel export (uses JSZip) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

        <!-- Custom JS -->
        <script src="js_inv_reports.js"></script>
    </body>
</html>