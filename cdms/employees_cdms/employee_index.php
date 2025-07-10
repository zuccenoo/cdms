<?php
session_start();
require_once 'emp_dbconn.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

//////////////////////////////////////////////// EMPLOYEE CODE FUNCTION////////////////////////////////////////////////
function generateEmployeeCode($conn) {
  $sql = "SELECT employee_id FROM employees ORDER BY employee_id DESC LIMIT 1";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $last_code = $row['employee_id'];
      $number = intval(substr($last_code, 2)) + 1;
      return 'EM' . str_pad($number, 3, '0', STR_PAD_LEFT);
  } else {
      return 'EM001';
  }
}

// Handle AJAX request for generating employee code
if (isset($_GET['generate_employee_code'])) {
echo generateEmployeeCode($conn);
exit; // Stop further execution since this is an AJAX request
}

////////////////////////////////////////////// CLOCK IN/OUT FUNCTION////////////////////////////////////////////////
// CLOCK IN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clock_in_employee_id'])) {
    $employee_id = $conn->real_escape_string($_POST['clock_in_employee_id']);
    $date = date('Y-m-d');
    $time = date('H:i:s');

    // Check if attendance record exists for today
    $check_sql = "SELECT * FROM attendance WHERE employee_id = '$employee_id' AND date = '$date'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Update clock_in if not already set
        $row = $check_result->fetch_assoc();
        if (empty($row['clock_in'])) {
            $conn->query("UPDATE attendance SET clock_in = '$time', status = 'Present' WHERE employee_id = '$employee_id' AND date = '$date'");
        }
    } else {
        // Insert new record with clock_in
        $conn->query("INSERT INTO attendance (employee_id, date, clock_in, status) VALUES ('$employee_id', '$date', '$time', 'Present')");
    }
    exit;
}

// CLOCK OUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clock_out_employee_id'])) {
    $employee_id = $conn->real_escape_string($_POST['clock_out_employee_id']);
    $date = date('Y-m-d');
    $time = date('H:i:s');

    // Update clock_out for today's attendance
    $conn->query("UPDATE attendance SET clock_out = '$time' WHERE employee_id = '$employee_id' AND date = '$date' AND clock_in IS NOT NULL AND (clock_out IS NULL OR clock_out = '')");
    exit;
}

//////////////////////////////////////////////// EDIT EMPLOYEE FUNCTION////////////////////////////////////////////////
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_employee'])) {
  $employee_id = $conn->real_escape_string($_POST['employee_id']);
  $first_name = $conn->real_escape_string($_POST['first_name']);
  $middle_name = $conn->real_escape_string($_POST['middle_name']);
  $last_name = $conn->real_escape_string($_POST['last_name']);
  $department = $conn->real_escape_string($_POST['department']); // CHANGED
  $contact = $conn->real_escape_string($_POST['contact']);
  $address = $conn->real_escape_string(
    $_POST['region_name'] . ', ' .
    $_POST['province_name'] . ', ' .
    $_POST['city_name'] . ', ' .
    $_POST['barangay_name'] . ', ' .
    $_POST['address_details']
  );
  $age = intval($_POST['age']);
  $sex = $conn->real_escape_string($_POST['sex']);
  $salary = floatval($_POST['salary']);

  // Handle picture upload
  $picture_sql = "";
  if (isset($_FILES['edit_employee_picture']) && $_FILES['edit_employee_picture']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $ext = pathinfo($_FILES['edit_employee_picture']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('emp_') . '.' . $ext;
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($_FILES['edit_employee_picture']['tmp_name'], $target_file)) {
      $picture_path = $conn->real_escape_string($target_file);
      $picture_sql = ", emp_picture = '$picture_path'";
    }
  }

  $sql = "UPDATE employees SET 
              first_name = '$first_name',
              middle_name = '$middle_name',
              last_name = '$last_name',
              department = '$department', // CHANGED
              contact = '$contact',
              address = '$address',
              age = '$age',
              sex = '$sex',
              salary = '$salary'
              $picture_sql
          WHERE employee_id = '$employee_id'";

  if ($conn->query($sql) === TRUE) {
      echo "success";
      exit;
  } else {
      http_response_code(500);
      echo "Error updating employee: " . $conn->error;
      exit;
  }
}

