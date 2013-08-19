-- --------------------------------------------------------

-- 
-- Update table `cards`
-- add column for availability flag of a photo

ALTER TABLE  `cards` ADD  `active` INT NOT NULL DEFAULT  '1';