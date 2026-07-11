<?php
$pageTitle = 'Registration Success - Panihati Yatra';
$pageType = 'default';
require_once '../config.php';
include '../partials/header.php';

$registrationId = intval($_GET['reg_id'] ?? 0);
$paymentId = trim($_GET['pay_id'] ?? '');

$reg = null;
if ($registrationId > 0) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM panihati_yatra_registrations WHERE id = ? LIMIT 1");
        $stmt->execute([$registrationId]);
        $reg = $stmt->fetch();
    } catch (PDOException $e) {
        // Silent error
    }
}
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/panihati-banner1.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Registration Successful</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <span>Panihati Yatra</span>
      <span>›</span>
      <span>Success</span>
    </div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width: 700px;">
    
    <div style="background: var(--white); border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-md); padding: var(--space-2xl); text-align: center;" class="reveal">
      <div style="width: 80px; height: 80px; background: #e8f5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-lg); color: #2e7d32; font-size: 36px; box-shadow: var(--shadow-sm);">
        <i class="fas fa-check-circle"></i>
      </div>
      
      <h2 style="font-family: var(--font-heading); color: var(--text-dark); margin-bottom: var(--space-sm);">Haribol! Registration Confirmed</h2>
      <p style="color: var(--text-light); margin-bottom: var(--space-xl); line-height: 1.6;">
        Thank you for registering for the Panihati Yatra. Your payment has been successfully processed and verified.
      </p>

      <?php if ($reg): ?>
        <div style="background: var(--cream-light); border: 1px solid var(--border); border-radius: var(--radius-md); padding: var(--space-xl); text-align: left; margin-bottom: var(--space-xl);">
          <h3 style="font-family: var(--font-heading); color: var(--text-dark); font-size: var(--font-size-md); margin-top: 0; margin-bottom: var(--space-md); border-bottom: 2px solid var(--primary); display: inline-block; padding-bottom: 4px;">Registration Details</h3>
          
          <table style="width: 100%; border-collapse: collapse; font-size: var(--font-size-sm); color: var(--text);">
            <tr style="border-bottom: 1px solid var(--border);">
              <td style="padding: 10px 0; font-weight: 600; width: 40%;">Registration ID:</td>
              <td style="padding: 10px 0;">#<?php echo htmlspecialchars($reg['id']); ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border);">
              <td style="padding: 10px 0; font-weight: 600;">Name:</td>
              <td style="padding: 10px 0;"><?php echo htmlspecialchars($reg['name']); ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border);">
              <td style="padding: 10px 0; font-weight: 600;">Phone:</td>
              <td style="padding: 10px 0;"><?php echo htmlspecialchars($reg['phone']); ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border);">
              <td style="padding: 10px 0; font-weight: 600;">Email:</td>
              <td style="padding: 10px 0;"><?php echo htmlspecialchars($reg['email']); ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border);">
              <td style="padding: 10px 0; font-weight: 600;">Travel Mode:</td>
              <td style="padding: 10px 0; text-transform: uppercase; font-weight: 600; color: var(--primary);">
                <?php echo str_replace('_', ' ', htmlspecialchars($reg['travel_mode'])); ?>
              </td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border);">
              <td style="padding: 10px 0; font-weight: 600;">Participants:</td>
              <td style="padding: 10px 0;">
                Adults: <?php echo htmlspecialchars($reg['adults_count']); ?> | 
                Kids: <?php echo htmlspecialchars($reg['kids_count']); ?>
              </td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border);">
              <td style="padding: 10px 0; font-weight: 600;">Connected Bhakti Sadan:</td>
              <td style="padding: 10px 0;"><?php echo htmlspecialchars($reg['bhakti_sadan']); ?></td>
            </tr>
            <?php if ($reg['travel_mode'] === 'bus'): ?>
            <tr style="border-bottom: 1px solid var(--border);">
              <td style="padding: 10px 0; font-weight: 600;">Pickup Location:</td>
              <td style="padding: 10px 0; font-weight: 600; color: var(--maroon);"><?php echo htmlspecialchars($reg['pickup_location']); ?></td>
            </tr>
            <?php endif; ?>
            <tr style="border-bottom: 1px solid var(--border);">
              <td style="padding: 10px 0; font-weight: 600;">Amount Paid:</td>
              <td style="padding: 10px 0; font-weight: 700; color: var(--primary); font-size: var(--font-size-md);">
                ₹<?php echo number_format($reg['amount'], 2); ?>
              </td>
            </tr>
            <tr>
              <td style="padding: 10px 0; font-weight: 600;">Payment Transaction ID:</td>
              <td style="padding: 10px 0; font-family: monospace; font-size: 12px; color: var(--text-light);"><?php echo htmlspecialchars($paymentId); ?></td>
            </tr>
          </table>
        </div>
      <?php endif; ?>

      <div style="background: #fff9f0; border: 1px solid #ffe8cc; border-radius: var(--radius-md); padding: var(--space-md); margin-bottom: var(--space-xl); font-size: var(--font-size-sm); color: var(--text); display: flex; align-items: flex-start; gap: 8px; text-align: left;">
        <i class="fas fa-info-circle" style="color: var(--primary); margin-top: 2px;"></i>
        <div>
          <strong>Important:</strong> Please keep a screenshot or print of this confirmation page. Bus departure timings and details will be shared with you via SMS/WhatsApp on your registered phone number.
        </div>
      </div>

      <div style="display: flex; gap: var(--space-md); justify-content: center;">
        <a href="<?php echo BASE_URL; ?>" class="btn btn-primary"><i class="fas fa-home"></i> Go to Home</a>
        <a href="<?php echo BASE_URL; ?>yatra" class="btn btn-outline-dark"><i class="fas fa-route"></i> All Yatras</a>
      </div>

    </div>

  </div>
</section>

<?php include '../partials/footer.php'; ?>
