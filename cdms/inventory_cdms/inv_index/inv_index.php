<?php
session_start();
require_once '../inv_dbconn.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$currentCategory = isset($_GET['category']) ? $_GET['category'] : 'allInventory';

/////////////////////////////////////////////// CHECK IF DUPLICATE ITEM FOR SINGLE ADD ////////////////////////////////////////////////
// AJAX endpoint to check if item exists
if (isset($_GET['check_item_exists']) && isset($_GET['item_name'])) {
    $itemName = mysqli_real_escape_string($conn, $_GET['item_name']);
    $sql = "SELECT COUNT(*) as cnt FROM inventory WHERE item_name = '$itemName'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo json_encode(['exists' => $row['cnt'] > 0]);
    exit;
}

////////////////////////////////////////// ITEM COUNT ////////////////////////////////////////////////
// Count all items
$totalItemsResult = $conn->query("SELECT COUNT(*) as total FROM inventory");
$totalItems = $totalItemsResult->fetch_assoc()['total'] ?? 0;

// Count all consumables
$totalConsumablesResult = $conn->query("SELECT COUNT(*) as total FROM inventory WHERE is_consumable = 1");
$totalConsumables = $totalConsumablesResult->fetch_assoc()['total'] ?? 0;

// Count all non-consumables
$totalNonConsumables = $totalItems - $totalConsumables;

//////////////////////////////////////////////// ITEM CODE FUNCTION////////////////////////////////////////////////
function generateItemCode($conn, $categoryCode) {
    $query = "SELECT inv_id FROM inventory WHERE inv_id LIKE '$categoryCode-%' ORDER BY inv_id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Error fetching latest item code: " . mysqli_error($conn));
    }

    $latestCode = mysqli_fetch_assoc($result)['inv_id'] ?? null;
    $num = $latestCode ? (int)substr($latestCode, strpos($latestCode, '-') + 1) + 1 : 1;

    return sprintf("%s-%03d", $categoryCode, $num);
}

$categoryCodes = [
    "Cleaning Materials" => "CM",
    "Food & Beverage" => "FB",
    "Linens" => "LN",
    "Appliances" => "AP",
    "Lights & Sounds" => "LS",
    "Tables & Chairs" => "TC",
    "Utensils" => "UT",
    "Uniform" => "UF",
    "Staff Supplies" => "SS",
    "Others" => "OT"
];

if (isset($_GET['get_item_code']) && $_GET['get_item_code'] === 'true') {
    $category = $_GET['category'];
    file_put_contents('debug_category.txt', $category); // Add this line
    $categoryCode = $categoryCodes[$category] ?? 'XX';
    
    echo generateItemCode($conn, $categoryCode);
    exit;
}

