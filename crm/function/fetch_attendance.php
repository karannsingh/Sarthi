<?php
require('../include/config.php');
require('../include/functions.inc.php');

if (isset($_SESSION['USEROID'])) {
    $employee_id = $_SESSION['USEROID'];
    $month = isset($_POST['month']) ? $_POST['month'] : date('m');
    $year = isset($_POST['year']) ? $_POST['year'] : date('Y');

    $sql = "SELECT `id`, `check_in_time`, `check_out_time`, `date`, `total_hours`, `status` 
            FROM `employee_attendance` 
            WHERE `employee_id` = ? 
            AND MONTH(`date`) = ? 
            AND YEAR(`date`) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $employee_id, $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['date']}</td>
                    <td>" . (!empty($row['check_in_time']) ? $row['check_in_time'] : '--:--:--') . "</td>
                    <td>" . (!empty($row['check_out_time']) ? $row['check_out_time'] : '--:--:--') . "</td>
                    <td>" . (!empty($row['total_hours']) ? $row['total_hours'] : '--:--:--') . "</td>
                    <td>{$row['status']}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='5' class='text-center fw-semibold'>No record found</td></tr>";
    }
}
?>