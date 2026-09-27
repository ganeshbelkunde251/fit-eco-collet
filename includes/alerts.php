<?php
// includes/alerts.php
$flash = get_flash();
if ($flash):
    $type = $flash['type'] ?? 'info';
    $message = $flash['message'] ?? '';
    $icon = match($type) {
        'success' => 'bi-check-circle-fill',
        'danger'  => 'bi-exclamation-octagon-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        default   => 'bi-info-circle-fill'
    };
?>
<div class="alert alert-<?= htmlspecialchars($type) ?> alert-dismissible fade show d-flex align-items-center shadow-sm rounded-3 mb-4" role="alert">
    <i class="bi <?= $icon ?> me-2 fs-5"></i>
    <div><?= htmlspecialchars($message) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
