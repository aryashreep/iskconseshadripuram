<?php
/**
 * Sudamaseva Module — Print Receipt (Admin/Staff)
 *
 * Renders a clean, print-ready layout of an 80G receipt and triggers browser print.
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.view');

use Isjm\Modules\Sudamaseva\SudamasevaService;
use Isjm\Modules\Sudamaseva\SudamasevaRepository;

$service = new SudamasevaService();
$repo = new SudamasevaRepository();

$receiptId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$paymentId = isset($_GET['payment_id']) ? (int) $_GET['payment_id'] : 0;

if ($receiptId > 0) {
    $receipt = $repo->getReceiptById($receiptId);
} elseif ($paymentId > 0) {
    $receiptsList = $repo->getReceiptsByPayment($paymentId);
    $receipt = !empty($receiptsList) ? $receiptsList[0] : null;
} else {
    die('Invalid receipt or payment ID.');
}

if (!$receipt) {
    die('Receipt not found.');
}

// Fetch corresponding payment
$db = getDB();
$stmtPayment = $db->prepare("
    SELECT p.*, d.donor_name, d.phone, d.email, d.pan, d.area, d.city, d.state 
    FROM sudamaseva_payments p
    LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id
    WHERE p.id = ? LIMIT 1
");
$stmtPayment->execute([$receipt['payment_id']]);
$payment = $stmtPayment->fetch();

if (!$payment) {
    die('Associated payment record not found.');
}

// Extract receipt data (JSON field fallback to database row values)
$rData = $receipt['receipt_data'] ? json_decode($receipt['receipt_data'], true) : [];
$donorName = $rData['donor_name'] ?? $payment['donor_name'] ?? 'Devotee';
$donorPan = $rData['donor_pan'] ?? $payment['pan'] ?? '';
$amount = (float) ($rData['amount'] ?? $payment['amount'] ?? 0);
$payDate = $rData['payment_date'] ?? $payment['payment_date'] ?? '';
$method = $rData['payment_method'] ?? 'Online';
$refNo = $rData['reference_no'] ?? $payment['razorpay_payment_id'] ?? '';
$fy = $rData['fy'] ?? $service->getFinancialYearLabel();
$instNum = (int) ($payment['installment_number'] ?? 0);

// Helper function: Convert number to words (Indian system)
function numberToWords(float $number): string
{
    $no = (int) floor($number);
    $point = (int) round(($number - $no) * 100);
    $hundred = null;
    $digits_1 = strlen($no);
    $i = 0;
    $str = array();
    $words = array(
        0 => '', 1 => 'One', 2 => 'Two',
        3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
        7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
        13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
        19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
        40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
        70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
    );
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . $hundred;
        } else {
            $str[] = null;
        }
    }
    $Rupees = implode('', array_reverse($str));
    $paise = ($point) ? "and " . ($words[$point - $point % 10] . " " . $words[$point % 10]) . ' Paise' : '';
    return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise . 'Only';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Donation Receipt - <?php echo htmlspecialchars($receipt['receipt_no']); ?></title>
  <style>
    body {
      font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: var(--space-xl, 20px);
      background-color: #f5f5f5;
      color: #333;
    }
    .receipt-container {
      max-width: 750px;
      margin: 20px auto;
      background: #fff;
      border: 2px solid #7b1e1e;
      border-radius: 8px;
      padding: 40px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
      position: relative;
    }
    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-30deg);
      font-size: 80px;
      color: rgba(123, 30, 30, 0.04);
      font-weight: 800;
      z-index: 0;
      pointer-events: none;
      white-space: nowrap;
    }
    .receipt-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 2px solid #7b1e1e;
      padding-bottom: 20px;
      margin-bottom: 30px;
    }
    .logo-area {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .logo-area img {
      height: 60px;
      width: auto;
    }
    .logo-text h1 {
      margin: 0;
      font-size: 20px;
      color: #7b1e1e;
      font-weight: 700;
      text-transform: uppercase;
    }
    .logo-text p {
      margin: 4px 0 0;
      font-size: 11px;
      color: #666;
      line-height: 1.4;
    }
    .receipt-meta {
      text-align: right;
      font-size: 12px;
      color: #444;
      line-height: 1.6;
    }
    .receipt-title {
      text-align: center;
      margin-bottom: 30px;
    }
    .receipt-title h2 {
      margin: 0;
      display: inline-block;
      font-size: 16px;
      background: #7b1e1e;
      color: #fff;
      padding: 6px 20px;
      border-radius: 4px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .receipt-details-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
      font-size: 14px;
      z-index: 1;
      position: relative;
    }
    .receipt-details-table td {
      padding: 10px;
      vertical-align: top;
      border-bottom: 1px dashed #eee;
    }
    .receipt-details-table td.label {
      font-weight: 600;
      color: #666;
      width: 30%;
    }
    .receipt-details-table td.value {
      color: #111;
      font-weight: 500;
    }
    .amount-block {
      background-color: #fdf5f5;
      border: 1px solid #f5c2c2;
      border-radius: 6px;
      padding: 15px 20px;
      margin-bottom: 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 1;
      position: relative;
    }
    .amount-val {
      font-size: 22px;
      font-weight: 700;
      color: #7b1e1e;
    }
    .amount-words {
      font-size: 13px;
      font-style: italic;
      color: #555;
    }
    .exempt-notice {
      background-color: #f9f9f9;
      border-left: 3px solid #7b1e1e;
      padding: 15px;
      font-size: 11px;
      color: #555;
      line-height: 1.6;
      margin-bottom: 40px;
    }
    .signatures {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-top: 50px;
      z-index: 1;
      position: relative;
    }
    .sig-block {
      text-align: center;
      width: 200px;
      font-size: 12px;
    }
    .sig-line {
      border-top: 1px solid #666;
      margin-top: 50px;
      padding-top: 5px;
      color: #666;
    }
    .print-actions {
      text-align: center;
      margin: 30px 0;
    }
    .btn-print {
      background-color: #7b1e1e;
      color: #fff;
      border: none;
      padding: 10px 24px;
      border-radius: 4px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .btn-print:hover {
      background-color: #a02828;
    }

    @media print {
      body {
        background-color: #fff;
        padding: 0;
        margin: 0;
      }
      .receipt-container {
        border: none;
        box-shadow: none;
        margin: 0;
        padding: 0;
        max-width: 100%;
      }
      .print-actions {
        display: none;
      }
    }
  </style>
</head>
<body>

<div class="print-actions">
  <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print Receipt</button>
</div>

<div class="receipt-container">
  <div class="watermark">ISKCON BANGALORE</div>
  
  <div class="receipt-header">
    <div class="logo-area">
      <img src="assets/images/iskcon_logo.svg" alt="ISKCON Logo">
      <div class="logo-text">
        <h1>International Society for Krishna Consciousness</h1>
        <p>
          ISKCON Sri Jagannath Mandir, Seshadripuram<br>
          5th Cross, Seshadripuram, Bangalore - 560020, Karnataka, India<br>
          Tel: +91 80 2346 1111 | Email: accounts@iskconseshadripuram.org
        </p>
      </div>
    </div>
    <div class="receipt-meta">
      <strong>Receipt No:</strong> <?php echo htmlspecialchars($receipt['receipt_no']); ?><br>
      <strong>Date:</strong> <?php echo $service->formatDate($receipt['receipt_date'], 'd M Y'); ?><br>
      <strong>FY:</strong> <?php echo htmlspecialchars($fy); ?>
    </div>
  </div>

  <div class="receipt-title">
    <h2>Donation Receipt</h2>
  </div>

  <table class="receipt-details-table">
    <tr>
      <td class="label">Received with thanks from</td>
      <td class="value"><?php echo htmlspecialchars($donorName); ?></td>
    </tr>
    <?php if ($donorPan): ?>
      <tr>
        <td class="label">Donor PAN</td>
        <td class="value" style="font-family: monospace; letter-spacing: 0.5px;"><?php echo htmlspecialchars($donorPan); ?></td>
      </tr>
    <?php endif; ?>
    <tr>
      <td class="label">Seva / Purpose</td>
      <td class="value">Sudama Seva Monthly Contribution (Installment #<?php echo $instNum; ?>)</td>
    </tr>
    <tr>
      <td class="label">Payment Mode</td>
      <td class="value"><?php echo ucfirst(str_replace('_', ' ', $method)); ?></td>
    </tr>
    <?php if ($refNo): ?>
      <tr>
        <td class="label">Transaction Reference</td>
        <td class="value" style="font-family: monospace; font-size: 13px;"><?php echo htmlspecialchars($refNo); ?></td>
      </tr>
    <?php endif; ?>
  </table>

  <div class="amount-block">
    <div>
      <div class="amount-words">Amount in Words:</div>
      <div style="font-weight: 600; margin-top: 4px;"><?php echo numberToWords($amount); ?></div>
    </div>
    <div class="amount-val">
      <?php echo $service->formatAmount($amount); ?>
    </div>
  </div>

  <div class="exempt-notice">
    <strong>Tax Exemption Notice:</strong><br>
    All donations to ISKCON Sri Jagannath Mandir are exempt under Section 80G of the Income Tax Act, 1961 
    vide Order No. ITBA/EXM/S/80G/2021-22/1039864223(1) dated 14/10/2021, valid from AY 2022-23 onwards. 
    Donee PAN: AAATI0040G. Form 10BE certificate will be uploaded and issued to you by the Income Tax Department 
    upon filing Form 10BD at the end of the financial year.
  </div>

  <div class="signatures">
    <div class="sig-block">
      <div class="sig-line">Donor's Signature</div>
    </div>
    <div class="sig-block">
      <div style="font-style: italic; color: #666; font-size: 11px; margin-bottom: 5px;">Generated Electronically</div>
      <div class="sig-line">For ISKCON Sri Jagannath Mandir</div>
    </div>
  </div>
</div>

<script>
// Automatically open the print dialog when page loads
window.addEventListener('load', function() {
  setTimeout(function() {
    window.print();
  }, 500);
});
</script>

</body>
</html>
