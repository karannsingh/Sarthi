<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

if (isset($_SESSION['USEROID'])) {
    $employee_id = $_SESSION['USEROID'];

    // Default to current month & year
    $currentMonth = date('m');
    $currentYear = date('Y');

    $sql = "SELECT `id`, `check_in_time`, `check_out_time`, `date`, `total_hours`, `status` 
            FROM `employee_attendance` 
            WHERE `employee_id` = ? 
            AND MONTH(`date`) = ? 
            AND YEAR(`date`) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $employee_id, $currentMonth, $currentYear);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

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
              <div class="card-title fs-4 fw-semibold">Timesheet</div>
              <div class="card-subtitle text-disabled">
                <?php echo isset($_SESSION['USERNAME']) ? 'Hi, ' . $_SESSION['USERNAME'] : ''; ?>
              </div>
              <div class="row">
                <div class="col-md-12 mb-3 mt-3 d-flex align-items-center gap-3 justify-content-center">
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
                    $currentYear = date('Y');
                    for ($y = $currentYear - 5; $y <= $currentYear; $y++) {
                        $selected = ($y == $currentYear) ? 'selected' : '';
                        echo "<option value='$y' $selected>$y</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>

              <!-- Attendance Table -->
              <table class="table table-bordered">
                <thead class="table-dark">
                  <tr>
                    <th>Date</th>
                    <th>Check-in Time</th>
                    <th>Check-out Time</th>
                    <th>Total Hours</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="attendanceData">
                  <?php if ($result->num_rows > 0) { ?>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                      <tr>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo !empty($row['check_in_time']) ? $row['check_in_time'] : '--:--:--'; ?></td>
                        <td><?php echo !empty($row['check_out_time']) ? $row['check_out_time'] : '--:--:--'; ?></td>
                        <td><?php echo !empty($row['total_hours']) ? $row['total_hours'] : '--:--:--'; ?></td>
                        <td><?php echo $row['status']; ?></td>
                      </tr>
                    <?php } ?>
                  <?php } else { ?>
                    <tr>
                      <td colspan="5" class="text-center fw-semibold">No record found</td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <?php require('include/footer.php'); ?>
</div>

<script>
$(document).ready(function() {
    function fetchAttendance() {
        var month = $("#month").val();
        var year = $("#year").val();

        $.ajax({
            url: "function/fetch_attendance.php",
            type: "POST",
            data: { month: month, year: year },
            success: function(response) {
                $("#attendanceData").html(response);
            }
        });
    }

    // Trigger AJAX on dropdown change
    $("#month, #year").change(function() {
        fetchAttendance();
    });

    // Load attendance on page load
    fetchAttendance();
});
</script>