////////////////////////////////////////////////ADD & EDIT FUNCTION////////////////////////////////////////////////
//EDIT (kelangan nauuna)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_item'])) {
    $inv_id = mysqli_real_escape_string($conn, $_POST['inv_id']);
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $location_name = !empty($_POST['custom_location']) 
    ? mysqli_real_escape_string($conn, $_POST['custom_location']) 
    : mysqli_real_escape_string($conn, $_POST['location_name']);
    $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);
    $date_added = mysqli_real_escape_string($conn, $_POST['date_added']);
    $last_updated = mysqli_real_escape_string($conn, $_POST['last_updated']);
    $is_consumable = isset($_POST['is_consumable']) ? 1 : 0;

    // Convert last_updated to UTC before storing in MySQL
    $last_updated_utc = (new DateTime($last_updated, new DateTimeZone('Asia/Singapore')))
    ->setTimezone(new DateTimeZone('UTC'))
    ->format('Y-m-d H:i:s');

    // Log the converted value for debugging
    error_log("Converted last_updated to UTC: " . $last_updated_utc);

    // Log the received last_updated value for debugging
    error_log("Received last_updated: " . $last_updated);

    $sql = "UPDATE inventory SET 
                item_name = '$item_name',
                category = '$category',
                quantity = '$quantity',
                unit = '$unit',
                location_name = '$location_name',
                supplier = '$supplier',
                last_updated = '$last_updated',
                is_consumable = '$is_consumable'
            WHERE inv_id = '$inv_id'";

    if (mysqli_query($conn, $sql)) {
        echo "success:" . $inv_id;
        exit();
    } else {
        http_response_code(500);
        echo "Error updating item: " . mysqli_error($conn);
        exit();
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['item_name'])) {
    if (is_array($_POST['item_name'])) {
        // MULTI-ADD
        $newIds = [];
        foreach ($_POST['item_name'] as $i => $item_name) {
            $category = mysqli_real_escape_string($conn, $_POST['category'][$i]);
            $item_name = mysqli_real_escape_string($conn, $item_name);
            $categoryCode = $categoryCodes[$category] ?? 'XX';
            $inv_id = generateItemCode($conn, $categoryCode);

            $quantity = (int)$_POST['quantity'][$i];
            $unit = mysqli_real_escape_string($conn, $_POST['unit'][$i]);
            $location_name = mysqli_real_escape_string($conn, $_POST['location_name'][$i]);
            $supplier = !empty($_POST['supplier'][$i]) ? mysqli_real_escape_string($conn, $_POST['supplier'][$i]) : 'N/A';

            // Use date_added from form if present, else generate now
            $date_added = !empty($_POST['date_added']) ? mysqli_real_escape_string($conn, $_POST['date_added']) : (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
            $last_updated = $date_added;
            $is_consumable = (isset($_POST['is_consumable'][$i]) && $_POST['is_consumable'][$i] == 'on') ? 1 : 0;

            $sql = "INSERT INTO inventory (inv_id, category, item_name, quantity, unit, location_name, supplier, date_added, last_updated, is_consumable)
                    VALUES ('$inv_id', '$category', '$item_name', '$quantity', '$unit', '$location_name', '$supplier', '$date_added', '$last_updated', '$is_consumable')";
            mysqli_query($conn, $sql);

            $newIds[] = $inv_id;
        }
        echo "success:" . implode(',', $newIds);
        exit();
    } else {
        // SINGLE ADD
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
        $categoryCode = $categoryCodes[$category] ?? 'XX';
        $inv_id = isset($_POST['inv_id']) && $_POST['inv_id'] ? mysqli_real_escape_string($conn, $_POST['inv_id']) : generateItemCode($conn, $categoryCode);

        $quantity = (int)$_POST['quantity'];
        $unit = mysqli_real_escape_string($conn, $_POST['unit']);
        $location_name = !empty($_POST['custom_location']) 
            ? mysqli_real_escape_string($conn, $_POST['custom_location']) 
            : mysqli_real_escape_string($conn, $_POST['location_name']);
        $supplier = !empty($_POST['supplier']) ? mysqli_real_escape_string($conn, $_POST['supplier']) : 'N/A';

        // Use date_added from form if present, else generate now
        $date_added = !empty($_POST['date_added']) ? mysqli_real_escape_string($conn, $_POST['date_added']) : (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $last_updated = $date_added;
        $is_consumable = isset($_POST['is_consumable']) ? 1 : 0;

        $sql = "INSERT INTO inventory (inv_id, category, item_name, quantity, unit, location_name, supplier, date_added, last_updated, is_consumable)
                VALUES ('$inv_id', '$category', '$item_name', '$quantity', '$unit', '$location_name', '$supplier', '$date_added', '$last_updated', '$is_consumable')";

        if (mysqli_query($conn, $sql)) {
            echo "success:" . $inv_id;
            exit();
        } else {
            http_response_code(500);
            echo "Error adding item: " . mysqli_error($conn);
            exit();
        }
    }
}
/////fetch item details for edit
if (isset($_GET['get_item_details']) && isset($_GET['inv_id'])) {
    $inv_id = mysqli_real_escape_string($conn, $_GET['inv_id']);
    $sql = "SELECT * FROM inventory WHERE inv_id = '$inv_id'";
    $result = mysqli_query($conn, $sql);

    echo json_encode($result && mysqli_num_rows($result) > 0 ? mysqli_fetch_assoc($result) : []);
    exit;
}

////////////////////////////////////////////////EXPANDABLE//CHILD ROWS ////////////////////////////////////////////
if (isset($_GET['item_details_by_name'])) {
    $itemName = mysqli_real_escape_string($conn, $_GET['item_details_by_name']);
    $sql = "SELECT * FROM inventory WHERE item_name = '$itemName' AND archived = 0";
    $result = $conn->query($sql);

    echo '<table class="table table-secondary table-sm mb-0"><tbody>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>
            <td><input type="checkbox" class="child-row-checkbox" data-inv-id="' . htmlspecialchars($row['inv_id']) . '"></td>
            <td>' . htmlspecialchars($row['inv_id']) . '</td>
            <td>' . htmlspecialchars($row['item_name']) . ($row['is_consumable'] ? '<span class="badge bg-primary ms-1">cons.</span>' : '') . '</td>
            <td>' . htmlspecialchars($row['category']) . '</td>
            <td>' . $row['quantity'] . ' ' . htmlspecialchars($row['unit']) . '</td>
            <td>' . htmlspecialchars($row['location_name']) . '</td>
            <td>' . (!empty($row['supplier']) ? htmlspecialchars($row['supplier']) : 'N/A') . '</td>
            <td>' . formatDate($row['date_added']) . '</td>
            <td>' . formatDate($row['last_updated']) . '</td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear"></i>
                    </button>
                    <ul class="dropdown-menu">
                        ' . ($row['is_consumable'] ? '
                        <li>
                            <a class="dropdown-item" href="#" onclick="openConsumeModal(\'' . $row['inv_id'] . '\'); return false;">
                                <i class="bi bi-cup-straw me-2"></i>Consume
                            </a>
                        </li>
                        ' : '') . '
                        <li>
                            <a class="dropdown-item" href="#" onclick="openEditModal(\'' . $row['inv_id'] . '\'); return false;">
                                <i class="bi bi-pencil-square me-2"></i>Edit
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-secondary" href="#" onclick="openArchiveModal(\'' . $row['inv_id'] . '\'); return false;">
                                <i class="bi bi-archive me-2"></i>Archive
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="#" onclick="openDeleteModal(\'' . $row['inv_id'] . '\'); return false;">
                                <i class="bi bi-trash me-2"></i>Delete
                            </a>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>';
    }
    if ($result->num_rows === 0) {
        echo '<div class="text-center text-muted">No details found.</div>';
    }
    echo '</tbody></table>';
    exit;
}

///////////////////////////////////////////////////ARCHIVE FUNCTION////////////////////////////////////////////////
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['archive_item']) && isset($_POST['item_id'])) {
    $item_id = mysqli_real_escape_string($conn, $_POST['item_id']);
    $sql = "UPDATE inventory SET archived = 1 WHERE inv_id = '$item_id'";
    if (mysqli_query($conn, $sql)) {
        echo "success";
        exit();
    } else {
        http_response_code(500);
        echo "Error archiving item: " . mysqli_error($conn);
        exit();
    }
}
// BULK ARCHIVE FUNCTION
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['bulk_archive']) && !empty($_POST['ids'])) {
    $ids = $_POST['ids'];
    $ids = array_map(function($id) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $id) . "'";
    }, $ids);
    $idsList = implode(',', $ids);

    $sql = "UPDATE inventory SET archived = 1 WHERE inv_id IN ($idsList)";
    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Error archiving items: " . mysqli_error($conn);
    }
    exit;
}

