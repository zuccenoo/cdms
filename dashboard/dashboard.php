<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>CDMS - Dashboard</title>

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
    <link rel="stylesheet" href="dashboard.css">
    <link rel="icon" type="image/x-icon" href="/cdms/img/favicon_io/favicon.ico">
    <link rel="stylesheet" href="/cdms/sidebar/sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <!-- SIDEBAR SAKA HEADER-->
    <?php include '../sidebar/sidebar.php'; ?>

    <div class="main-content">
      <h1 class="dashboard-title">Dashboard</h1>
      <div class="dashboard">
        <!-- Top Cards -->
        <div class="top-boxes">
          <div class="card card-green1">
            <div class="card-content">
              <div class="card-label">Villa Reservation</div>
              <div class="card-number">15</div>
            </div>
          </div>
          <div class="card card-green2">
            <div class="card-content">
              <div class="card-label">Pavilion Reservation</div>
              <div class="card-number">8</div>
            </div>
          </div>
          <div class="card card-green3">
            <div class="card-content">
              <div class="card-label">Exclusive Reservation</div>
              <div class="card-number">5</div>
            </div>
          </div>
          <div class="card card-green4">
            <div class="card-content">
              <div class="card-label">Total Reservation</div>
              <div class="card-number">28</div>
            </div>
          </div>
        </div>
      </div>
      <br>

      <div class="dashboard">
        <!-- Top Cards -->
        <div class="top-boxes">
          <div class="card card-green5">
            <div class="card-content">
              <div class="card-label">Total Guest Accounts</div>
              <div class="card-number">100</div>
            </div>
          </div>
          <div class="card card-green6">
            <div class="card-content">
              <div class="card-label">Total System Accounts</div>
              <div class="card-number">8</div>
            </div>
          </div>
          <div class="card card-green7">
            <div class="card-content">
              <div class="card-label">Monthly Income</div>
              <div class="card-number">500,000</div>
            </div>
          </div>
          <div class="card card-green8">
            <div class="card-content">
              <div class="card-label">Yearly Income</div>
              <div class="card-number">2,000,000</div>
            </div>
          </div>
        </div>
      </div>
      <br>

      <!-- Bottom Section -->
      <div class="bottom-boxes">
        <!-- Calendar -->
        <div class="card calendar">
          <h3>Booked Calendar</h3>
          <div class="calendar-header">
            <button id="prevMonth">&lt;</button>
            <span id="monthYear"></span>
            <button id="nextMonth">&gt;</button>
          </div>
          <div class="calendar-grid" id="calendarGrid"></div>
        </div>

        <!-- Line Chart -->
        <div class="card chart-box">
          <h3>Income Trend Line</h3>
          <br>
          <canvas id="lineChart"></canvas>
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
    <script src="dashboard.js"></script>
    <script src="/cdms/sidebar/sidebar.js"></script>
  </body>
</html>
