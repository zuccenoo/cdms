<?php
session_start();
require_once '../inv_dbconn.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
/////////////////////////////////////////////////////FETCH GUEST CHECKLIST////////////////////////////////////////////////
if (isset($_GET['location'])) {
    $location = mysqli_real_escape_string($conn, $_GET['location']);
    if (!$location) {
        echo '<div class="alert alert-warning">No location specified.</div>';
        exit;
    }

    $result = $conn->query("
        SELECT 
            ic.checklist_id, 
            ic.inv_id, 
            i.item_name, 
            i.category, 
            i.quantity,
            i.unit,
            i.last_updated, 
            ic.status_check_in, 
            ic.status_check_out,
            ic.checked_at
        FROM inventory_checklist ic
        JOIN inventory i ON ic.inv_id = i.inv_id
        WHERE i.location_name = '$location'
        ORDER BY i.item_name
    ");

if ($result && $result->num_rows > 0) {
    echo '<div class="table-responsive"><table class="table table-striped table-hover table-bordered align-middle">';
    echo '<thead><tr>
        <th>Item Code</th>
        <th>Item Name</th>
        <th>Category</th>
        <th>Quantity</th>
        <th>Last Updated</th>
        <th>Status (Check-in)</th>
        <th>Status (Check-out)</th>
        <th>Last Checked</th>
    </tr></thead><tbody>';
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>" . htmlspecialchars($row['inv_id']) . "</td>
            <td>" . htmlspecialchars($row['item_name']) . "</td>
            <td>" . htmlspecialchars($row['category']) . "</td>
            <td>" . htmlspecialchars($row['quantity'] . ' ' . $row['unit']) . "</td>
            <td>" . htmlspecialchars($row['last_updated']) . "</td>
            <td>
                <form method='POST' action='inv_checklist.php'>
                    <input type='hidden' name='checklist_id' value='" . htmlspecialchars($row['checklist_id']) . "'>
                    <input type='hidden' name='status_type' value='status_check_in'>
                    <select name='status' onchange='this.form.submit()'>
                        <option value='Good' " . ($row['status_check_in'] === 'Good' ? 'selected' : '') . ">Good</option>
                        <option value='Missing' " . ($row['status_check_in'] === 'Missing' ? 'selected' : '') . ">Missing</option>
                        <option value='Broken' " . ($row['status_check_in'] === 'Broken' ? 'selected' : '') . ">Broken</option>
                        <option value='Damaged' " . ($row['status_check_in'] === 'Damaged' ? 'selected' : '') . ">Damaged</option>
                    </select>
                </form>
            </td>
            <td>
                <form method='POST' action='inv_checklist.php'>
                    <input type='hidden' name='checklist_id' value='" . htmlspecialchars($row['checklist_id']) . "'>
                    <input type='hidden' name='status_type' value='status_check_out'>
                    <select name='status' onchange='this.form.submit()'>
                        <option value='Good' " . ($row['status_check_out'] === 'Good' ? 'selected' : '') . ">Good</option>
                        <option value='Missing' " . ($row['status_check_out'] === 'Missing' ? 'selected' : '') . ">Missing</option>
                        <option value='Broken' " . ($row['status_check_out'] === 'Broken' ? 'selected' : '') . ">Broken</option>
                        <option value='Damaged' " . ($row['status_check_out'] === 'Damaged' ? 'selected' : '') . ">Damaged</option>
                    </select>
                </form>
            </td>
            <td>" . htmlspecialchars($row['checked_at']) . "</td>
        </tr>";
    }
    echo '</tbody>
    <tfoot>
        <tr>
            <th colspan="8" class="text-start">
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <span class="fw-bold me-2">Amenities Check:</span>
                    <div>
                        <label class="me-1">Floorings</label>
                        <select class="form-select d-inline-block w-auto" name="amenity_floorings">
                            <option value="Good">Good</option>
                            <option value="Broken">Broken</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>
                    <div>
                        <label class="me-1">Walls</label>
                        <select class="form-select d-inline-block w-auto" name="amenity_walls">
                            <option value="Good">Good</option>
                            <option value="Broken">Broken</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>
                    <div>
                        <label class="me-1">Ceilings</label>
                        <select class="form-select d-inline-block w-auto" name="amenity_ceilings">
                            <option value="Good">Good</option>
                            <option value="Broken">Broken</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>
                    <div>
                        <label class="me-1">Rooms</label>
                        <select class="form-select d-inline-block w-auto" name="amenity_rooms">
                            <option value="Good">Good</option>
                            <option value="Broken">Broken</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>
                    <div>
                        <label class="me-1">Pool</label>
                        <select class="form-select d-inline-block w-auto" name="amenity_pool">
                            <option value="Good">Good</option>
                            <option value="Broken">Broken</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>
                </div>
            </th>
        </tr>
    </tfoot>
    </table></div>';
    } else {
        echo '<div class="alert alert-info">No checklist items found for this location.</div>';
    }
    exit; // Prevent the rest of the page from rendering
}


