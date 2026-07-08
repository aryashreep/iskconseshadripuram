-- Panihati Expenses data (local → prod sync)
-- Run this on production database iskcop35_iskconseshadripuram

INSERT INTO `panihati_expenses` (`id`, `type`, `particulars`, `amount`, `category`, `expense_date`, `created_at`, `updated_at`) VALUES
(1, 'expense', 'VIPIN Pr Self Cheque Issued from Axis 8445', 50000.00, 'Transport', '2026-06-18', '2026-07-06 18:43:29', '2026-07-06 19:57:16'),
(2, 'expense', 'VIPIN Pr Self Cheque Issued from Axis 8445', 50000.00, 'Transport', '2026-06-18', '2026-07-06 18:43:29', '2026-07-06 19:57:16'),
(3, 'expense', 'Rakhal Pr Self Cheque Issued from Axis 8445', 60000.00, 'Transport', '2026-06-18', '2026-07-06 18:43:29', '2026-07-06 19:57:16'),
(4, 'expense', 'Rakhal Pr Self Cheque Issued from Axis 8445', 30000.00, 'Transport', '2026-06-18', '2026-07-06 18:43:30', '2026-07-06 19:57:16'),
(5, 'expense', 'Kitchen ICICI Bank Issued from Axis 8445', 200000.00, 'Prasadam & Kitchen', '2026-06-18', '2026-07-06 18:43:30', '2026-07-06 19:57:16'),
(6, 'expense', 'Nandish Bus transferred from Axis 8445', 50000.00, 'Transport', '2026-06-18', '2026-07-06 18:43:30', '2026-07-06 19:57:16'),
(7, 'expense', 'Mastaiah Gosai Ghat Booking transferred from Axis 8445', 25000.00, 'Venue Bookings', '2026-06-18', '2026-07-06 18:43:30', '2026-07-06 19:57:16'),
(8, 'expense', 'Nandish Bus cheque written from Axis 8445', 338500.00, 'Transport', '2026-06-18', '2026-07-06 18:43:30', '2026-07-06 19:57:16'),
(9, 'expense', 'Cash paid to Nandish Bus', 150000.00, 'Transport', '2026-06-18', '2026-07-06 18:43:30', '2026-07-06 19:57:16'),
(10, 'expense', 'Rakhal Pr Self Cheque Issued from Axis 8445', 103150.00, 'Transport', '2026-06-18', '2026-07-06 18:43:30', '2026-07-06 19:57:16'),
(11, 'expense', 'Rakhal Pr Self Cheque Issued from Axis 8445', 32000.00, 'Transport', '2026-06-18', '2026-07-06 18:43:30', '2026-07-06 19:57:16'),
(12, 'expense', 'Rakhal Pr took cash from Giteshwari Mtj on PH site', 35600.00, 'Miscellaneous', '2026-06-18', '2026-07-06 18:43:30', '2026-07-06 18:43:30'),
(13, 'expense', 'Keshav Pr Panihati Labour', 13000.00, 'Labour & Seva', '2026-06-18', '2026-07-06 18:43:30', '2026-07-06 19:57:16'),
(14, 'expense', 'Vipin pr Expenses for Deity', 750.00, 'Deity Worship', '2026-06-18', '2026-07-06 18:43:30', '2026-07-06 19:57:16'),
(15, 'income', 'Online Collection', 102401.00, 'Miscellaneous', '2026-06-18', '2026-07-06 18:43:30', '2026-07-06 18:43:30'),
(16, 'income', 'BBT Counter Cash', 98100.00, 'Miscellaneous', '2026-06-18', '2026-07-06 18:43:30', '2026-07-06 18:43:30')
ON DUPLICATE KEY UPDATE
  `type` = VALUES(`type`),
  `particulars` = VALUES(`particulars`),
  `amount` = VALUES(`amount`),
  `category` = VALUES(`category`),
  `expense_date` = VALUES(`expense_date`),
  `updated_at` = VALUES(`updated_at`);
