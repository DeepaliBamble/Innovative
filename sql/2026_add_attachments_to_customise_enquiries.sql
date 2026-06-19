-- Adds attachment support to the customise enquiry form.
-- Stores a JSON array of uploaded file paths (relative to the site root), e.g.
--   ["uploads/enquiries/64f0_a1b2c3.pdf","uploads/enquiries/64f0_d4e5f6.jpg"]
-- Run once against each environment (local + production).

ALTER TABLE `customise_enquiries`
  ADD COLUMN `attachments` TEXT DEFAULT NULL AFTER `requirements`;
