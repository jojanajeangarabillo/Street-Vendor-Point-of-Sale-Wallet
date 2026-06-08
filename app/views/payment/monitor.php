<?php require APPROOT . '/views/layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Live Payment Monitoring</h1>
    <div class="badge bg-success">
        <i class="bi bi-arrow-repeat me-1"></i> Auto-refreshing...
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-body">
                <div id="requests-container" class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Ref ID</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="requests-body">
                            <?php if(empty($data['requests'])) : ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">No active payment requests</div>
                                        <a href="<?php echo URLROOT; ?>/payment/create" class="btn btn-sm btn-link">Create one now</a>
                                    </td>
                                </tr>
                            <?php else : ?>
                                <?php foreach($data['requests'] as $request) : ?>
                                    <tr id="request-<?php echo $request->payment_reference; ?>">
                                        <td><strong><?php echo $request->payment_reference; ?></strong></td>
                                        <td><?php echo $request->amount; ?> XLM</td>
                                        <td><?php echo $request->description; ?></td>
                                        <td><span class="badge bg-warning text-dark">Pending</span></td>
                                        <td><?php echo $request->created_at; ?></td>
                                        <td>
                                            <a href="<?php echo URLROOT; ?>/payment/showQR/<?php echo $request->payment_reference; ?>" class="btn btn-sm btn-outline-primary">Show QR</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function refreshRequests() {
        // For simplicity, we'll just reload the page for now, but in a real app, we'd fetch JSON
        // Actually, let's just do a location.reload() every 30 seconds as a simple "auto-refresh"
        // OR better, fetch the checkStatus for each pending request.
        
        const rows = document.querySelectorAll('[id^="request-"]');
        rows.forEach(row => {
            const ref = row.id.replace('request-', '');
            fetch(`<?php echo URLROOT; ?>/payment/checkStatus/${ref}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'paid') {
                        row.classList.add('table-success');
                        row.querySelector('.badge').className = 'badge bg-success';
                        row.querySelector('.badge').innerText = 'Paid';
                        setTimeout(() => row.remove(), 5000);
                    } else if (data.status === 'expired') {
                        row.classList.add('table-danger');
                        row.querySelector('.badge').className = 'badge bg-danger';
                        row.querySelector('.badge').innerText = 'Expired';
                        setTimeout(() => row.remove(), 5000);
                    }
                });
        });
    }

    setInterval(refreshRequests, 15000); // Check every 15 seconds
</script>

<?php require APPROOT . '/views/layout/footer.php'; ?>
