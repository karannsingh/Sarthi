<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

$query = "";

if($_SESSION['ROLE'] = 1){
  $query = "SELECT l.*, U.UserName, 
  rs.StatusName, 
  CONCAT(IFNULL(l.FollowUpDate, ''), ' ', IFNULL(l.FollowUpTime, '')) AS FollowUp,
  lc.Feedback
  FROM leads l
  LEFT JOIN master_remark_status rs ON l.RemarkStatusOID = rs.RemarkStatusOID
  LEFT JOIN lead_conversation lc ON l.LeadOID = lc.LeadOID
  LEFT JOIN users U ON U.UserOID = l.EmployeeOID
  ORDER BY l.LeadDate DESC";
}else{
  $query = "SELECT l.*, U.UserName,
  rs.StatusName, 
  CONCAT(IFNULL(l.FollowUpDate, ''), ' ', IFNULL(l.FollowUpTime, '')) AS FollowUp,
  lc.Feedback
  FROM 
    leads l
  LEFT JOIN 
    master_remark_status rs ON l.RemarkStatusOID = rs.RemarkStatusOID
  LEFT JOIN 
    lead_conversation lc ON l.LeadOID = lc.LeadOID
  LEFT JOIN 
    users U ON U.UserOID = l.EmployeeOID
  WHERE 
    l.EmployeeOID = " . intval($_SESSION['USEROID']) . "
  ORDER BY l.LeadDate DESC";
}

$result = mysqli_query($conn, $query);

?>
<style>
  #leadTable {
    overflow-x: auto;
    white-space: nowrap;
  }
  .table-container thead{
    background-color: #454545;
    color: white;
  }
/* Ensure only table body scrolls */
.table-container {
  overflow-x: auto;
}
.dataTables_scrollHead {
  background-color: #454545 !important;
  color: white !important;
  position: sticky;
  top: 0;
  z-index: 100;
}
.dt-buttons {
    float: none!important;
}
</style>
<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php
  require('include/header.php');
  ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="fs-2 fw-semibold">Lead Summary</div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item">
            <!-- if breadcrumb is single--><span>Lead</span>
          </li>
          <li class="breadcrumb-item active"><span>Lead Summary</span></li>
        </ol>
      </nav>
      <div class="row">
        <div class="col-lg-12 text-center">
          <div class="card mb-4">
            <div class="card-body p-4" style="overflow-x: auto;">
              <!-- <div class="card-title fs-4 fw-semibold">Lead Entry Form</div> -->
              <div class="table-controls">
                <div class="record-count"></div>
                <div class="export-buttons"></div>
                <div class="table-search"></div>
              </div>

<!-- Scrollable Table Body -->
<div class="table-container">
  <table id="leadTable" class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>Sr.No.</th>
        <th>Lead ID</th>
        <th>Customer Name</th>
        <th>Mobile Number</th>
        <th>Vehicle Number</th>
        <th>Renewal Date</th>
        <th>Status</th>
        <th>Follow-Up</th>
        <th>Feedback</th>
        <th>Lead By</th>
        <th>Lead Date</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if (mysqli_num_rows($result) > 0) {
        $count = 1;
        while ($row = mysqli_fetch_assoc($result)) {
          echo "<tr>
          <td>{$count}</td>
          <td><a href='lead-details.php?LeadID=" . $row['LeadOID'] . "' target='_blank'>{$row['LeadID']}</a></td>
          <td>{$row['CustomerName']}</td>
          <td>{$row['CustomerNumber']}</td>
          <td>{$row['VehicleNumber']}</td>
          <td>{$row['RenewalDate']}</td>
          <td>{$row['StatusName']}</td>
          <td>{$row['FollowUp']}</td>
          <td class='feedback-wrap' title='{$row['Feedback']}'>{$row['Feedback']}</td>
          <td>{$row['UserName']}</td>
          <td>{$row['LeadDate']}</td>
          </tr>";
          $count++;
        }
      } else {
        echo "<tr><td colspan='11' class='text-center'>No leads found</td></tr>";
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
</div>
<?php
require('include/footer.php');
?>
<script>
 $(document).ready(function() {
  var table = $('#leadTable').DataTable({
    responsive: false,  
        scrollY: "500px",  // Set a specific height for the table body
        scrollX: true,
        scrollCollapse: true,
        paging: true,
        pagingType: 'full_numbers',
        pageLength: 25,
        fixedHeader: true, // Enable fixed header
        dom: '<"top"lfB>rtip',
        buttons: [
          'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
        ],
        initComplete: function () {
          $(".record-count").html("Total Records: " + table.rows().count());
          $(".export-buttons").html($(".dt-buttons"));
          $(".table-search").html($("#leadTable_filter"));
        }
      });
});

</script>