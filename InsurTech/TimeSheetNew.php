<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');
?>

<style type="text/css">
  .calendar-container {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  }
  
  .calendar-day {
    min-height: 90px;
    border: 1px solid #e9ecef;
    position: relative;
    cursor: pointer;
    transition: all 0.2s;
  }
  
  .calendar-day:hover {
    background-color: #f8f9fa;
  }
  
  .calendar-day.today {
    background-color: #e8f4ff;
  }
  
  .calendar-day.weekend {
    background-color: #f8f9fa;
  }
  
  .calendar-day.inactive {
    background-color: #f8f9fa;
    opacity: 0.5;
  }
  
  .calendar-day .date-number {
    position: absolute;
    top: 5px;
    right: 8px;
    font-size: 12px;
    color: #6c757d;
  }
  
  .calendar-day .status-badge {
    margin-top: 25px;
    display: flex;
    justify-content: center;
  }
  
  .calendar-day .status-badge .badge {
    padding: 5px 10px;
    font-weight: normal;
  }
  
  .details-sidebar {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    padding: 20px;
    max-height: 650px;
    overflow-y: auto;
  }
  
  .details-sidebar hr {
    margin: 10px 0;
  }
  
  .details-sidebar .close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    cursor: pointer;
    font-size: 18px;
  }
  
  .action-buttons {
    margin-top: 15px;
    display: flex;
    gap: 10px;
  }
  
  #calendar-header {
    background-color: #f8f9fa;
    border-radius: 10px 10px 0 0;
    padding: 15px;
  }
  
  .legend-item {
    display: inline-flex;
    align-items: center;
    margin-right: 15px;
    font-size: 0.85rem;
  }
  
  .legend-color {
    width: 12px;
    height: 12px;
    border-radius: 3px;
    margin-right: 5px;
    display: inline-block;
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
      <!-- Main Content -->
      <div class="row">
        <div class="col-lg-12">
          <div class="card mb-4">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <div>
                  <h4 class="fw-semibold mb-1">Timesheet</h4>
                  <small class="text-muted">Hi, <?php echo isset($_SESSION['USERNAME']) ? $_SESSION['USERNAME'] : ''; ?></small>
                </div>
                <div class="d-flex gap-2">
                  <button id="regularizeBtn" class="btn btn-primary">
                    <i class="bi bi-pencil-square"></i> Regularize Attendance
                  </button>
                  <button id="exportExcel" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Export to Excel
                  </button>
                </div>
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

                  <button id="todayBtn" class="btn btn-outline-secondary">Today</button>
                </div>
              </div>

              <!-- Custom Calendar -->
              <div class="row">
                <div class="col-lg-9">
                  <div class="calendar-container">
                    <!-- Calendar Header -->
                    <div id="calendar-header" class="d-flex justify-content-between align-items-center p-3">
                      <div>
                        <h5 class="m-0" id="calendar-title"></h5>
                      </div>
                      <div class="d-flex gap-2">
                        <button id="prev-month" class="btn btn-sm btn-outline-secondary">
                          <i class="bi bi-chevron-left"></i>
                        </button>
                        <button id="next-month" class="btn btn-sm btn-outline-secondary">
                          <i class="bi bi-chevron-right"></i>
                        </button>
                      </div>
                    </div>

                    <!-- Calendar Days Header -->
                    <div class="row border-bottom">
                      <div class="col p-2 text-center"><strong>Sun</strong></div>
                      <div class="col p-2 text-center"><strong>Mon</strong></div>
                      <div class="col p-2 text-center"><strong>Tue</strong></div>
                      <div class="col p-2 text-center"><strong>Wed</strong></div>
                      <div class="col p-2 text-center"><strong>Thu</strong></div>
                      <div class="col p-2 text-center"><strong>Fri</strong></div>
                      <div class="col p-2 text-center"><strong>Sat</strong></div>
                    </div>

                    <!-- Calendar Body -->
                    <div id="calendar-body"></div>

                    <!-- Legend -->
                    <div class="p-3 border-top">
                      <div class="legend-item">
                        <span class="legend-color" style="background-color: #28a745;"></span>
                        <span>Present</span>
                      </div>
                      <div class="legend-item">
                        <span class="legend-color" style="background-color: #fd7e14;"></span>
                        <span>Late/Half-day</span>
                      </div>
                      <div class="legend-item">
                        <span class="legend-color" style="background-color: #dc3545;"></span>
                        <span>Absent</span>
                      </div>
                      <div class="legend-item">
                        <span class="legend-color" style="background-color: #007bff;"></span>
                        <span>Holiday</span>
                      </div>
                      <div class="legend-item">
                        <span class="legend-color" style="background-color: #6610f2;"></span>
                        <span>Leave</span>
                      </div>
                      <div class="legend-item">
                        <span class="legend-color" style="background-color: #adb5bd;"></span>
                        <span>Pending</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Details Sidebar -->
                <div class="col-lg-3" id="details-sidebar-container">
                  <div class="details-sidebar d-none" id="details-sidebar">
                    <div class="position-relative">
                      <h5 id="selected-date-heading" class="mb-3"></h5>
                      <i class="bi bi-x-lg close-btn" id="close-sidebar"></i>
                    </div>
                    <hr>
                    <div id="attendance-details">
                      <!-- Details will be populated via JavaScript -->
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Regularization Modal -->
  <div class="modal fade" id="regularizationModal" tabindex="-1" aria-labelledby="regularizationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="regularizationModalLabel">Regularize Attendance</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="regularizationForm">
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="regularize-date" class="form-label">Date</label>
                <input type="date" class="form-control" id="regularize-date" name="date" required>
              </div>
              <div class="col-md-6">
                <label for="regularize-type" class="form-label">Regularization Type</label>
                <select class="form-select" id="regularize-type" name="type" required>
                  <option value="">Select type</option>
                  <option value="checkin_correction">Check-in Time Correction</option>
                  <option value="checkout_correction">Check-out Time Correction</option>
                  <option value="forgot">Forgot to Check In/Out</option>
                  <option value="leave">Mark as Leave</option>
                  <option value="half_day">Mark as Half Day</option>
                </select>
              </div>
            </div>

            <div class="row mb-3" id="time-correction-fields">
              <div class="col-md-6" id="check-in-field">
                <label for="regularize-checkin" class="form-label">Check-in Time</label>
                <input type="time" class="form-control" id="regularize-checkin" name="check_in_time">
              </div>
              <div class="col-md-6" id="check-out-field">
                <label for="regularize-checkout" class="form-label">Check-out Time</label>
                <input type="time" class="form-control" id="regularize-checkout" name="check_out_time">
              </div>
            </div>

            <div class="mb-3">
              <label for="regularize-reason" class="form-label">Reason</label>
              <textarea class="form-control" id="regularize-reason" name="reason" rows="3" required></textarea>
            </div>

            <div class="mb-3">
              <label for="regularize-evidence" class="form-label">Evidence (optional)</label>
              <input type="file" class="form-control" id="regularize-evidence" name="evidence">
              <small class="text-muted">Upload any supporting documents if available</small>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="submit-regularization">Submit for Approval</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Holiday Modal -->
  <div class="modal fade" id="holidayModal" tabindex="-1" aria-labelledby="holidayModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="holidayModalLabel">Holiday Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="holiday-details">
          <!-- Holiday details will be populated here -->
        </div>
      </div>
    </div>
  </div>

  <?php require('include/footer.php'); ?>
</div>

<script>
$(document).ready(function() {
  let currentDate = new Date();
  let currentMonth = $("#month").val();
  let currentYear = $("#year").val();
  let selectedDate = null;
  let attendanceData = [];

  // Initialize calendar
  refreshCalendar(currentMonth, currentYear);

  // Event Listeners
  $("#month, #year").change(function() {
    currentMonth = $("#month").val();
    currentYear = $("#year").val();
    refreshCalendar(currentMonth, currentYear);
  });

  $("#todayBtn").click(function() {
    const today = new Date();
    $("#month").val(today.getMonth() + 1);
    $("#year").val(today.getFullYear());
    currentMonth = $("#month").val();
    currentYear = $("#year").val();
    refreshCalendar(currentMonth, currentYear);
  });

  $("#prev-month").click(function() {
    currentMonth--;
    if (currentMonth < 1) {
      currentMonth = 12;
      currentYear--;
    }
    $("#month").val(currentMonth);
    $("#year").val(currentYear);
    refreshCalendar(currentMonth, currentYear);
  });

  $("#next-month").click(function() {
    currentMonth++;
    if (currentMonth > 12) {
      currentMonth = 1;
      currentYear++;
    }
    $("#month").val(currentMonth);
    $("#year").val(currentYear);
    refreshCalendar(currentMonth, currentYear);
  });

  $("#exportExcel").click(function() {
    window.location.href = `function/export_attendance_excel.php?month=${currentMonth}&year=${currentYear}`;
  });

  $("#regularizeBtn").click(function() {
    // Set current date as default
    const todayStr = new Date().toISOString().split('T')[0];
    $("#regularize-date").val(todayStr);
    
    // Reset form
    $("#regularizationForm")[0].reset();
    $("#regularize-date").val(todayStr);
    $("#time-correction-fields").hide();
    
    // Show modal
    $("#regularizationModal").modal('show');
  });

  $("#regularize-type").change(function() {
    const type = $(this).val();
    
    // Show/hide fields based on selected type
    if (type === 'checkin_correction') {
      $("#time-correction-fields").show();
      $("#check-in-field").show();
      $("#check-out-field").hide();
    } else if (type === 'checkout_correction') {
      $("#time-correction-fields").show();
      $("#check-in-field").hide();
      $("#check-out-field").show();
    } else if (type === 'forgot') {
      $("#time-correction-fields").show();
      $("#check-in-field").show();
      $("#check-out-field").show();
    } else {
      $("#time-correction-fields").hide();
    }
  });

  $("#submit-regularization").click(function() {
    // Validate form
    const form = document.getElementById('regularizationForm');
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    // Get form data
    const formData = new FormData(form);
    
    // Submit via AJAX
    $.ajax({
      url: 'function/submit_regularization.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        try {
          // Parse response if it's a string
          const result = typeof response === 'string' ? JSON.parse(response) : response;
          
          if (result.success) {
            alert('Regularization request submitted successfully!');
            $("#regularizationModal").modal('hide');
            // Refresh calendar to show pending status
            refreshCalendar(currentMonth, currentYear);
          } else {
            alert('Error: ' + (result.message || 'Unknown error occurred'));
          }
        } catch (e) {
          console.error('Error parsing response:', e);
          alert('An error occurred while processing the server response.');
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX Error:', error);
        alert('An error occurred while submitting your request. Please try again.');
      }
    });
  });

  $("#close-sidebar").click(function() {
    $("#details-sidebar").addClass('d-none');
    $(".calendar-day").removeClass('selected');
  });

  // Functions
  function refreshCalendar(month, year) {
    // Update calendar title 
    const monthName = new Date(year, month - 1, 1).toLocaleString('default', { month: 'long' });
    $("#calendar-title").text(`${monthName} ${year}`);
    
    // Show loading indicator
    const calendarBody = document.getElementById('calendar-body');
    calendarBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading calendar data...</p></div>';
    
    // Fetch attendance data
    $.ajax({
      url: `function/fetch_attendance_calendar.php?month=${month}&year=${year}`,
      type: 'GET',
      dataType: 'json',
      success: function(data) {
        console.log("Calendar data received:", data);
        attendanceData = data;
        renderCalendar(month, year, data);
      },
      error: function(xhr, status, error) {
        console.error("Error fetching attendance data:", error);
        console.error("Response:", xhr.responseText);
        calendarBody.innerHTML = '<div class="text-center p-5 text-danger"><i class="bi bi-exclamation-triangle-fill fs-1"></i><p class="mt-2">Failed to load calendar data. Please try refreshing the page.</p></div>';
      }
    });
  }

  function renderCalendar(month, year, events) {
    const calendarBody = document.getElementById('calendar-body');
    calendarBody.innerHTML = '';

    // First day of the month (0 = Sunday, 1 = Monday, etc.)
    const firstDay = new Date(year, month - 1, 1).getDay();
    
    // Last day of the month
    const lastDay = new Date(year, month, 0).getDate();
    
    // Current date for highlighting today
    const today = new Date();
    const todayDate = today.getDate();
    const todayMonth = today.getMonth() + 1;
    const todayYear = today.getFullYear();

    // Convert events to a map for easier access
    const eventMap = {};
    if (Array.isArray(events)) {
      events.forEach(event => {
        const date = new Date(event.start);
        const day = date.getDate();
        eventMap[day] = event;
      });
    }

    // Create calendar rows and cells
    let date = 1;
    
    // Create rows for 6 weeks (max possible for a month)
    for (let i = 0; i < 6; i++) {
      // Break if we've already rendered all days
      if (date > lastDay) break;
      
      const row = document.createElement('div');
      row.className = 'row';
      
      // Create 7 cells for each day of the week
      for (let j = 0; j < 7; j++) {
        const cell = document.createElement('div');
        cell.className = 'col calendar-day p-2';
        
        if ((i === 0 && j < firstDay) || date > lastDay) {
          // Empty cell before first day or after last day
          cell.classList.add('inactive');
          cell.innerHTML = '&nbsp;';
        } else {
          // Valid day cell
          
          // Check if it's a weekend
          if (j === 0 || j === 6) {
            cell.classList.add('weekend');
          }
          
          // Check if it's today
          if (date === todayDate && month == todayMonth && year == todayYear) {
            cell.classList.add('today');
          }

          // Add date number
          const dateNumber = document.createElement('div');
          dateNumber.className = 'date-number';
          dateNumber.textContent = date;
          cell.appendChild(dateNumber);
          
          // Add status badge if event exists for this date
          if (eventMap[date]) {
            const event = eventMap[date];
            const statusBadge = document.createElement('div');
            statusBadge.className = 'status-badge';
            
            const badge = document.createElement('span');
            badge.className = 'badge';
            badge.textContent = event.title;
            
            // Set badge color based on status
            switch (event.color) {
              case '#28a745': // Present
                badge.classList.add('bg-success');
                break;
              case '#fd7e14': // Late/Half-day
                badge.classList.add('bg-warning', 'text-dark');
                break;
              case '#dc3545': // Absent
                badge.classList.add('bg-danger');
                break;
              case '#007bff': // Holiday
                badge.classList.add('bg-primary');
                break;
              case '#6610f2': // Leave
                badge.classList.add('bg-purple');
                break;
              default:
                badge.classList.add('bg-secondary');
            }
            
            statusBadge.appendChild(badge);
            cell.appendChild(statusBadge);
            
            // Store event data
            cell.dataset.date = event.start;
            cell.dataset.eventId = event.id;
          } else {
            // No event for this date
            cell.dataset.date = `${year}-${String(month).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
          }
          
          // Add click event
          cell.addEventListener('click', function() {
            if (!this.classList.contains('inactive')) {
              showDayDetails(this);
            }
          });
          
          date++;
        }
        
        row.appendChild(cell);
      }
      
      calendarBody.appendChild(row);
    }
  }

  function showDayDetails(dayElement) {
    // Remove selection from previously selected day
    $(".calendar-day").removeClass('selected');
    
    // Add selection to clicked day
    $(dayElement).addClass('selected');
    
    const dateStr = dayElement.dataset.date;
    const eventId = dayElement.dataset.eventId;
    
    // Find the event data
    let event = null;
    if (eventId && attendanceData) {
      event = attendanceData.find(e => e.id === eventId);
    }
    
    // Format date for display
    const displayDate = new Date(dateStr);
    const formattedDate = displayDate.toLocaleDateString('en-US', { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
    
    // Update sidebar header
    $("#selected-date-heading").text(formattedDate);
    
    // Update details content
    const detailsContent = document.getElementById('attendance-details');
    
    if (event) {
      // Get event details
      const status = event.title;
      const extProps = event.extendedProps || {};
      const checkIn = extProps.checkIn || '--';
      const checkOut = extProps.checkOut || '--';
      const totalHours = extProps.totalHours || '--';
      const checkInLocation = extProps.CheckInlocation;
      const checkOutLocation = extProps.CheckOutlocation;
      const eventType = extProps.type || 'attendance';
      
      // Format status badge
      const statusClass = getStatusClass(event.color);
      
      // Build HTML content
      let html = '';
      
      if (eventType === 'holiday') {
        // Holiday details
        html = `
          <div class="mb-3">
            <span class="d-block mb-2">Holiday:</span>
            <span class="badge bg-primary">${status}</span>
          </div>
          <div class="mb-3">
            <span class="d-block mb-2">Description:</span>
            <p>${extProps.description || 'Official Holiday'}</p>
          </div>
        `;
      } else {
        // Attendance details
        html = `
          <div class="mb-3">
            <span class="d-block mb-2">Status:</span>
            <span class="badge ${statusClass}">${status}</span>
          </div>
        `;
        
        if (eventType !== 'upcoming') {
          html += `
            <div class="mb-3">
              <span class="d-block mb-2">Check-in Time:</span>
              <strong>${checkIn}</strong>
            </div>
            <div class="mb-3">
              <span class="d-block mb-2">Check-out Time:</span>
              <strong>${checkOut}</strong>
            </div>
            <div class="mb-3">
              <span class="d-block mb-2">Total Hours:</span>
              <strong>${totalHours}</strong>
            </div>
          `;
          
          // Add location links if available
          if (checkInLocation) {
            html += `
              <div class="mb-3">
                <span class="d-block mb-2">Check-in Location:</span>
                <a href="${checkInLocation}" target="_blank" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-geo-alt"></i> View on Map
                </a>
              </div>
            `;
          }
          
          if (checkOutLocation) {
            html += `
              <div class="mb-3">
                <span class="d-block mb-2">Check-out Location:</span>
                <a href="${checkOutLocation}" target="_blank" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-geo-alt"></i> View on Map
                </a>
              </div>
            `;
          }
          
          if (eventType === 'regularization' && extProps.status === 'pending') {
            html += `
              <div class="mb-3">
                <span class="d-block mb-2">Regularization:</span>
                <span class="badge bg-warning text-dark">Pending Approval</span>
                <p class="mt-2 small text-muted">Your regularization request is awaiting approval.</p>
              </div>
            `;
          }
        }
      }
      
      // Add action buttons
      const isPast = new Date(dateStr) < new Date(new Date().toISOString().split('T')[0]);
      const isFuture = new Date(dateStr) > new Date(new Date().toISOString().split('T')[0]);
      
      if (!isFuture && eventType !== 'holiday' && (eventType !== 'regularization' || extProps.status !== 'pending')) {
        html += `<hr><div class="action-buttons">`;
        
        // Only show regularize button for past dates
        if (isPast) {
          html += `
            <button class="btn btn-sm btn-outline-primary regularize-day" data-date="${dateStr}">
              <i class="bi bi-pencil-square"></i> Regularize
            </button>
          `;
        }
        
        html += `</div>`;
      }
      
      detailsContent.innerHTML = html;
      
      // Add event listeners to new buttons
      $('.regularize-day').click(function() {
        const dateToRegularize = $(this).data('date');
        $("#regularize-date").val(dateToRegularize);
        $("#regularizationModal").modal('show');
      });
      
    } else {
      // No event data (future date or no record)
      const isPast = new Date(dateStr) < new Date(new Date().toISOString().split('T')[0]);
      
      if (isPast) {
        detailsContent.innerHTML = `
          <p class="text-muted">No attendance data available for this date.</p>
          <hr>
          <div class="action-buttons">
            <button class="btn btn-sm btn-outline-primary regularize-day" data-date="${dateStr}">
              <i class="bi bi-pencil-square"></i> Regularize
            </button>
          </div>
        `;
        
        // Add event listeners to new buttons
        $('.regularize-day').click(function() {
          const dateToRegularize = $(this).data('date');
          $("#regularize-date").val(dateToRegularize);
          $("#regularizationModal").modal('show');
        });
      } else {
        detailsContent.innerHTML = '<p class="text-muted">No attendance data available for this date.</p>';
      }
    }
    
    // Show sidebar
    $("#details-sidebar").removeClass('d-none');
  }

  function getStatusClass(color) {
    switch (color) {
      case '#28a745': return 'bg-success';
      case '#fd7e14': return 'bg-warning text-dark';
      case '#dc3545': return 'bg-danger';
      case '#007bff': return 'bg-primary';
      case '#6610f2': return 'bg-purple';
      default: return 'bg-secondary';
    }
  }
  
  // Initialize tooltips
  $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>