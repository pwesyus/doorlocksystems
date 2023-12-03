<?php
include 'database.php';

$sql = "SELECT RFIDNumber, name, TIME(Timein) as Timein, TIME(Timeout) as Timeout, status FROM userlogs WHERE DATE(Timein) = CURDATE()";
$result = $conn->query($sql);

if (!$result) {
    // Handle the error. You can print the error for debugging purposes.
    echo "Error retrieving logs: " . $conn->error;
}

// Query to count the total number of logs for today
$countSql = "SELECT COUNT(*) as totalLogs FROM userlogs WHERE DATE(Timein) = CURDATE()";
$countResult = $conn->query($countSql);

if (!$countResult) {
    // Handle the error. You can print the error for debugging purposes.
    echo "Error counting logs: " . $conn->error;
}

$totalLogsToday = $countResult->fetch_assoc()['totalLogs'];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    // Determine the CSS class based on the status
                    $statusClass = '';
                    switch ($row["status"]) {
                        case 'Absent':
                            $statusClass = 'status-absent';
                            break;
                        case 'Late':
                            $statusClass = 'status-late';
                            break;
                        case 'On-time':
                            $statusClass = 'status-on-time';
                            break;
                        case 'Masterkey':
                            $statusClass = 'status-masterkey';
                            break;
                        case 'Leave':
                            $statusClass = 'status-leave';
                            break;
                        default:
                            // Handle other status values if needed
                            break;
                    }

                    echo '<tr>';
                    echo '<td>' . $row["RFIDNumber"] . '</td>';
                    echo '<td>' . $row["name"] . '</td>';
                    echo '<td class="log-time">' . $row["Timein"] . '</td>';
                    echo '<td class="log-time">' . $row["Timeout"] . '</td>';
                    echo '<td class="' . $statusClass . '"><span>' . $row["status"] . '</span></td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="5">Error retrieving logs or no logs found for today</td></tr>';
            }
            ?>