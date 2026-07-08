-- Cleanup duplicate pickup locations on production
-- Run on database iskcop35_iskconseshadripuram BEFORE importing the clean data

-- Delete duplicate/typo entries
DELETE FROM panihati_pickup_locations WHERE id IN (
  133,  -- Chikkabanavara/Abbigere
  74,   -- ISKCON Seshadripuram(temple)
  99,   -- Old Madras Rd (Big Bazaar)
  179,  -- Rajaji Nagar(Shankar Math)
  102,  -- Vijay Nagar Maruthi Mandir
  247,  -- JP Nager 3rd Phase
  210,  -- JP Nager 8th Phase
  57,   -- Rajaji Nagar 1 st block
  160   -- Byrati Bande
);

-- Verify no duplicates remain
SELECT name, COUNT(*) as cnt FROM panihati_pickup_locations GROUP BY name HAVING cnt > 1;