//////////////////////////////////////////////// ADD EMPLOYEE FUNCTION////////////////////////////////////////////////
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name']) && !isset($_POST['edit_employee'])) {
  $employee_id = generateEmployeeCode($conn);
  $first_name = $conn->real_escape_string($_POST['first_name']);
  $middle_name = $conn->real_escape_string($_POST['middle_name']);
  $last_name = $conn->real_escape_string($_POST['last_name']);
  $department = $conn->real_escape_string($_POST['department']); // CHANGED
  $contact = $conn->real_escape_string($_POST['contact']);
  $address = $conn->real_escape_string(
      $_POST['region_name'] . ', ' .
      $_POST['province_name'] . ', ' .
      $_POST['city_name'] . ', ' .
      $_POST['barangay_name'] . ', ' .
      $_POST['address_details']
  );
  $age = intval($_POST['age']);
  $sex = $conn->real_escape_string($_POST['sex']);
  $salary = floatval($_POST['salary']);
  // Handle picture upload
  $picture_path = '';
  if (isset($_FILES['employee_picture']) && $_FILES['employee_picture']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $ext = pathinfo($_FILES['employee_picture']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('emp_') . '.' . $ext;
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($_FILES['employee_picture']['tmp_name'], $target_file)) {
        $picture_path = $conn->real_escape_string($target_file);
    }
  }

  $sql = "INSERT INTO employees (employee_id, first_name, middle_name, last_name, department, contact, address, age, sex, salary, emp_picture) 
      VALUES ('$employee_id', '$first_name', '$middle_name', '$last_name', '$department', '$contact', '$address', '$age', '$sex', '$salary', '$picture_path')";

  if ($conn->query($sql) === TRUE) {
      echo "success";
      exit;
  } else {
      http_response_code(500);
      echo "Error adding employee: " . $conn->error;
      exit;
  }
}


