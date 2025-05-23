<?php
require('../include/config.php');
require('../include/functions.inc.php');

$employee_id = $_SESSION['USEROID']; // Ensure employee is logged in

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $customer_number = $_POST['customer_number'];
    $vehicle_number = $_POST['vehicle_number'];
    $reg_year = $_POST['reg_year'];
    $renewal_date = $_POST['renewal_date'];
    $previous_idv = $_POST['previous_idv'];
    $pre_premium = $_POST['pre_premium'];
    $remark_status_id = $_POST['remark_status_id'];
    $feedback = $_POST['feedback'];
    $link = $_POST['link'];
    $follow_up_date = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : NULL;
    $follow_up_time = !empty($_POST['follow_up_time']) ? $_POST['follow_up_time'] : NULL;

    $lead_date = date("Y-m-d H:i:s");
    
    // Generate unique LeadID
    $fetchLastCode = "SELECT LeadID FROM leads ORDER BY LeadOID DESC LIMIT 1";
    $result = mysqli_query($conn, $fetchLastCode);
    $lastCode = mysqli_fetch_assoc($result)['LeadID'];

    if ($lastCode) {
        $number = (int) substr($lastCode, 1); // Extract numeric part
        $newLeadCode = "L" . str_pad($number + 1, 6, "0", STR_PAD_LEFT); // Increment lead code
    } else {
        $newLeadCode = "L000001"; // First lead code
    }

    // Insert lead data
    $query = "INSERT INTO leads (`LeadID`, `EmployeeOID`, `CustomerName`, `CustomerNumber`, `VehicleNumber`, `RegYear`, `RenewalDate`, `PreviousIDV`, `PreviousPremium`, `RemarkStatusOID`, `LeadDate`, `Link`, `FollowUpDate`, `FollowUpTime`) 
              VALUES ('$newLeadCode', '$employee_id', '$customer_name', '$customer_number', '$vehicle_number', '$reg_year', '$renewal_date', '$previous_idv', '$pre_premium', '$remark_status_id', '$lead_date', '$link', '$follow_up_date', '$follow_up_time')";
    
    if (mysqli_query($conn, $query)) {
        $lead_id = mysqli_insert_id($conn);

        // Insert into lead_conversation
        $conversationQuery = "INSERT INTO lead_conversation (`LeadOID`, `EmployeeOID`, `StatusOID`, `Feedback`, `ConversationDate`) 
                              VALUES ('$lead_id', '$employee_id', '$remark_status_id', '$feedback', '$lead_date')";
        mysqli_query($conn, $conversationQuery);

        // Handle file uploads
        if (!empty($_FILES['documents']['name'][0])) {
            $upload_dir = "../uploads/"; // Directory to store uploaded files
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create directory if not exists
            }

            foreach ($_FILES['documents']['name'] as $key => $file_name) {
                $file_tmp = $_FILES['documents']['tmp_name'][$key];
                $file_size = $_FILES['documents']['size'][$key];
                $file_error = $_FILES['documents']['error'][$key];

                if ($file_error === 0) {
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $allowed_ext = array("jpg", "jpeg", "png", "pdf", "doc", "docx");

                    if (in_array(strtolower($file_ext), $allowed_ext)) {
                        $new_file_name = uniqid("doc_") . "." . $file_ext;
                        $file_path = $upload_dir . $new_file_name;

                        if (move_uploaded_file($file_tmp, $file_path)) {
                            // Save file info in the database
                            $doc_query = "INSERT INTO lead_documents (`LeadOID`, `EmployeeOID`, `DocumentName`, `DocumentPath`) 
                                          VALUES ('$lead_id', '$employee_id', '$file_name', '$file_path')";
                            mysqli_query($conn, $doc_query);
                        }
                    }
                }
            }
        }
        ?>
        <script>
            alert('Lead successfully saved!');
            window.location.href = '../LeadSummary.php'; // Change to the desired page
        </script>
        <?php
    } else {
        $errorMessage = mysqli_error($conn);
        echo "<script>
                alert('Error: " . addslashes($errorMessage) . "');
                window.location.href = '../AddLead.php'; // Redirect even if there's an error
              </script>";
    }
}
?>