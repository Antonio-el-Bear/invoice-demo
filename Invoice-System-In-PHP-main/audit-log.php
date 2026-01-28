<?php

include('header.php');
include('functions.php');
include_once("includes/config.php");

// Get audit logs
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

if ($mysqli->connect_error) {
    die('Error : ('.$mysqli->connect_errno .') '. $mysqli->connect_error);
}

// Pagination
$limit = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM ai_activity ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$results = $mysqli->query($query);

// Get total count
$count_query = "SELECT COUNT(*) as total FROM ai_activity";
$count_result = $mysqli->query($count_query);
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $limit);

?>

<h1>System Activity Log</h1>
<hr>

<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Recent System Activities</h4>
            </div>
            <div class="panel-body form-group form-group-sm">
                <?php
                if($results && $results->num_rows > 0) {
                    echo '<table class="table table-striped table-hover table-bordered" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Entity Type</th>
                                <th>Entity ID</th>
                                <th>User</th>
                                <th>Details</th>
                                <th>Timestamp</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>';

                    while($row = $results->fetch_assoc()) {
                        $status_badge = $row['status'] == 'success' ? '<span class="label label-success">Success</span>' : '<span class="label label-danger">Failed</span>';
                        echo '<tr>
                            <td>'.$row['id'].'</td>
                            <td><strong>'.$row['action'].'</strong></td>
                            <td>'.$row['module'].'</td>
                            <td>'.$row['entity_type'].'</td>
                            <td>'.$row['entity_id'].'</td>
                            <td>'.$row['user'].'</td>
                            <td><small>'.substr($row['details'], 0, 50).'...</small></td>
                            <td>'.$row['created_at'].'</td>
                            <td>'.$status_badge.'</td>
                        </tr>';
                    }

                    echo '</tbody></table>';
                    
                    // Pagination
                    if($total_pages > 1) {
                        echo '<nav><ul class="pagination">';
                        
                        for($i = 1; $i <= $total_pages; $i++) {
                            $active = ($i == $page) ? 'active' : '';
                            echo '<li class="'.$active.'"><a href="?page='.$i.'">'.$i.'</a></li>';
                        }
                        
                        echo '</ul></nav>';
                    }
                } else {
                    echo '<div class="alert alert-info">No activity logged yet.</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
    include('footer.php');
?>
