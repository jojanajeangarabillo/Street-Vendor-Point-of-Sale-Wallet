<?php require APPROOT . '/views/layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Transaction History</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Export CSV
        </button>
    </div>
</div>

<div class="card shadow">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search by Hash or Ref...">
                    <button class="btn btn-outline-secondary" type="button"><i class="bi bi-search"></i></button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Hash</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['transactions'])) : ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No transaction history available</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach($data['transactions'] as $tx) : ?>
                            <tr>
                                <td><?php echo date('M j, Y H:i', strtotime($tx->confirmed_at)); ?></td>
                                <td><code><?php echo $tx->payment_reference ?? 'N/A'; ?></code></td>
                                <td>
                                    <a href="<?php echo STELLAR_HORIZON_URL; ?>/transactions/<?php echo $tx->stellar_transaction_hash; ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 150px;">
                                        <?php echo $tx->stellar_transaction_hash; ?>
                                    </a>
                                </td>
                                <td><strong><?php echo $tx->amount; ?></strong> <?php echo $tx->asset_code; ?></td>
                                <td><span class="badge bg-success">Confirmed</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center">
            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">Next</a></li>
          </ul>
        </nav>
    </div>
</div>

<?php require APPROOT . '/views/layout/footer.php'; ?>
