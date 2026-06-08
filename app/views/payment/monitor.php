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
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Ref ID</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Expires In</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">No active payment requests</div>
                                    <a href="<?php echo URLROOT; ?>/payment/create" class="btn btn-sm btn-link">Create one now</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/layout/footer.php'; ?>