////////////////////////////////////////////////DELETE FUNCTION////////////////////////////////////////////////
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['item_id'])) {
    // Escape input
    $item_id = mysqli_real_escape_string($conn, $_POST['item_id']);

    // SQL query to delete item from the database
    $sql = "DELETE FROM inventory WHERE inv_id = '$item_id'";

    if (mysqli_query($conn, $sql)) {
        echo "success";
        exit();
    } else {
        http_response_code(500);
        echo "Error deleting item: " . mysqli_error($conn);
        exit();
    }
}
//Bulk delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['bulk_delete']) && !empty($_POST['ids'])) {
    $ids = $_POST['ids'];
    $ids = array_map(function($id) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $id) . "'";
    }, $ids);
    $idsList = implode(',', $ids);

    $sql = "DELETE FROM inventory WHERE inv_id IN ($idsList)";
    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Error deleting items: " . mysqli_error($conn);
    }
    exit;
}

////////////////////////////////////////////////CONSUME FUNCTION////////////////////////////////////////////////
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['consume_item'])) {
    $inv_id = mysqli_real_escape_string($conn, $_POST['inv_id']);
    $consume_quantity = (int)$_POST['consume_quantity'];
    $sql = "UPDATE inventory SET quantity = quantity - $consume_quantity WHERE inv_id = '$inv_id' AND quantity >= $consume_quantity";
    if (mysqli_query($conn, $sql)) {
        // Log the consumption
        $consumed_by = isset($_SESSION['username']) ? mysqli_real_escape_string($conn, $_SESSION['username']) : null;
        $log_sql = "INSERT INTO inventory_consumption_log (inv_id, quantity_consumed, consumed_by) VALUES ('$inv_id', $consume_quantity, " . ($consumed_by ? "'$consumed_by'" : "NULL") . ")";
        mysqli_query($conn, $log_sql);

        echo "success";
        exit();
    } else {
        http_response_code(500);
        echo "Error consuming item: " . mysqli_error($conn);
        exit();
    }
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

////////////////////////////////////////////////FETCH DATA////////////////////////////////////////////////
$sql = "SELECT 
            item_name, 
            MIN(inv_id) as inv_id, 
            MIN(category) as category, 
            SUM(quantity) as total_quantity,
            GROUP_CONCAT(DISTINCT location_name SEPARATOR ', ') as locations,
            GROUP_CONCAT(DISTINCT supplier SEPARATOR ', ') as suppliers,
            COUNT(*) as item_count,
            MIN(date_added) as date_added, 
            MIN(last_updated) as last_updated, 
            MIN(is_consumable) as is_consumable,
            MIN(unit) as unit
        FROM inventory
        WHERE archived = 0
        GROUP BY item_name";
    
        // After your GROUP BY query, fetch counts for each item_name
        $itemCounts = [];
        $countResult = $conn->query("SELECT item_name, COUNT(*) as cnt FROM inventory WHERE archived = 0 GROUP BY item_name");
        while ($row = $countResult->fetch_assoc()) {
            $itemCounts[$row['item_name']] = $row['cnt'];
        }
$result = $conn->query($sql);

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CDMS - Inventory</title>

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
        <link rel="stylesheet" href="css_inv_index.css">
        <link rel="icon" type="image/x-icon" href="/cdms/img/favicon_io/favicon.ico">
        <link rel="stylesheet" href="/cdms/sidebar/sidebar.css">
    </head>

    <body>
        <!-- SIDEBAR SAKA HEADER-->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/cdms/sidebar/sidebar.php'; ?>

        <div class="main-content">
            <!-- MAIN CONTAINER -->
            <div class="main-container mt-4 p-3 bg-white rounded shadow">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12 d-flex align-items-center">
                        <i class='bx bx-box fs-1 text-success me-2'></i>
                        <h1 class="sub-header text-success mb-0">Manage Resort Inventory</h1>
                    </div>
                </div>
                <!-- Statistics Panels -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-2">
                        <div class="stat-panel bg-primary text-white rounded shadow-sm p-3 d-flex align-items-center">
                            <i class="bi bi-box-seam fs-2 me-3"></i>
                            <div>
                                <div class="fs-4 fw-bold"><?= $totalItems ?></div>
                                <div class="small">Total Items</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="stat-panel bg-success text-white rounded shadow-sm p-3 d-flex align-items-center">
                            <i class="bi bi-cup-straw fs-2 me-3"></i>
                            <div>
                                <div class="fs-4 fw-bold"><?= $totalConsumables ?></div>
                                <div class="small">Consumable Items</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="stat-panel bg-warning text-dark rounded shadow-sm p-3 d-flex align-items-center">
                            <i class="bi bi-tools fs-2 me-3"></i>
                            <div>
                                <div class="fs-4 fw-bold"><?= $totalNonConsumables ?></div>
                                <div class="small">Non-Consumable Items</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Main container Buttons -->
                <div class="button-group row mb-4">
                    <div class="col-md-3">
                        <button class="btn btn-success btn-lg btn-block w-100" onclick="openAddModal()">
                            <i class="bi bi-plus-circle me-2"></i>Add Item
                        </button>
                    </div>
                    <div class="col-md-3">
                        <form action="/cdms/inventory_cdms/inv_archive/inv_archive.php" method="GET">
                            <button class="btn btn-secondary btn-lg btn-block w-100">
                                <i class="bi bi-archive me-2"></i>View Archive
                            </button>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form action="/cdms/inventory_cdms/inv_reports/inv_reports.php" method="GET">
                            <button class="btn btn-primary btn-lg btn-block w-100">
                                <i class="bi bi-file-earmark-bar-graph me-2"></i>Inventory Reports
                            </button>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form action="/cdms/inventory_cdms/inv_chk/inv_checklist.php" method="GET">
                            <button class="btn btn-warning btn-lg btn-block w-100">
                                <i class="bi bi-card-checklist me-2"></i>View Checklist
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- TABS -->
            <div class="tab-container">
                <ul class="nav nav-tabs" id="categoryTabs" role="tablist">
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
            <div class="tabletab-container">
                <!-- TABLE -->
                <div class="table-container">
                    <div class="mb-3" id="bulk-actions-bar">
                        <button class="btn btn-primary btn-sm" id="bulkArchiveBtn" style="display:none;" title="Archive Selected Items">
                            <i class="bi bi-archive"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" id="bulkDeleteBtn" style="display:none;" title="Delete Selected Items">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button class="btn btn-secondary btn-sm" id="deselectAllBtn" style="display:none;" title="Deselect All">
                            <i class="bi bi-x-circle"></i> Deselect All
                        </button>
                    </div>
                    <table id="inventoryTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all-dt"></th>
                                <th>ID</th>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Location</th>
                                <th>Supplier</th>
                                <th>Date Added</th>
                                <th>Last Updated</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody id="inventoryBody">
                            <?php
                            if (isset($result) && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td></td><!-- Checkbox -->
                                <td><?= $row['inv_id'] ?></td>
                                <td>
                                    <?= htmlspecialchars($row['item_name']) ?>
                                    <?php if ($itemCounts[$row['item_name']] > 1): ?>
                                        (<?= $itemCounts[$row['item_name']] ?>)
                                    <?php endif; ?>
                                    <?php if ($row['is_consumable']) { ?>
                                        <span class="badge bg-primary ms-1">cons.</span>
                                    <?php } ?>
                                </td>
                                <td><?= $row['category'] ?></td>
                                <td>
                                    <?= $row['total_quantity'] ?>
                                    <?php if (!empty($row['unit'])): ?>
                                        <?= ' ' . htmlspecialchars($row['unit']) ?>
                                    <?php endif; ?>
                                    <?php if ($itemCounts[$row['item_name']] > 1): ?>
                                        total
                                    <?php endif; ?>
                                </td>
                                <td><?= $row['locations'] ?></td>
                                <td><?= !empty($row['suppliers']) ? $row['suppliers'] : 'N/A' ?></td>
                                <td data-order="<?= $row['date_added'] ?>">
                                    <?= formatDate($row['date_added']) ?>
                                </td>
                                <td data-order="<?= $row['last_updated'] ?>">
                                    <?= formatDate($row['last_updated']) ?>
                                </td>
                                <td>
                                    <?php if ($itemCounts[$row['item_name']] > 1): // Parent row with children ?>
                                        <button class="btn btn-secondary btn-sm details-control" title="Expand/Collapse">
                                            <i class="bi bi-arrow-bar-down"></i>
                                        </button>
                                    <?php else: // Single row, show normal actions ?>
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                                                <i class="bi bi-gear"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <?php if ($row['is_consumable']) { ?>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="openConsumeModal('<?= htmlspecialchars($row['inv_id'], ENT_QUOTES) ?>'); return false;">
                                                        <i class="bi bi-cup-straw me-2"></i>Consume
                                                    </a>
                                                </li>
                                                <?php } ?>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="openEditModal('<?= htmlspecialchars($row['inv_id'], ENT_QUOTES) ?>'); return false;">
                                                        <i class="bi bi-pencil-square me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-secondary" href="#" onclick="openArchiveModal('<?= htmlspecialchars($row['inv_id'], ENT_QUOTES) ?>'); return false;">
                                                        <i class="bi bi-archive me-2"></i>Archive
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" onclick="openDeleteModal('<?= htmlspecialchars($row['inv_id'], ENT_QUOTES) ?>'); return false;">
                                                        <i class="bi bi-trash me-2"></i>Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>No items found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- ADD MODAL -->
            <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addItemModalLabel">
                                <i class="bi bi-plus-circle me-2"></i>Add New Item
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addItemForm">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="itemConsumable" name="is_consumable" value="1">
                                    <label class="form-check-label" for="itemConsumable">
                                        Consumable Item
                                    </label>
                                </div>                                
                                <!-- Category, Item Code, Quantity, unit -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="itemCategory" class="form-label">Category <span class="text-danger">*</span></label>
                                        <select id="itemCategory" name="category" class="form-select" onchange="generateItemCode()" required>
                                                <option value="" disabled selected>Select Category</option>
                                                <optgroup label="Inventory Categories">
                                                    <option value="Food & Beverage">Food & Beverage</option>
                                                    <option value="Cleaning Materials">Cleaning Materials</option>
                                                    <option value="Linens">Linens</option>
                                                    <option value="Appliances">Appliances</option>
                                                    <option value="Lights & Sounds">Lights & Sounds</option>
                                                    <option value="Tables & Chairs">Tables & Chairs</option>
                                                    <option value="Utensils">Utensils</option>
                                                </optgroup>
                                                <optgroup label="Staff Items">
                                                    <option value="Uniform">Uniform</option>
                                                    <option value="Staff Supplies">Staff Supplies</option>
                                                </optgroup>
                                                <option value="Others">Others</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="itemCode" class="form-label">Item Code</label>
                                        <input type="text" id="itemCode" name="inv_id" class="form-control" placeholder="auto-generated" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="itemQuantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" id="itemQuantity" name="quantity" class="form-control" min="1" placeholder="Quantity" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="itemUnit" class="form-label">Unit</label>
                                        <select id="itemUnit" name="unit" class="form-select" required>
                                            <option value="" disabled selected>Select unit</option>
                                            <option value="pcs">pcs</option>
                                            <option value="box(s)">box(s)</option>
                                            <option value="kg(s)">kg(s)</option>
                                            <option value="liter(s)">liter(s)</option>
                                            <option value="pack(s)">pack(s)</option>
                                            <option value="set(s)">set(s)</option>
                                            <option value="meter(s)">meter(s)</option>
                                            <option value="other(s)">other(s)</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Item Name and Location-->
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label for="itemName" class="form-label">Item Name <span class="text-danger">*</span></label>
                                        <input type="text" id="itemName" name="item_name" class="form-control" placeholder="Item Name" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="itemLocation" class="form-label">Location <span class="text-danger">*</span></label>
                                        <select id="itemLocation" name="location_name" class="form-select" required>
                                            <option value="" disabled selected>Select Location</option>
                                            <optgroup label="Villa 1">
                                                <option value="Villa 1">Villa 1 - Main</option>
                                                <option value="Villa 1 - Bathroom">Villa 1 - Bathroom</option>
                                                <option value="Villa 1 - Kitchen">Villa 1 - Kitchen</option>
                                                <option value="Villa 1 - Bedroom">Villa 1 - Bedroom</option>
                                                <option value="Villa 1 - Storage">Villa 1 - Storage</option>
                                            </optgroup>
                                            <optgroup label="Villa 2">
                                                <option value="Villa 2">Villa 2 - Main</option>
                                                <option value="Villa 2 - Bathroom">Villa 2 - Bathroom</option>
                                                <option value="Villa 2 - Kitchen">Villa 2 - Kitchen</option>
                                                <option value="Villa 2 - Bedroom">Villa 2 - Bedroom</option>
                                                <option value="Villa 2 - Storage">Villa 2 - Storage</option>
                                            </optgroup>
                                            <optgroup label="Pavilion">
                                                <option value="Pavilion">Pavilion - Main</option>
                                                <option value="Pavilion - Bathroom">Pavilion - Bathroom</option>
                                                <option value="Pavilion - Storage">Pavilion - Storage</option>
                                            </optgroup>
                                            <optgroup label="Barn">
                                                <option value="Barn">Barn - Main</option>
                                                <option value="Barn - Storage">Barn - Storage</option>
                                            </optgroup>
                                            <optgroup label="Barkada 1">
                                                <option value="Barkada 1">Barkada 1 - Main</option>
                                                <option value="Barkada 1 - Bathroom">Barkada 1 - Bathroom</option>
                                                <option value="Barkada 1 - Storage">Barkada 1 - Storage</option>
                                            </optgroup>
                                            <optgroup label="Barkada 2">
                                                <option value="Barkada 2">Barkada 2 - Main</option>
                                                <option value="Barkada 2 - Bathroom">Barkada 2 - Bathroom</option>
                                                <option value="Barkada 2 - Storage">Barkada 2 - Storage</option>
                                            </optgroup>
                                            <optgroup label="Holding Room">
                                                <option value="Holding Rm">Holding Room - Main</option>
                                                <option value="Holding Rm - Bathroom">Holding Room - Bathroom</option>
                                                <option value="Holding Rm - Storage">Holding Room - Storage</option>
                                            </optgroup>
                                            <optgroup label="Family Room">
                                                <option value="Family Rm">Family Room - Main</option>
                                                <option value="Family Rm - Bathroom">Family Room - Bathroom</option>
                                                <option value="Family Rm - Storage">Family Room - Storage</option>
                                            </optgroup>
                                            <optgroup label="Caretaker's Room">
                                                <option value="CTR">CTR - Main</option>
                                                <option value="CTR - Storage">CTR - Storage</option>
                                            </optgroup>
                                            <optgroup label="Office">
                                                <option value="Office">Office - Main</option>
                                                <option value="Office - Storage">Office - Storage</option>
                                            </optgroup>
                                            <optgroup label="Poolside">
                                                <option value="Poolside">Poolside - Main</option>
                                                <option value="Poolside - Bar">Poolside - Bar</option>
                                                <option value="Poolside - Cabinet">Poolside - Cabinet</option>
                                                <option value="Poolside - Storage">Poolside - Storage</option>
                                            </optgroup>
                                            <option value="Other">Other (Please Specify)</option>
                                        </select>
                                        <div id="customLocationWrapper" style="display:none; position: relative;">
                                            <input type="text" id="customLocation" name="custom_location" class="form-control" placeholder="Please specify location">
                                            <button class="custom-location-x" type="button" id="customLocationCancel" style="position: absolute; right: 1px; top: 50%; transform: translateY(-50%); border: none; background: transparent; font-size: 1.2em; color: #333; cursor: pointer;" tabindex="-1" aria-label="Cancel">&times;</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Supplier and Date -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="itemSupplier" class="form-label">Supplier</label>
                                        <input type="text" id="itemSupplier" name="supplier" class="form-control" placeholder="Name of supplier">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="datePreview" class="form-label">Date to be added</label>
                                        <p id="datePreview" class="form-control-plaintext"></p>
                                        <input type="hidden" id="dateAdded" name="date_added">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <div class="me-auto">
                                <button type="button" class="btn btn-light" onclick="openMultiAddModal()">Add Multiple Items</button>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="addItem()">Add Item</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button> 
                        </div>
                    </div>
                </div>
            </div>

            <!-- Multi-Row Add Modal -->
            <div class="modal fade" id="multiAddModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addItemModalLabel">
                        <i class="bi bi-layers-fill me-2"></i>Add Multiple Items
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="multiAddForm">
                        <p>Add multiple items to the inventory at once. Items with the same exact <strong> item name</strong> will be grouped together.</p>
                        <div class="table-container mb-3">
                            <table class="table table-striped table-hover" id="multiAddTable">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Location</th>
                                        <th>Supplier</th>
                                        <th>is consumable</th>
                                        <th>Action(s)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input name="item_name[]" class="form-control" placeholder="Item Name" required></td>
                                        <td>
                                            <select name="category[]" class="form-select" required>
                                                <option value="" disabled selected>Select</option>
                                                <optgroup label="Inventory Categories">
                                                    <option value="Food & Beverage">Food & Beverage</option>
                                                    <option value="Cleaning Materials">Cleaning Materials</option>
                                                    <option value="Linens">Linens</option>
                                                    <option value="Appliances">Appliances</option>
                                                    <option value="Lights & Sounds">Lights & Sounds</option>
                                                    <option value="Tables & Chairs">Tables & Chairs</option>
                                                    <option value="Utensils">Utensils</option>
                                                </optgroup>
                                                <optgroup label="Staff Items">
                                                    <option value="Uniform">Uniform</option>
                                                    <option value="Staff Supplies">Staff Supplies</option>
                                                </optgroup>
                                                <option value="Others">Others</option>
                                            </select>
                                        </td>
                                        <td><input name="quantity[]" type="number" min="1" class="form-control" placeholder="Quantity" required></td>
                                        <td>
                                            <select name="unit[]" class="form-select" required>
                                                <option value="" disabled selected>Select</option>
                                                <option value="pcs">pcs</option>
                                                <option value="box(s)">box(s)</option>
                                                <option value="kg(s)">kg(s)</option>
                                                <option value="liter(s)">liter(s)</option>
                                                <option value="pack(s)">pack(s)</option>
                                                <option value="set(s)">set(s)</option>
                                                <option value="meter(s)">meter(s)</option>
                                                <option value="other(s)">other(s)</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="location_name[]" class="form-select" required>
                                                <option value="" disabled selected>Select Location</option>
                                                <optgroup label="Villa 1">
                                                    <option value="Villa 1">Villa 1 - Main</option>
                                                    <option value="Villa 1 - Bathroom">Villa 1 - Bathroom</option>
                                                    <option value="Villa 1 - Kitchen">Villa 1 - Kitchen</option>
                                                    <option value="Villa 1 - Bedroom">Villa 1 - Bedroom</option>
                                                    <option value="Villa 1 - Storage">Villa 1 - Storage</option>
                                                </optgroup>
                                                <optgroup label="Villa 2">
                                                    <option value="Villa 2">Villa 2 - Main</option>
                                                    <option value="Villa 2 - Bathroom">Villa 2 - Bathroom</option>
                                                    <option value="Villa 2 - Kitchen">Villa 2 - Kitchen</option>
                                                    <option value="Villa 2 - Bedroom">Villa 2 - Bedroom</option>
                                                    <option value="Villa 2 - Storage">Villa 2 - Storage</option>
                                                </optgroup>
                                                <optgroup label="Pavilion">
                                                    <option value="Pavilion">Pavilion - Main</option>
                                                    <option value="Pavilion - Bathroom">Pavilion - Bathroom</option>
                                                    <option value="Pavilion - Storage">Pavilion - Storage</option>
                                                </optgroup>
                                                <optgroup label="Barn">
                                                    <option value="Barn">Barn - Main</option>
                                                    <option value="Barn - Storage">Barn - Storage</option>
                                                </optgroup>
                                                <optgroup label="Barkada 1">
                                                    <option value="Barkada 1">Barkada 1 - Main</option>
                                                    <option value="Barkada 1 - Bathroom">Barkada 1 - Bathroom</option>
                                                    <option value="Barkada 1 - Storage">Barkada 1 - Storage</option>
                                                </optgroup>
                                                <optgroup label="Barkada 2">
                                                    <option value="Barkada 2">Barkada 2 - Main</option>
                                                    <option value="Barkada 2 - Bathroom">Barkada 2 - Bathroom</option>
                                                    <option value="Barkada 2 - Storage">Barkada 2 - Storage</option>
                                                </optgroup>
                                                <optgroup label="Holding Room">
                                                    <option value="Holding Rm">Holding Room - Main</option>
                                                    <option value="Holding Rm - Bathroom">Holding Room - Bathroom</option>
                                                    <option value="Holding Rm - Storage">Holding Room - Storage</option>
                                                </optgroup>
                                                <optgroup label="Family Room">
                                                    <option value="Family Rm">Family Room - Main</option>
                                                    <option value="Family Rm - Bathroom">Family Room - Bathroom</option>
                                                    <option value="Family Rm - Storage">Family Room - Storage</option>
                                                </optgroup>
                                                <optgroup label="Caretaker's Room">
                                                    <option value="CTR">CTR - Main</option>
                                                    <option value="CTR - Storage">CTR - Storage</option>
                                                </optgroup>
                                                <optgroup label="Office">
                                                    <option value="Office">Office - Main</option>
                                                    <option value="Office - Storage">Office - Storage</option>
                                                </optgroup>
                                                <optgroup label="Poolside">
                                                    <option value="Poolside">Poolside - Main</option>
                                                    <option value="Poolside - Bar">Poolside - Bar</option>
                                                    <option value="Poolside - Cabinet">Poolside - Cabinet</option>
                                                    <option value="Poolside - Storage">Poolside - Storage</option>
                                                </optgroup>
                                                <option value="Other">Other</option>
                                            </select>
                                        </td>
                                        <td><input name="supplier[]" class="form-control" placeholder="Supplier"></td>
                                        <td><input name="is_consumable[]" type="checkbox" class="form-check-input"></td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" onclick="copyRow(this)" title="Duplicate Row"><i class="bi bi-clipboard"></i></button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)" title="Remove Row"><i class="bi bi-x"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-secondary" onclick="addRow()">Add Row</button>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <label for="multiDatePreview" class="form-label mb-0 me-2">Date to be added</label>
                                    <p id="multiDatePreview" class="form-control-plaintext mb-0 me-2"></p>
                                    <input type="hidden" id="multiDateAdded" name="date_added">
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="submitMultiAdd()">Add Items</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                    </div>
                </div>
            </div>

            <!-- EDIT MODAL -->
            <div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">                  
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editItemModalLabel">
                                <i class="bi bi-pencil-square me-2"></i>Edit Item
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <form id="editItemForm">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="editItemConsumable" name="is_consumable" value="1">
                                    <label class="form-check-label" for="editItemConsumable">
                                        Consumable Item
                                    </label>
                                </div>
                                <!-- Category, Item Code, quantity and unit -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="editItemCategory" class="form-label">Category</label>
                                        <input type="text" id="editItemCategory" name="category" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="editItemId" class="form-label">Item Code</label>
                                        <input type="text" id="editItemId" name="inv_id" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="editItemQuantity" class="form-label">Quantity</label>
                                        <input type="number" id="editItemQuantity" name="quantity" class="form-control" min="1" required>
                                    </div>

                                    <div class="col-md-2">
                                        <label for="editItemUnit" class="form-label">Unit</label>
                                        <select id="editItemUnit" name="unit" class="form-select" required>
                                            <option value="" disabled selected>Select unit</option>
                                            <option value="pcs">pcs</option>
                                            <option value="box(s)">box(s)</option>
                                            <option value="kg(s)">kg(s)</option>
                                            <option value="liter(s)">liter(s)</option>
                                            <option value="pack(s)">pack(s)</option>
                                            <option value="set(s)">set(s)</option>
                                            <option value="meter(s)">meter(s)</option>
                                            <option value="other(s)">other(s)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Item name and Location -->
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label for="editItemName" class="form-label">Item Name</label>
                                        <input type="text" id="editItemName" name="item_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="editItemLocation" class="form-label">Location <span class="text-danger">*</span></label>
                                        <select id="editItemLocation" name="location_name" class="form-select" required>
                                            <option value="" disabled selected>Select Location</option>
                                            <optgroup label="Villa 1">
                                                <option value="Villa 1">Villa 1 - Main</option>
                                                <option value="Villa 1 - Bathroom">Villa 1 - Bathroom</option>
                                                <option value="Villa 1 - Kitchen">Villa 1 - Kitchen</option>
                                                <option value="Villa 1 - Bedroom">Villa 1 - Bedroom</option>
                                                <option value="Villa 1 - Storage">Villa 1 - Storage</option>
                                            </optgroup>
                                            <optgroup label="Villa 2">
                                                <option value="Villa 2">Villa 2 - Main</option>
                                                <option value="Villa 2 - Bathroom">Villa 2 - Bathroom</option>
                                                <option value="Villa 2 - Kitchen">Villa 2 - Kitchen</option>
                                                <option value="Villa 2 - Bedroom">Villa 2 - Bedroom</option>
                                                <option value="Villa 2 - Storage">Villa 2 - Storage</option>
                                            </optgroup>
                                            <optgroup label="Pavilion">
                                                <option value="Pavilion">Pavilion - Main</option>
                                                <option value="Pavilion - Bathroom">Pavilion - Bathroom</option>
                                                <option value="Pavilion - Storage">Pavilion - Storage</option>
                                            </optgroup>
                                            <optgroup label="Barn">
                                                <option value="Barn">Barn - Main</option>
                                                <option value="Barn - Storage">Barn - Storage</option>
                                            </optgroup>
                                            <optgroup label="Barkada 1">
                                                <option value="Barkada 1">Barkada 1 - Main</option>
                                                <option value="Barkada 1 - Bathroom">Barkada 1 - Bathroom</option>
                                                <option value="Barkada 1 - Storage">Barkada 1 - Storage</option>
                                            </optgroup>
                                            <optgroup label="Barkada 2">
                                                <option value="Barkada 2">Barkada 2 - Main</option>
                                                <option value="Barkada 2 - Bathroom">Barkada 2 - Bathroom</option>
                                                <option value="Barkada 2 - Storage">Barkada 2 - Storage</option>
                                            </optgroup>
                                            <optgroup label="Holding Room">
                                                <option value="Holding Rm">Holding Room - Main</option>
                                                <option value="Holding Rm - Bathroom">Holding Room - Bathroom</option>
                                                <option value="Holding Rm - Storage">Holding Room - Storage</option>
                                            </optgroup>
                                            <optgroup label="Family Room">
                                                <option value="Family Rm">Family Room - Main</option>
                                                <option value="Family Rm - Bathroom">Family Room - Bathroom</option>
                                                <option value="Family Rm - Storage">Family Room - Storage</option>
                                            </optgroup>
                                            <optgroup label="Caretaker's Room">
                                                <option value="CTR">CTR - Main</option>
                                                <option value="CTR - Storage">CTR - Storage</option>
                                            </optgroup>
                                            <optgroup label="Office">
                                                <option value="Office">Office - Main</option>
                                                <option value="Office - Storage">Office - Storage</option>
                                            </optgroup>
                                            <optgroup label="Poolside">
                                                <option value="Poolside">Poolside - Main</option>
                                                <option value="Poolside - Bar">Poolside - Bar</option>
                                                <option value="Poolside - Cabinet">Poolside - Cabinet</option>
                                                <option value="Poolside - Storage">Poolside - Storage</option>
                                            </optgroup>
                                            <option value="Other">Other (Please Specify)</option>
                                        </select>
                                        <div id="editCustomLocationWrapper" style="display:none;">
                                            <input type="text" id="editCustomLocation" name="custom_location" class="form-control" placeholder="Please specify location">
                                            <button class="custom-location-x" type="button" id="editCustomLocationCancel" style="position: absolute; right: 1px; top: 50%; transform: translateY(-50%); border: none; background: transparent; font-size: 1.2em; color: #333; cursor: pointer;" tabindex="-1" aria-label="Cancel">&times;</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Supplier, Date Added and Last Updated -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="editItemSupplier" class="form-label">Supplier</label>
                                        <input type="text" id="editItemSupplier" name="supplier" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="editDatePreview" class="form-label">Date Added</label>
                                        <p id="editDatePreview" class="form-control-plaintext"></p>
                                        <input type="hidden" id="editDateAdded" name="date_added">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="editUpdatedPreview" class="form-label">Last Updated</label>
                                        <p id="editUpdatedPreview" class="form-control-plaintext"></p>
                                        <input type="hidden" id="editLastUpdated" name="last_updated">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="editItem()">Save Changes</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DELETE MODAL -->
            <div class="modal fade" id="deleteItemModal" tabindex="-1" aria-labelledby="deleteItemModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteItemModalLabel">
                                <i class="bi bi-trash-fill text-danger me-2"></i>Delete Item
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p id="deleteConfirmText">Are you sure you want to delete this item?</p>

                            <input type="hidden" id="itemIdToDelete">
                            <input type="hidden" id="itemNameToDelete">

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" onclick="deleteItem()">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ARCHIVE MODAL -->
            <div class="modal fade" id="archiveItemModal" tabindex="-1" aria-labelledby="archiveItemModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="archiveItemModalLabel">
                                <i class="bi bi-archive text-secondary me-2"></i>Archive Item
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p id="archiveConfirmText">Are you sure you want to archive this item?</p>
                            <input type="hidden" id="itemIdToArchive">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-warning" onclick="archiveItem()">Archive</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONSUME MODAL -->
            <div class="modal fade" id="consumeModal" tabindex="-1" aria-labelledby="consumeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                    <form id="consumeForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="consumeModalLabel">Consume Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="consume_item" value="true">
                            <input type="hidden" id="consumeItemId" name="inv_id">

                            <label for="consumeQuantity" class="form-label">Quantity to consume</label>
                            <div id="consumeErrorMsg" class="text-danger mt-2" style="display:none;"></div>
                            <input type="number" id="consumeQuantity" name="consume_quantity" class="form-control" min="1" required>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" id="consumeSubmitBtn" class="btn btn-primary">Consume</button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>

            <!-- SUCCESS MODAL -->
            <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="successModalLabel"> <i class="bi bi-check-circle-fill text-success me-2"></i>Success!</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>The item has been added successfully.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ITEM EXISTS//ALERT MODAL -->
            <div class="modal fade" id="itemExistsModal" tabindex="-1" aria-labelledby="itemExistsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="itemExistsModalLabel">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Item Already Exists
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>
                                The item "<span id="existsItemName"></span>" already exists.<br>
                                Do you still wish to continue?
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="continueAddBtn">Continue Anyway</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- ///////////////////////////////////SCRIPTS/////////////////////////////////// -->
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
        <script src="js_inv_index.js"></script>
        <script src="/cdms/sidebar/sidebar.js"></script>
    </body>
</html>