///////////////////////////////////////////////////ITEM STATUS CHECK////////////////////////////////////////////////
// Count statuses for check-in
$statusCounts = [
    'Good' => 0,
    'Missing' => 0,
    'Broken' => 0,
    'Damaged' => 0
];

$countResult = $conn->query("SELECT status_check_in, COUNT(*) as count FROM inventory_checklist GROUP BY status_check_in");
if ($countResult) {
    while ($row = $countResult->fetch_assoc()) {
        $status = $row['status_check_in'];
        if (isset($statusCounts[$status])) {
            $statusCounts[$status] = $row['count'];
        }
    }
}

// Handle status updates for check-in and check-out
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checklist_id'], $_POST['status_type'], $_POST['status'])) {
    $checklist_id = mysqli_real_escape_string($conn, $_POST['checklist_id']);
    $status_type = mysqli_real_escape_string($conn, $_POST['status_type']); // Either 'status_check_in' or 'status_check_out'
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Update the status in the inventory_checklist table
    $sql = "UPDATE inventory_checklist SET $status_type = '$status', checked_at = NOW() WHERE checklist_id = '$checklist_id'";

    if ($conn->query($sql)) {
        // Redirect back to the checklist page to avoid form resubmission
        header("Location: inv_checklist.php");
        exit();
    } else {
        echo "<script>alert('Error updating status: " . $conn->error . "');</script>";
    }
}

////////////////////////////////////////////////FORMAT DATE FUNCTION////////////////////////////////////////////////
function formatDate($date) {
    return !empty($date) ? (new DateTime($date))->format('F j, Y, g:i a') : "N/A";
}

