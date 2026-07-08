-- Bhakti Sadans and Pickup Locations (clean data from prod local copy)
-- Run on production database iskcop35_iskconseshadripuram
-- First run panihati_pickups_cleanup.sql to remove duplicates

-- =============================================
-- panihati_bhakti_sadans (23 rows)
-- =============================================
INSERT INTO `panihati_bhakti_sadans` (`id`, `name`, `is_active`, `created_at`) VALUES
(1, 'Anekal', 1, '2026-07-01 05:54:46'),
(2, 'Annapurnishwara', 1, '2026-07-01 05:54:46'),
(3, 'HAL', 1, '2026-07-01 05:54:46'),
(4, 'HSR Layout', 1, '2026-07-01 05:54:46'),
(5, 'JP Nagar', 1, '2026-07-01 05:54:46'),
(6, 'Kudlu(Electronics City)', 1, '2026-07-01 05:54:46'),
(7, 'Nelamangala', 1, '2026-07-01 05:54:46'),
(8, 'Raja Rajeswari Nagar', 1, '2026-07-01 05:54:46'),
(9, 'RT Nagar', 1, '2026-07-01 05:54:46'),
(10, 'Sahakara Nagar', 1, '2026-07-01 05:54:46'),
(11, 'Sarjapur Road', 1, '2026-07-01 05:54:46'),
(12, 'Seshadripuram', 1, '2026-07-01 05:54:46'),
(13, 'Nagarbhavi', 1, '2026-07-01 05:54:46'),
(14, 'Yelahanka', 1, '2026-07-01 05:54:46'),
(15, 'Hulimavu', 1, '2026-07-01 05:54:46'),
(16, 'Byrathi Bande', 1, '2026-07-01 05:54:46'),
(17, 'Vijayanagar', 1, '2026-07-01 05:54:46'),
(18, 'Hoskote', 1, '2026-07-01 05:54:46'),
(19, 'Haralur', 1, '2026-07-01 05:54:46'),
(20, 'Prakashnagar', 1, '2026-07-01 05:54:46'),
(21, 'Mahadevapura', 1, '2026-07-01 05:54:46'),
(164, 'Seshadripuram (Bengali)', 1, '2026-07-01 05:59:26'),
(350, 'RT Nagar (Hindi group)', 1, '2026-07-01 05:59:29')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `is_active` = VALUES(`is_active`);

-- =============================================
-- panihati_pickup_locations (54 rows, duplicates removed)
-- =============================================
INSERT INTO `panihati_pickup_locations` (`id`, `name`, `is_active`, `created_at`) VALUES
(1, 'Abbigere', 1, '2026-07-01 05:54:46'),
(2, 'Ayyappa Nagar', 1, '2026-07-01 05:54:46'),
(3, 'BEL Circle', 1, '2026-07-01 05:54:46'),
(4, 'BHEL Mysore Road', 1, '2026-07-01 05:54:46'),
(5, 'BTM Layout', 1, '2026-07-01 05:54:46'),
(6, 'Bapuji Nagar', 1, '2026-07-01 05:54:46'),
(7, 'Basaveshwara Nagar', 1, '2026-07-01 05:54:46'),
(8, 'Budigere Cross', 1, '2026-07-01 05:54:46'),
(9, 'Byarahalli', 1, '2026-07-01 05:54:46'),
(10, 'Byrathi Bande', 1, '2026-07-01 05:54:46'),
(11, 'Chikkabanavara / Abbigere', 1, '2026-07-01 05:54:46'),
(12, 'Domlur', 1, '2026-07-01 05:54:46'),
(13, 'GM Palya', 1, '2026-07-01 05:54:46'),
(14, 'HAL', 1, '2026-07-01 05:54:46'),
(15, 'HSR Layout', 1, '2026-07-01 05:54:46'),
(16, 'Hanumantha Nagar', 1, '2026-07-01 05:54:46'),
(17, 'Hebbal', 1, '2026-07-01 05:54:46'),
(18, 'Hoskote', 1, '2026-07-01 05:54:46'),
(19, 'Hulimavu', 1, '2026-07-01 05:54:46'),
(20, 'JP Nagar 3rd Phase', 1, '2026-07-01 05:54:46'),
(21, 'JP Nagar 8th Phase', 1, '2026-07-01 05:54:46'),
(22, 'KR Puram', 1, '2026-07-01 05:54:46'),
(23, 'Kudlu', 1, '2026-07-01 05:54:46'),
(24, 'Kumarswamy Layout', 1, '2026-07-01 05:54:46'),
(25, 'Laggere', 1, '2026-07-01 05:54:46'),
(26, 'Magadi Road', 1, '2026-07-01 05:54:46'),
(27, 'Marathahalli', 1, '2026-07-01 05:54:46'),
(28, 'Mico Layout', 1, '2026-07-01 05:54:46'),
(29, 'Muddenapalya', 1, '2026-07-01 05:54:46'),
(30, 'Mysore Road (Nayandahalli)', 1, '2026-07-01 05:54:46'),
(31, 'Nagarbhavi', 1, '2026-07-01 05:54:46'),
(32, 'Nelamangala', 1, '2026-07-01 05:54:46'),
(33, 'Old Madras Road (Big Bazaar)', 1, '2026-07-01 05:54:46'),
(34, 'Peenya Jalahalli Cross', 1, '2026-07-01 05:54:46'),
(35, 'RT Nagar', 1, '2026-07-01 05:54:46'),
(36, 'Rajajinagar 1st Block', 1, '2026-07-01 05:54:46'),
(37, 'Rajajinagar (Shankar Math)', 1, '2026-07-01 05:54:46'),
(38, 'Rajarajeswari Medical College', 1, '2026-07-01 05:54:46'),
(39, 'Ramamurthy Nagar', 1, '2026-07-01 05:54:46'),
(40, 'Sahakarnagar', 1, '2026-07-01 05:54:46'),
(41, 'Sarjapur Road', 1, '2026-07-01 05:54:46'),
(42, 'ISKCON Seshadripuram (Temple)', 1, '2026-07-01 05:54:46'),
(43, 'Shankar Math', 1, '2026-07-01 05:54:46'),
(44, 'Sivanahalli', 1, '2026-07-01 05:54:46'),
(45, 'Vijay Nagar (Maruthi Mandir)', 1, '2026-07-01 05:54:46'),
(46, 'Wilson Garden', 1, '2026-07-01 05:54:46'),
(47, 'West of Chord Road (Warrier Bakery)', 1, '2026-07-01 05:54:46'),
(48, 'Yelanka New Town', 1, '2026-07-01 05:54:46'),
(49, 'Yeshwanthpur', 1, '2026-07-01 05:54:46'),
(50, 'Haralur', 1, '2026-07-01 05:54:46'),
(51, 'RajaRajeswari Nagar', 1, '2026-07-01 05:54:46'),
(52, 'Sanjay Nagar', 1, '2026-07-01 05:54:46'),
(53, 'Prakashnagar', 1, '2026-07-01 05:54:46'),
(54, 'Mahadevapura', 1, '2026-07-01 05:54:46')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `is_active` = VALUES(`is_active`);