////////////////////////////////////////////////DELETE EMPLOYEE FUNCTION////////////////////////////////////////////////
// Delete Employee
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['employee_id']) && isset($_POST['delete_employee'])) {
  $employee_id = $conn->real_escape_string($_POST['employee_id']);

  // Delete from attendance, salary, then employees (to maintain referential integrity)
  $conn->query("DELETE FROM attendance WHERE employee_id = '$employee_id'");
  $conn->query("DELETE FROM salary WHERE employee_id = '$employee_id'");

  $sql = "DELETE FROM employees WHERE employee_id = '$employee_id'";
  if ($conn->query($sql)) {
    $_SESSION['success_message'] = "Employee deleted successfully!";
    echo "success";
    exit;
  } else {
    http_response_code(500);
    echo "Error deleting employee: " . $conn->error;
    exit;
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

//////////////////////////////////////////////// ATTENDANCE STATUS UPDATE FUNCTION////////////////////////////////////////////////
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'], $_POST['employee_id'], $_POST['date'])) {
    $employee_id = $conn->real_escape_string($_POST['employee_id']);
    $date = $conn->real_escape_string($_POST['date']);
    $status = $conn->real_escape_string($_POST['status']);

    $update_sql = "UPDATE attendance SET status = '$status' WHERE employee_id = '$employee_id' AND date = '$date'";
    if (!$conn->query($update_sql)) {
        error_log("Error updating attendance: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CDMS - Employee Management</title>

    <!-- website icon -->
    <link rel="icon" type="image/x-icon" href="/cdms/img/favicon_io/favicon.ico">
    
    <link rel="stylesheet" href="css_emp_index.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Linking Google Fonts for Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="../sidebar/sidebar.css">
  </head>

  <body>
    <!-- SIDEBAR SAKA HEADER-->
    <?php include '../sidebar/sidebar.php'; ?>
    <div class="main-content">
      <!-- MAIN CONTAINER -->
      <div class="main-container mt-4 p-3 bg-white rounded shadow">
        <div class="row mb-4">
          <div class="col-12">
            <h1 class="sub-header text-success text-center">Employee Management</h1>
          </div>
        </div>

        <div class="row mb-3">
          <!-- Info Box 1 -->
          <div class="col-md-4 mb-2">
              <div class="d-flex justify-content-between align-items-center p-3 rounded bg-success text-white shadow-sm">
                  <div>
                      <div class="fw-bold fs-5">Total Employees</div>
                      <div class="fs-6">120</div>
                  </div>
                  <span class="ms-2">
                      <i class="fas fa-users fa-2x"></i>
                  </span>
              </div>
          </div>
          <!-- Info Box 2 -->
          <div class="col-md-4 mb-2">
              <div class="d-flex justify-content-between align-items-center p-3 rounded bg-primary text-white shadow-sm">
                  <div>
                      <div class="fw-bold fs-5">Present Today</div>
                      <div class="fs-6">98</div>
                  </div>
                  <span class="ms-2">
                      <i class="fas fa-user-check fa-2x"></i>
                  </span>
              </div>
          </div>
          <!-- Info Box 3 -->
          <div class="col-md-4 mb-2">
              <div class="d-flex justify-content-between align-items-center p-3 rounded bg-warning text-dark shadow-sm">
                  <div>
                      <div class="fw-bold fs-5">On Leave</div>
                      <div class="fs-6">5</div>
                  </div>
                  <span class="ms-2">
                      <i class="fas fa-user-clock fa-2x"></i>
                  </span>
              </div>
          </div>
        </div>

        <!-- Search Bar and Buttons in the same column -->
        <div class="row mb-3 align-items-center">  
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                    <button onclick="openAddEmployeeModal()" id="addEmployeeButton" class="btn btn-success ms-md-3">
                        <i class="fas fa-plus"></i> Add Employee
                    </button>
                    <button id="generateReportButton" class="btn btn-info ms-md-2">
                        <i class="fas fa-file-alt"></i> Generate Report
                    </button>

                    <div class="input-group w-100 w-md-50">
                      <span class="input-group-text bg-light">
                          <i class="fas fa-search"></i>
                      </span>
                      <input type="text" id="searchBar" class="form-control" placeholder="Search employees...">
                  </div>                 
                </div>
            </div>
        </div>
      </div>

      <!-- Tables Section: Bootstrap Tabs and Tables -->
      <div class="tabletab-container">
        <!-- Tabs -->
        <ul class="nav nav-tabs mb-1" id="employeeTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab" aria-controls="attendance" aria-selected="true">
              Employees Attendance
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="salary-tab" data-bs-toggle="tab" data-bs-target="#salary" type="button" role="tab" aria-controls="salary" aria-selected="false">
              Employees Salary
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="false">
              Employee Information
            </button>
          </li>
        </ul>

        <div class="tab-content" id="employeeTabsContent">
          <!-- Attendance Tab -->
          <div class="tab-pane fade show active" id="attendance" role="tabpanel" aria-labelledby="attendance-tab">
            <div class="card mb-4">
              <div class="card-header bg-primary text-white">
                <h2 class="mb-0 fs-5">Attendance</h2>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $attendance_sql = "
                        SELECT 
                          attendance.employee_id, 
                          employees.first_name, 
                          employees.middle_name, 
                          employees.last_name, 
                          employees.department, 
                          attendance.date, 
                          attendance.status,
                          attendance.clock_in,
                          attendance.clock_out
                        FROM attendance
                        JOIN employees ON attendance.employee_id = employees.employee_id
                      ";
                      $attendance_result = $conn->query($attendance_sql);

                      if ($attendance_result->num_rows > 0) {
                        while ($row = $attendance_result->fetch_assoc()) {
                          $employee_name = trim("{$row['first_name']} {$row['middle_name']} {$row['last_name']}");
                          echo "<tr class='table-row'>
                                  <td>{$row['employee_id']}</td>
                                  <td>{$employee_name}</td>
                                  <td>{$row['department']}</td>
                                  <td>" . formatDate($row['date']) . "</td>
                                  <td>
                                    <div>
                                      <strong>In:</strong> " . ($row['clock_in'] ? formatDate($row['clock_in']) : '--:--') . "
                                      <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='clock_in_employee_id' value='{$row['employee_id']}'>
                                        <button type='submit' class='btn btn-success btn-sm ms-1' " . ($row['clock_in'] ? 'disabled' : '') . ">Clock In</button>
                                      </form>
                                    </div>
                                  </td>
                                  <td>
                                    <div class='mt-1'>
                                      <strong>Out:</strong> " . ($row['clock_out'] ? formatDate($row['clock_out']) : '--:--') . "
                                      <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='clock_out_employee_id' value='{$row['employee_id']}'>
                                        <button type='submit' class='btn btn-danger btn-sm ms-1' " . (!$row['clock_in'] || $row['clock_out'] ? 'disabled' : '') . ">Clock Out</button>
                                      </form>
                                    </div>
                                  </td>
                                  <td>
                                    <form action='' method='POST' class='status-form'>
                                      <input type='hidden' name='employee_id' value='{$row['employee_id']}' />
                                      <input type='hidden' name='date' value='{$row['date']}' />
                                      <select name='status' onchange='this.form.submit()'>
                                        <option value='Present' " . ($row['status'] === 'Present' ? 'selected' : '') . ">Present</option>
                                        <option value='Absent' " . ($row['status'] === 'Absent' ? 'selected' : '') . ">Absent</option>
                                        <option value='On Leave' " . ($row['status'] === 'On Leave' ? 'selected' : '') . ">On Leave</option>
                                      </select>
                                    </form>
                                  </td>
                                </tr>";
                        }
                      } else {
                        echo "<tr><td colspan='7' class='no-records'>No attendance records found.</td></tr>";
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Salary Tab -->
          <div class="tab-pane fade" id="salary" role="tabpanel" aria-labelledby="salary-tab">
            <div class="card mb-4">
              <div class="card-header bg-warning text-dark">
                <h2 class="mb-0 fs-5">Salary</h2>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Employee ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Department</th>
                        <th>Month</th>
                        <th>Amount/Salary</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $salary_sql = "
                        SELECT 
                          salary.employee_id, 
                          employees.first_name, 
                          employees.last_name, 
                          employees.department, 
                          salary.month, 
                          salary.amount 
                        FROM salary
                        JOIN employees ON salary.employee_id = employees.employee_id
                      ";
                      $salary_result = $conn->query($salary_sql);

                      if ($salary_result->num_rows > 0) {
                        while ($row = $salary_result->fetch_assoc()) {
                          echo "<tr class='table-row'>
                                  <td>{$row['employee_id']}</td>
                                  <td>{$row['first_name']}</td>
                                  <td>{$row['last_name']}</td>
                                  <td>{$row['department']}</td>
                                  <td>" . formatDate($row['month']) . "</td>
                                  <td>{$row['amount']}</td>
                                </tr>";
                        }
                      } else {
                        echo "<tr><td colspan='7' class='no-records'>No salary records found.</td></tr>";
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Information Tab -->
          <div class="tab-pane fade" id="info" role="tabpanel" aria-labelledby="info-tab">
            <div class="card mb-4">
              <div class="card-header bg-info text-white">
                <h2 class="mb-0 fs-5">Employee Information</h2>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Employee ID</th>
                        <th>Photo</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Department</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Age</th>
                        <th>Sex</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $info_sql = "
                        SELECT 
                          employee_id, 
                          first_name, 
                          middle_name, 
                          last_name, 
                          department, 
                          contact, 
                          address, 
                          age, 
                          sex,
                          emp_picture
                        FROM employees
                      ";
                      $info_result = $conn->query($info_sql);

                      if ($info_result->num_rows > 0) {
                        while ($row = $info_result->fetch_assoc()) {
                          echo "<tr class='table-row'>
                                  <td>{$row['employee_id']}</td>
                                  <td><img src='{$row['emp_picture']}' alt='Employee Picture' class='img-thumbnail' style='max-width: 100px;'></td>
                                  <td>{$row['first_name']}</td>
                                  <td>{$row['middle_name']}</td>
                                  <td>{$row['last_name']}</td>
                                  <td>{$row['department']}</td>
                                  <td>{$row['contact']}</td>
                                  <td>{$row['address']}</td>
                                  <td>{$row['age']}</td>
                                  <td>{$row['sex']}</td>
                                  <td>
                                    <button class='btn btn-warning btn-sm' onclick=\"openEditEmployeeModal('{$row['employee_id']}')\">Edit</button>
                                    <button class='btn btn-danger btn-sm' onclick=\"openDeleteEmployeeModal('{$row['employee_id']}')\">Delete</button>
                                  </td>
                                </tr>";
                        }
                      } else {
                        echo "<tr><td colspan='10' class='no-records'>No employee information found.</td></tr>";
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Add Employee Modal -->
      <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="addEmployeeForm" method="POST" enctype="multipart/form-data">
                <div class="row mb-2">
                  <div class="col-md-12">
                    <label class="form-label fw-bold">Employee Profile</label>
                  </div>
                </div>
                <!-- Employee ID and Picture -->
                <div class="row mb-3">
                  <div class="col-md-2">
                    <label for="employee_id_preview" class="form-label">Employee ID</label>
                    <input type="text" id="employee_id_preview" name="employee_id" class="form-control" placeholder="auto-generated" readonly style="background-color: #e9ecef; color: #6c757d; pointer-events: none;">
                  </div>
                  <div class="col-md-6">
                    <label for="employee_picture" class="form-label">Employee Picture</label>
                    <input type="file" id="employee_picture" name="employee_picture" class="form-control" accept="image/*">
                  </div>
                  <img id="employee_picture_preview" src="#" alt="Preview" class="img-thumbnail mt-2" style="display:none; max-width:120px; max-height:120px;">
                </div>
                <!-- Name -->
                <div class="row mb-3">
                  <div class="col-md-5">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" id="first_name" name="first_name" class="form-control" placeholder="First Name" required>
                  </div>
                  <div class="col-md-2">
                    <label for="middle_name" class="form-label">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" class="form-control" placeholder="Middle Name">
                  </div>
                  <div class="col-md-5">
                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Last Name" required>
                  </div>
                </div>
                <!-- Age, sex and department -->  
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label for="age" class="form-label">Age <span class="text-danger">*</span></label>
                    <input type="number" id="age" name="age" class="form-control" min="0" placeholder="Age">
                  </div>
                  <div class="col-md-3">
                    <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
                    <select id="sex" name="sex" class="form-select" required>
                      <option value="" disabled selected>Select Sex</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                    <input type="text" id="department" name="department" class="form-control" placeholder="Department" required>
                  </div>
                </div>
                <!-- Contact/ salary -->
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="contact" class="form-label">Contact <span class="text-danger">*</span></label>
                    <input type="text" id="contact" name="contact" class="form-control" placeholder="Contact" required>
                  </div>
                  <div class="col-md-6">
                    <label for="salary" class="form-label"> Initial Salary <span class="text-danger">*</span></label>
                    <input type="number" id="salary" name="salary" class="form-control" step="0.01" min="0" required>
                  </div>
                </div>

                <!-- Address APA (Cascading Dropdowns) -->
                <div class="row mb-2">
                  <div class="col-md-12">
                    <label class="form-label fw-bold">Address</label>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label for="region" class="form-label">Region <span class="text-danger">*</span></label>
                    <select id="region" name="region" class="form-select" required></select>
                  </div>
                  <div class="col-md-3">
                    <label for="province" class="form-label">Province <span class="text-danger">*</span></label>
                    <select id="province" name="province" class="form-select" required></select>
                  </div>
                  <div class="col-md-3">
                    <label for="city" class="form-label">City/Municipality <span class="text-danger">*</span></label>
                    <select id="city" name="city" class="form-select" required></select>
                  </div>
                  <div class="col-md-3">
                    <label for="barangay" class="form-label">Barangay <span class="text-danger">*</span></label>
                    <select id="barangay" name="barangay" class="form-select" required></select>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-12">
                    <label for="address_details" class="form-label">Street/Details</label>
                    <input type="text" id="address_details" name="address_details" class="form-control" placeholder="House No., Street, etc.">
                  </div>
                </div>

                <!-- Hidden fields for address names -->
                <input type="hidden" id="region_name" name="region_name">
                <input type="hidden" id="province_name" name="province_name">
                <input type="hidden" id="city_name" name="city_name">
                <input type="hidden" id="barangay_name" name="barangay_name">
              </form>
            </div>
            <div class="modal-footer">
              <button type="submit" form="addEmployeeForm" class="btn btn-primary">Add Employee</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
          </div>
        </div>
      </div>

      <!-- EDIT EMPLOYEE MODAL -->
      <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="editEmployeeForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_employee" value="1">
                <!-- Employee id and picture -->
                <div class="row mb-3">
                  <div class="col-md-2">
                    <label for="edit_employee_id" class="form-label">Employee ID</label>
                    <input type="text" id="edit_employee_id" name="employee_id" class="form-control" readonly>
                  </div>
                  <div class="col-md-6">
                    <label for="edit_employee_picture" class="form-label">Employee Picture</label>
                    <input type="file" id="edit_employee_picture" name="edit_employee_picture" class="form-control" accept="image/*">
                    <img id="edit_employee_picture_preview" src="#" alt="Preview" class="img-thumbnail mt-2" style="display:none; max-width:120px; max-height:120px;">
                  </div>
                </div>
                <!-- Name fields -->
                <div class="row mb-3">
                  <div class="col-md-5">
                    <label for="edit_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                  </div>
                  <div class="col-md-2">
                    <label for="edit_middle_name" class="form-label">Middle Name</label>
                    <input type="text" id="edit_middle_name" name="middle_name" class="form-control">
                  </div>
                  <div class="col-md-5">
                    <label for="edit_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" id="edit_last_name" name="last_name" class="form-control" required>
                  </div>
                </div>
                <!-- age and sex -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label for="edit_age" class="form-label">Age <span class="text-danger">*</span></label>
                    <input type="number" id="edit_age" name="age" class="form-control" min="0">
                  </div>
                  <div class="col-md-3">
                    <label for="edit_sex" class="form-label">Sex <span class="text-danger">*</span></label>
                    <select id="edit_sex" name="sex" class="form-select" required>
                      <option value="" disabled>Select Sex</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>
                  <!-- department, contact, salary -->
                  <div class="col-md-6">
                    <label for="edit_department" class="form-label">Department <span class="text-danger">*</span></label>
                    <input type="text" id="edit_department" name="department" class="form-control" required>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="edit_contact" class="form-label">Contact <span class="text-danger">*</span></label>
                    <input type="text" id="edit_contact" name="contact" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                    <label for="edit_salary" class="form-label">Salary <span class="text-danger">*</span></label>
                    <input type="number" id="edit_salary" name="salary" class="form-control" step="0.01" min="0" required>
                  </div>
                </div>

                <!-- Address APA (Cascading Dropdowns) -->
                <div class="row mb-2">
                  <div class="col-md-12">
                    <label class="form-label fw-bold">Address</label>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label for="edit_region" class="form-label">Region <span class="text-danger">*</span></label>
                    <select id="edit_region" name="region" class="form-select" required></select>
                  </div>
                  <div class="col-md-3">
                    <label for="edit_province" class="form-label">Province <span class="text-danger">*</span></label>
                    <select id="edit_province" name="province" class="form-select" required></select>
                  </div>
                  <div class="col-md-3">
                    <label for="edit_city" class="form-label">City/Municipality <span class="text-danger">*</span></label>
                    <select id="edit_city" name="city" class="form-select" required></select>
                  </div>
                  <div class="col-md-3">
                    <label for="edit_barangay" class="form-label">Barangay <span class="text-danger">*</span></label>
                    <select id="edit_barangay" name="barangay" class="form-select" required></select>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-12">
                    <label for="edit_address_details" class="form-label">Street/Details</label>
                    <input type="text" id="edit_address_details" name="address_details" class="form-control" placeholder="House No., Street, etc.">
                  </div>
                </div>
                <!-- Hidden fields for address names -->
                <input type="hidden" id="edit_region_name" name="region_name">
                <input type="hidden" id="edit_province_name" name="province_name">
                <input type="hidden" id="edit_city_name" name="city_name">
                <input type="hidden" id="edit_barangay_name" name="barangay_name">
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" onclick="editEmployee()">Save Changes</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
          </div>
        </div>
      </div>

      <!-- DELETE EMPLOYEE MODAL -->
      <div class="modal fade" id="deleteEmployeeModal" tabindex="-1" aria-labelledby="deleteEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deleteEmployeeModalLabel">Delete Employee</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>Are you sure you want to delete this employee?</p>
              <div class="row">
                <div class="col-4">
                  <label for="employeeIdToDelete" class="form-label">Employee ID</label>
                  <input type="text" id="employeeIdToDelete" class="form-control" readonly>
                </div>
                <div class="col-8">
                  <label for="employeeNameToDelete" class="form-label">Employee Name</label>
                  <input type="text" id="employeeNameToDelete" class="form-control" readonly>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger" onclick="deleteEmployeeConfirmed()">Delete</button>
            </div>
          </div>
        </div>
      </div>

      <!-- SUCCESS MODAL -->
      <div class="modal fade" id="employeeSuccessModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="successModalLabel">Employee Added</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>Employee has been added.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab Script -->
    <script src="../sidebar/sidebar.js"></script>
    <script src="js_emp_index.js"></script>
  </body>
</html>