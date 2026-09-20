ALTER TABLE photos DROP COLUMN title;
ALTER TABLE projects ADD COLUMN cover_photo_id INT UNSIGNED NULL,
  ADD CONSTRAINT fk_project_cover FOREIGN KEY (cover_photo_id) REFERENCES photos(id) ON DELETE SET NULL;
