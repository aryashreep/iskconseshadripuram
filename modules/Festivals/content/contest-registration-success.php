<?php
/**
 * Janmashtami Contest Registration — Success Page
 *
 * Displayed after a successful payment and registration.
 * URL: /festivals/contest-registration-success?reg_id=X&pay_id=Y
 */
$pageTitle = 'Contest Registration Successful — Janmashtami';
include '../partials/header.php';
require_once '../config.php';

$registrationId = isset($_GET['reg_id']) ? (int) $_GET['reg_id'] : 0;
$paymentId = isset($_GET['pay_id']) ? trim($_GET['pay_id']) : '';

$registration = null;
$error = '';

if ($registrationId > 0) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT * FROM janmashtami_contest_registrations WHERE id = ?
        ");
        $stmt->execute([$registrationId]);
        $registration = $stmt->fetch();
    } catch (PDOException $e) {
        $error = 'Could not load registration details.';
    }
}
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/janmashtami.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Registration Successful!</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a><span>›</span>
      <a href="<?php echo BASE_URL; ?>festivals/">Festivals</a><span>›</span>
      <a href="<?php echo BASE_URL; ?>festivals/grand-festivals/janmashtami/">Janmashtami</a><span>›</span>
      <span>Success</span>
    </div>
  </div>
</section>

<!-- Content -->
<section class="page-content" style="background:var(--cream-light); padding:var(--space-3xl) 0;">
  <div class="container" style="max-width:700px;">

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <div style="text-align:center; margin-top:var(--space-lg);">
        <a href="<?php echo BASE_URL; ?>festivals/grand-festivals/janmashtami/" class="btn btn-primary">Back to Janmashtami Page</a>
      </div>
    <?php elseif ($registration && $registration['payment_status'] === 'paid'): ?>
      
      <!-- Success Card -->
      <div style="background:var(--white); border-radius:var(--radius-lg); padding:var(--space-2xl); box-shadow:var(--shadow-md); text-align:center; border-top:4px solid #2e7d32;">
        
        <div style="font-size:64px; color:#2e7d32; margin-bottom:var(--space-md);">
          <i class="fas fa-check-circle"></i>
        </div>
        
        <h2 style="font-family:var(--font-heading); color:#2e7d32; margin:0 0 var(--space-sm);">
          Registration Complete!
        </h2>
        
        <p style="color:var(--text-light); font-size:var(--font-size-base); max-width:500px; margin:0 auto var(--space-lg);">
          Your child has been successfully registered for the Janmashtami contest. 
          All participants receive the blessings of Lord Krishna!
        </p>

        <div style="background:var(--cream); border-radius:var(--radius-md); padding:var(--space-lg); text-align:left; margin-bottom:var(--space-lg);">
          <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <tr>
              <td style="padding:8px 12px; font-weight:600; color:var(--text-light); width:140px;">Participant</td>
              <td style="padding:8px 12px; font-weight:600;"><?php echo htmlspecialchars($registration['participant_name']); ?></td>
            </tr>
            <tr>
              <td style="padding:8px 12px; font-weight:600; color:var(--text-light);">Age Group</td>
              <td style="padding:8px 12px;"><?php
                $ageLabels = ['group1' => 'Group 1 (Up to 6 years)', 'group2' => 'Group 2 (7–10 years)', 'group3' => 'Group 3 (11–15 years)'];
                echo htmlspecialchars($ageLabels[$registration['age_group']] ?? $registration['age_group']);
              ?></td>
            </tr>
            <tr>
              <td style="padding:8px 12px; font-weight:600; color:var(--text-light);">Participant Type</td>
              <td style="padding:8px 12px;"><?php echo ucfirst(htmlspecialchars($registration['participant_type'] ?? '')); ?></td>
            </tr>
            <tr>
              <td style="padding:8px 12px; font-weight:600; color:var(--text-light);">Contest</td>
              <td style="padding:8px 12px;"><?php echo htmlspecialchars($registration['contest_name']); ?></td>
            </tr>
            <tr>
              <td style="padding:8px 12px; font-weight:600; color:var(--text-light);">Amount</td>
              <td style="padding:8px 12px; font-weight:700; color:var(--maroon);">₹<?php echo number_format((float) $registration['amount']); ?></td>
            </tr>
            <tr>
              <td style="padding:8px 12px; font-weight:600; color:var(--text-light);">Registration ID</td>
              <td style="padding:8px 12px; font-family:monospace; font-size:12px;">#<?php echo $registration['id']; ?></td>
            </tr>
            <tr>
              <td style="padding:8px 12px; font-weight:600; color:var(--text-light);">Payment ID</td>
              <td style="padding:8px 12px; font-family:monospace; font-size:11px; word-break:break-all;"><?php echo htmlspecialchars($paymentId); ?></td>
            </tr>
          </table>
        </div>

        <div style="background:#e8f5e9; border:1px solid #a5d6a7; border-radius:var(--radius-md); padding:var(--space-md); margin-bottom:var(--space-lg); font-size:13px; color:#2e7d32;">
          <i class="fas fa-info-circle"></i>
          Please save your Registration ID <strong>#<?php echo $registration['id']; ?></strong> for reference. 
          You will be contacted with further details about the contest schedule and venue.
        </div>

        <div style="display:flex; gap:var(--space-md); justify-content:center; flex-wrap:wrap;">
          <a href="<?php echo BASE_URL; ?>festivals/grand-festivals/janmashtami/" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Janmashtami Page
          </a>
          <a href="<?php echo BASE_URL; ?>donate/janmashtami" class="btn btn-accent">
            <i class="fas fa-heart"></i> Offer Janmashtami Seva
          </a>
        </div>

      </div>

    <?php else: ?>
      
      <div style="background:var(--white); border-radius:var(--radius-lg); padding:var(--space-2xl); box-shadow:var(--shadow-md); text-align:center;">
        <div style="font-size:64px; color:var(--primary); margin-bottom:var(--space-md);">
          <i class="fas fa-hourglass-half"></i>
        </div>
        <h2 style="font-family:var(--font-heading); margin:0 0 var(--space-sm);">
          Payment Processing
        </h2>
        <p style="color:var(--text-light);">
          Your registration is being processed. You will receive a confirmation shortly.
        </p>
        <div style="margin-top:var(--space-lg);">
          <a href="<?php echo BASE_URL; ?>festivals/grand-festivals/janmashtami/" class="btn btn-primary">Back to Janmashtami Page</a>
        </div>
      </div>

    <?php endif; ?>

  </div>
</section>

<?php include '../partials/footer.php'; ?>
