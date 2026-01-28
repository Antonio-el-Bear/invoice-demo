<?php

include('header.php');
include_once("includes/config.php");

// Check if logs directory exists
$logs_dir = __DIR__ . '/logs';
$log_files = [];

if (is_dir($logs_dir)) {
    $log_files = array_diff(scandir($logs_dir, SCANDIR_SORT_DESCENDING), array('.', '..'));
}

// Get last run status
$last_log = null;
$last_status = 'Never Run';
if (!empty($log_files)) {
    $last_log_file = $logs_dir . '/' . $log_files[0];
    $last_log = file_get_contents($last_log_file);
    $last_status = strpos($last_log, 'Failed') !== false ? 'Last Run Failed' : 'Last Run Success';
}

?>

<h1>Automation & Tasks</h1>
<hr>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Automated Tasks Configuration</h3>
            </div>
            <div class="box-body">
                <p>The system includes several automated tasks that can be scheduled using cron jobs or Windows Task Scheduler.</p>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h4 class="box-title">📧 Overdue Payment Reminders</h4>
                            </div>
                            <div class="box-body">
                                <p><strong>File:</strong> <code>cron-send-reminders.php</code></p>
                                <p><strong>Purpose:</strong> Automatically sends email reminders for overdue invoices</p>
                                <p><strong>Frequency:</strong> Recommended daily (9:00 AM)</p>
                                <p><strong>Features:</strong></p>
                                <ul>
                                    <li>Sends reminders only if last reminder was 3+ days ago</li>
                                    <li>Logs all activity to <code>/logs</code> directory</li>
                                    <li>Skips invoices already paid</li>
                                </ul>
                                
                                <h5>Setup Instructions:</h5>
                                
                                <h6>Windows Task Scheduler:</h6>
                                <ol>
                                    <li>Open Task Scheduler</li>
                                    <li>Create Basic Task</li>
                                    <li>Name: "Invoice Reminders"</li>
                                    <li>Trigger: Daily at 09:00</li>
                                    <li>Action: Start a program
                                        <ul>
                                            <li>Program: <code>C:\xampp\php\php.exe</code></li>
                                            <li>Arguments: <code>C:\xampp\htdocs\clouduko-invoice\cron-send-reminders.php</code></li>
                                        </ul>
                                    </li>
                                </ol>
                                
                                <h6>Linux Cron:</h6>
                                <pre>0 9 * * * /usr/bin/php /var/www/html/clouduko-invoice/cron-send-reminders.php</pre>
                                
                                <h6>Test Run (Manual):</h6>
                                <a href="cron-send-reminders.php" class="btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-play"></i> Run Now
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="box box-warning">
                            <div class="box-header with-border">
                                <h4 class="box-title">⏰ Scheduled Tasks Status</h4>
                            </div>
                            <div class="box-body">
                                <table class="table table-condensed">
                                    <tr>
                                        <td><strong>Task</strong></td>
                                        <td><strong>Status</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Overdue Reminders</td>
                                        <td><span class="label label-<?php echo strpos($last_status, 'Success') !== false ? 'success' : 'warning'; ?>"><?php echo $last_status; ?></span></td>
                                    </tr>
                                </table>
                                
                                <?php if ($last_log): ?>
                                <hr>
                                <h5>Last Execution Log:</h5>
                                <pre style="max-height: 200px; overflow-y: auto; background: #f5f5f5; padding: 10px; border-radius: 4px;">
<?php echo htmlspecialchars(substr($last_log, 0, 500)); ?>
                                </pre>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Task Logs -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">📋 Task Execution History</h3>
            </div>
            <div class="box-body">
                <?php if (!empty($log_files)): ?>
                <table class="table table-striped table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Log File</th>
                            <th>Date</th>
                            <th>Size</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($log_files as $log_file):
                            $file_path = $logs_dir . '/' . $log_file;
                            $file_size = filesize($file_path);
                            $file_date = date('Y-m-d H:i:s', filemtime($file_path));
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log_file); ?></td>
                            <td><?php echo $file_date; ?></td>
                            <td><?php echo number_format($file_size / 1024, 2); ?> KB</td>
                            <td>
                                <a href="javascript:void(0)" class="btn btn-xs btn-info view-log" data-log="<?php echo htmlspecialchars($log_file); ?>">
                                    <i class="fa fa-eye"></i> View
                                </a>
                                <a href="<?php echo 'logs/' . urlencode($log_file); ?>" download class="btn btn-xs btn-primary">
                                    <i class="fa fa-download"></i> Download
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="alert alert-info">No task logs found yet. Once tasks run, logs will appear here.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Log Viewer Modal -->
<div id="logModal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Log Details</h4>
            </div>
            <div class="modal-body">
                <pre id="logContent" style="max-height: 400px; overflow-y: auto;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.view-log', function() {
    var logFile = $(this).data('log');
    $.ajax({
        url: 'logs/' + logFile,
        type: 'GET',
        success: function(data) {
            $('#logContent').text(data);
            $('#logModal').modal('show');
        },
        error: function() {
            alert('Error loading log file');
        }
    });
});
</script>

<?php
    include('footer.php');
?>
