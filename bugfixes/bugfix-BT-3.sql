# This record needs to be deleted. It is likely that on dev/production
# servers, you will be required to use the actual id...

SELECT * FROM regulations
WHERE rg_pp_name = "Use of PHI"
AND rg_pp_number = "PR-103";

# Assuming that rg_ids are consistent across servers, this would be achieved
# by running the following query:
DELETE FROM regulations
WHERE rg_id = 4;
