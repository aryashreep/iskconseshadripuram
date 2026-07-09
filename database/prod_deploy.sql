-- ================================================================
-- ISKCON Sudamaseva — Production Deployment SQL
-- Run these in order via phpMyAdmin or MySQL CLI
-- Date: July 2026
-- ================================================================

-- ================================================================
-- PART 1: Schema Changes
-- ================================================================

-- 1a. Add offline/hybrid to collection_mode ENUM
ALTER TABLE sudamaseva_subscriptions 
MODIFY COLUMN collection_mode ENUM('recurring','manual','offline','hybrid') NOT NULL DEFAULT 'recurring';

-- 1b. Add cycle column for renewal tracking
ALTER TABLE sudamaseva_subscriptions 
ADD COLUMN cycle INT NOT NULL DEFAULT 1 
COMMENT 'Renewal cycle number (1=original, 2=first renewal, etc.)' 
AFTER collection_mode;

UPDATE sudamaseva_subscriptions SET cycle = 1 WHERE cycle IS NULL OR cycle = 0;


-- ================================================================
-- PART 2: Data Fixes — Correct subscription data
-- ================================================================

-- 2a. Fix total_installments (sub #167 was set to 11 instead of 24)
UPDATE sudamaseva_subscriptions 
SET total_installments = 24, installments_paid = 13 
WHERE id = 167;

-- 2b. Fix installments_paid mismatches
UPDATE sudamaseva_subscriptions SET installments_paid = 24 WHERE id = 23;
UPDATE sudamaseva_subscriptions SET installments_paid = 24 WHERE id = 84;
UPDATE sudamaseva_subscriptions SET installments_paid = 21, total_installments = 21 WHERE id = 186;
UPDATE sudamaseva_subscriptions SET installments_paid = 11 WHERE id = 262;
UPDATE sudamaseva_subscriptions SET installments_paid = 5  WHERE id = 324;
UPDATE sudamaseva_subscriptions SET installments_paid = 0  WHERE id = 367;

-- 2c. Mark fully-paid subscriptions as completed
UPDATE sudamaseva_subscriptions 
SET status = 'completed', end_date = NOW() 
WHERE source = 'migrated' 
  AND status = 'active' 
  AND total_installments > 0 
  AND installments_paid >= total_installments;

-- Expected: ~77 rows affected

-- 2d. Fix migrated recurring subs without Razorpay ID
UPDATE sudamaseva_subscriptions 
SET collection_mode = 'manual' 
WHERE source = 'migrated' 
  AND collection_mode = 'recurring' 
  AND (razorpay_subscription_id IS NULL OR razorpay_subscription_id = '') 
  AND status = 'active';

-- Expected: ~21 rows affected


-- ================================================================
-- PART 3: Verification Queries (run these to confirm)
-- ================================================================

-- Check no remaining issues:
SELECT 'Remaining active-but-fully-paid:' AS check_name, COUNT(*) AS count 
FROM sudamaseva_subscriptions 
WHERE source = 'migrated' AND status = 'active' AND total_installments > 0 AND installments_paid >= total_installments;

SELECT 'Remaining recurring-without-razorpay:' AS check_name, COUNT(*) AS count 
FROM sudamaseva_subscriptions 
WHERE source = 'migrated' AND collection_mode = 'recurring' AND (razorpay_subscription_id IS NULL OR razorpay_subscription_id = '') AND status = 'active';

-- ================================================================
-- END OF DEPLOYMENT SQL
-- ================================================================
