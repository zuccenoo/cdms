<?php
session_start();
require_once '../inv_dbconn.php';


//////////////////////////RESTORE FUNCTION//////////////////////////
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['restore_item']) && isset($_POST['item_id'])) {
    $item_id = mysqli_real_escape_string($conn, $_POST['item_id']);
    $sql = "UPDATE inventory SET archived = 0 WHERE inv_id = '$item_id'";
    if (mysqli_query($conn, $sql)) {
        echo "success";
        exit();
    } else {
        http_response_code(500);
        echo "Error restoring item: " . mysqli_error($conn);
        exit();
    }
}

//////////////////////////FORMAT DATE//////////////////////////
function formatDate($date) {
    if (!empty($date)) {
        $dateTime = new DateTime($date, new DateTimeZone('UTC'));
        $dateTime->setTimezone(new DateTimeZone('Asia/Singapore'));
        return $dateTime->format('F j, Y, g:i a');
    }
    return "N/A";
}

// Fetch archived items
$sql = "SELECT * FROM inventory WHERE archived = 1";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Archived Inventory</title>   

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
        <link rel="stylesheet" href="css_inv_archive.css">
        <link rel="icon" type="image/x-icon" href="/cdms/img/favicon_io/favicon.ico">
    </head>
    <body>
        <!-- MAIN CONTAINER -->
        <div class="main-container my-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../inv_index/inv_index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
                <h1 class="h4 mb-2 mb-md-0 sub-header"><i class="bi bi-archive"></i> Inventory Archive</h1>
                <form action="/cdms/inventory_cdms/inv_reports/inv_reports.php" method="GET" class="mb-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-bar-graph"></i> Inventory Reports
                    </button>
                </form>
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
        <div class="main-container">
            <div class="container my-4">
                <h2>Archived Inventory Items</h2>
                <table id="archivedTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Location</th>
                            <th>Supplier</th>
                            <th>Date Added</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['inv_id']) ?></td>
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                            <td><?= htmlspecialchars($row['quantity']) ?></td>
                            <td><?= htmlspecialchars($row['unit']) ?></td>
                            <td><?= htmlspecialchars($row['location_name']) ?></td>
                            <td><?= !empty($row['supplier']) ? htmlspecialchars($row['supplier']) : 'N/A' ?></td>
                            <td><?= formatDate($row['date_added']) ?></td>
                            <td><?= formatDate($row['last_updated']) ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-gear"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="openRestoreModal('<?= $row['inv_id'] ?>'); return false;">
                                                <i class="bi bi-arrow-counterclockwise text-success me-2"></i>Restore
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="openDeleteModal('<?= $row['inv_id'] ?>'); return false;">
                                                <i class="bi bi-trash text-danger me-2"></i>Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RESTORE ITEM MODAL -->
        <div class="modal fade" id="restoreItemModal" tabindex="-1" aria-labelledby="restoreItemModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restoreItemModalLabel">
                    <i class="bi bi-arrow-counterclockwise text-success me-2"></i>Restore Item
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="restoreConfirmText">Are you sure you want to restore this item?</p>
                    <input type="hidden" id="itemIdToRestore">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="confirmRestoreItem()">Restore</button>
                </div>
                </div>
            </div>
        </div>

        <!-- DELETE MODAL -->
        <div class="modal fade" id="deleteItemModal" tabindex="-1" aria-labelledby="deleteItemModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
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
        <script src="js_inv_archive.js"></script>
        <script src="/cdms/sidebar/sidebar.js"></script>
    </body>
</html>