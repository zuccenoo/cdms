<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CDMS - Dashboard</title>

  <link rel="stylesheet" href="dash.css" />
  <link rel="stylesheet" href="../sidebar/sidebar.css"/>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <!-- SIDEBAR SAKA HEADER-->
  <?php include '../sidebar/sidebar.php'; ?>

  <div>
    <h1> Dashboard </h1><br>
</div>
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

  <script src="/cdms/sidebar/sidebar.js"></script>
  <script src="dash.js"></script>
</body>
</html>
