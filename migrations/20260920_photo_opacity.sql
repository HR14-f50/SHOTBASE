ALTER TABLE photos
  DROP COLUMN watermark_weight,
  ADD COLUMN watermark_opacity SMALLINT UNSIGNED NULL AFTER watermark_size;
