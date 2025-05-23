<!-- TimeSheet.php -->
  <?php
  require('include/top.php');
  require('include/side-navbar.php');
  require('include/right-side-navbar.php');
  ?>

  <style type="text/css">
    #calendar {
      min-height: 500px;
      background-color: #fff;
      border-radius: 10px;
    }
  </style>

  <div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
    <?php require('include/header.php'); ?>

    <div class="body flex-grow-1 px-3">
      <div class="container-lg">
        <div class="fs-2 fw-semibold">Attendance</div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><span>Home</span></li>
            <li class="breadcrumb-item active"><span>Attendance</span></li>
          </ol>
        </nav>

        <div class="row">
          <div class="col-lg-12 text-center">
            <div class="card mb-4">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                  <div>
                    <h4 class="fw-semibold mb-1">Timesheet</h4>
                    <small class="text-muted">Hi, <?php echo isset($_SESSION['USERNAME']) ? $_SESSION['USERNAME'] : ''; ?></small>
                  </div>
                  <button id="exportExcel" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Export to Excel
                  </button>
                </div>
  <!-- Month & Year Dropdown -->
  <div class="row">
    <div class="col-md-12 mb-3 mt-3 d-flex align-items-center gap-3 justify-content-center">
      <?php
      $currentMonth = date('n');
      $currentYear = date('Y');
      ?>
      <select id="month" class="form-select w-auto">
        <?php
        for ($m = 1; $m <= 12; $m++) {
          $monthName = date("F", mktime(0, 0, 0, $m, 10));
          $selected = ($m == $currentMonth) ? 'selected' : '';
          echo "<option value='$m' $selected>$monthName</option>";
        }
        ?>
      </select>

      <select id="year" class="form-select w-auto">
        <?php
        for ($y = $currentYear - 5; $y <= $currentYear; $y++) {
          $selected = ($y == $currentYear) ? 'selected' : '';
          echo "<option value='$y' $selected>$y</option>";
        }
        ?>
      </select>
    </div>
  </div>

  <!-- Calendar -->
  <div id="calendar"></div>

  <!-- <div class="mb-2">
  <span class="badge bg-success">Present</span>
  <span class="badge bg-warning text-dark">Late</span>
  <span class="badge bg-danger">Absent</span>
  </div> -->

  </div>
  </div>
  </div>
  </div>

  </div>
  </div>

  <?php require('include/footer.php'); ?>
  </div>

  <script>
  var calendar; // Global calendar variable

  $(document).ready(function () {
    function refreshCalendar(month, year) {
      if (calendar) {
        calendar.destroy();
      }

      var calendarEl = document.getElementById('calendar');
      calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: `${year}-${String(month).padStart(2, '0')}-01`,
        headerToolbar: {
          left: '',
          center: 'title',
          right: ''
        },
        events: `function/fetch_attendance_calendar.php?month=${month}&year=${year}`,
        eventContent: function (arg) {
          const props = arg.event.extendedProps;
          const CheckInlocation = props.CheckInlocation ? `<br><a style="color: white; text-decoration: none;" href="${props.CheckInlocation}" target="_blank">📍 Check-In Location</a>` : '';
          const CheckOutlocation = props.CheckOutlocation ? `<br><a style="color: white; text-decoration: none;" href="${props.CheckOutlocation}" target="_blank">📍 Check-Out Location</a>` : '';
          return {
            html: `
                <div style="font-size: 12px">
                <b>${arg.event.title}</b><br>
                ⏱ In: ${props.checkIn || '--'}
                ${CheckInlocation}<br>
                ⏱ Out: ${props.checkOut || '--'}
                ${CheckOutlocation}<br>
                🕒 Hours: ${props.totalHours || '--'}
                </div>
              `
            };
          }
        });

      calendar.render();
    }

    $("#exportExcel").click(function () {
      var month = $("#month").val();
      var year = $("#year").val();
      window.location.href = `function/export_attendance_excel.php?month=${month}&year=${year}`;
    });

  // Trigger both on load
    refreshCalendar($("#month").val(), $("#year").val());

  // On dropdown change
    $("#month, #year").change(function () {
      const month = $("#month").val();
      const year = $("#year").val();
      refreshCalendar(month, year);
    });
  });
  </script>