////////////////////////////////////////////////FETCH CHECKLIST FUNCTION////////////////////////////////////////////////
function fetchChecklistData($conn) {
    $sql = "SELECT 
                ic.checklist_id, 
                ic.inv_id, 
                i.item_name, 
                i.category, 
                i.quantity, 
                ic.status_check_in, 
                ic.status_check_out, 
                ic.checked_at 
            FROM inventory_checklist ic
            JOIN inventory i ON ic.inv_id = i.inv_id";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Output the row
            echo "<tr>
                    <td>{$row['inv_id']}</td>
                    <td>{$row['item_name']}</td>
                    <td>{$row['category']}</td>
                    <td>{$row['quantity']}</td>
                    <td>{$row['status_check_in']}</td>
                    <td>
                        <form method='POST' action='inv_checklist.php'>
                            <input type='hidden' name='checklist_id' value='{$row['checklist_id']}'>
                            <input type='hidden' name='status_type' value='status_check_out'>
                            <select name='status' onchange='this.form.submit()'>
                                <option value='Good' " . ($row['status_check_out'] === 'Good' ? 'selected' : '') . ">Good</option>
                                <option value='Missing' " . ($row['status_check_out'] === 'Missing' ? 'selected' : '') . ">Missing</option>
                                <option value='Broken' " . ($row['status_check_out'] === 'Broken' ? 'selected' : '') . ">Broken</option>
                                <option value='Damaged' " . ($row['status_check_out'] === 'Damaged' ? 'selected' : '') . ">Damaged</option>
                            </select>
                        </form>
                    </td>
                    <td>" . formatDate($row['checked_at']) . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='7' style='text-align: center;'>No checklist items found.</td></tr>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CDMS - Inventory Checklist</title>

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
        <link rel="stylesheet" href="css_inv_chk.css">
        <link rel="icon" type="image/x-icon" href="/cdms/img/favicon_io/favicon.ico">
        <link rel="stylesheet" href="/cdms/sidebar/sidebar.css">
    </head>

    <body>
        <!-- MAIN CONTAINER -->
        <div class="container my-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../inv_index/inv_index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
                <h1 class="h4 mb-2 mb-md-0 sub-header"><i class="bi bi-check-circle"></i> Guest Inventory Checklist</h1>
                <form action="/cdms/inventory_cdms/inv_reports/inv_reports.php" method="GET" class="mb-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-bar-graph"></i> Inventory Reports
                    </button>
                </form>
            </div>
        </div>

        <!-- CURRENT GUEST LIST -->
        <div class="container my-4">
            <h2 class="mb-3">Current Guest List</h2>
            <div class="table-responsive">
                <table id="guestTable" class="table table-sm table-striped table-hover table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Reservation ID</th>
                            <th>Type of Event</th>
                            <th>Venue Type</th>
                            <th>Guest Number</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Sex</th>
                            <th>Address</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Check-in Date</th>
                            <th>Check-out Date</th>
                            <th>Reservation Date</th>
                            <th>Total Downpayment</th>
                            <th>Mode of Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $resResult = $conn->query("SELECT * FROM reservation ORDER BY reservation_id DESC");
                        if ($resResult && $resResult->num_rows > 0) {
                            while ($row = $resResult->fetch_assoc()) {
                                echo "<tr class='guest-row' data-location=\"" . htmlspecialchars($row['venue_type']) . "\">";
                                echo "<td>" . htmlspecialchars($row['reservation_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['event_type']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['venue_type']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['guest_number']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['guest_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['guest_age']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['guest_sex']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['guest_address']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['guest_contact']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['guest_email']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['checkin_date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['checkout_date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['reservation_date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['total_downpayment']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['payment_mode']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr><td colspan="15" style="text-align: center;">No upcoming checkouts found.</td>
                                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                </tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- INVENTORY CHECKLIST -->
        <div class="container my-4">
            <h2 class="mb-3">Inventory Checklist (All Locations)</h2>
            <div class="mb-3">
                <span class="badge bg-success me-2">Good: <?= $statusCounts['Good'] ?></span>
                <span class="badge bg-warning text-dark me-2">Missing: <?= $statusCounts['Missing'] ?></span>
                <span class="badge bg-danger me-2">Broken: <?= $statusCounts['Broken'] ?></span>
                <span class="badge bg-secondary me-2">Damaged: <?= $statusCounts['Damaged'] ?></span>
            </div>
            <div class="table-responsive">
                <table id="checklistTable" class="table table-striped table-hover table-bordered align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>Location</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Last Updated</th>
                            <th>Status (Check-in)</th>
                            <th>Status (Check-out)</th>
                            <th>Last Checked</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("
                            SELECT 
                                ic.checklist_id, 
                                ic.inv_id, 
                                i.item_name, 
                                i.category, 
                                i.quantity,
                                i.unit,
                                i.last_updated, 
                                i.location_name,
                                ic.status_check_in, 
                                ic.status_check_out,
                                ic.checked_at
                            FROM inventory_checklist ic
                            JOIN inventory i ON ic.inv_id = i.inv_id
                            ORDER BY i.location_name, i.item_name
                        ");

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['location_name']) . "</td>
                                        <td>" . htmlspecialchars($row['inv_id']) . "</td>
                                        <td>" . htmlspecialchars($row['item_name']) . "</td>
                                        <td>" . htmlspecialchars($row['category']) . "</td>
                                        <td>" . htmlspecialchars($row['quantity'] . ' ' . $row['unit']) . "</td>
                                        <td>" . formatDate($row['last_updated']) . "</td>
                                        <td>
                                            <form method='POST' action='inv_checklist.php'>
                                                <input type='hidden' name='checklist_id' value='" . htmlspecialchars($row['checklist_id']) . "'>
                                                <input type='hidden' name='status_type' value='status_check_in'>
                                                <select name='status' onchange='this.form.submit()'>
                                                    <option value='Good' " . ($row['status_check_in'] === 'Good' ? 'selected' : '') . ">Good</option>
                                                    <option value='Missing' " . ($row['status_check_in'] === 'Missing' ? 'selected' : '') . ">Missing</option>
                                                    <option value='Broken' " . ($row['status_check_in'] === 'Broken' ? 'selected' : '') . ">Broken</option>
                                                    <option value='Damaged' " . ($row['status_check_in'] === 'Damaged' ? 'selected' : '') . ">Damaged</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <form method='POST' action='inv_checklist.php'>
                                                <input type='hidden' name='checklist_id' value='" . htmlspecialchars($row['checklist_id']) . "'>
                                                <input type='hidden' name='status_type' value='status_check_out'>
                                                <select name='status' onchange='this.form.submit()'>
                                                    <option value='Good' " . ($row['status_check_out'] === 'Good' ? 'selected' : '') . ">Good</option>
                                                    <option value='Missing' " . ($row['status_check_out'] === 'Missing' ? 'selected' : '') . ">Missing</option>
                                                    <option value='Broken' " . ($row['status_check_out'] === 'Broken' ? 'selected' : '') . ">Broken</option>
                                                    <option value='Damaged' " . ($row['status_check_out'] === 'Damaged' ? 'selected' : '') . ">Damaged</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>" . formatDate($row['checked_at']) . "</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9' class='text-center'>No checklist items found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Inventory Checklist Modal -->
        <div class="modal fade" id="locationChecklistModal" tabindex="-1" aria-labelledby="locationChecklistModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="locationChecklistModalLabel">Inventory Checklist for <span id="modalLocationName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalChecklistBody">
                    <!-- Checklist table will be loaded here -->
                    <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        <script src="js_inv_chk.js"></script>
        <script src="/cdms/sidebar/sidebar.js"></script>
    </body>
</html>