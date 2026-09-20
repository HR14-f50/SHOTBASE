-- 公開範囲を「公開」「非公開」の2種類に統一
START TRANSACTION;

UPDATE projects SET visibility = 'private' WHERE visibility = 'draft';
UPDATE photos SET visibility = 'private' WHERE visibility = 'draft';

ALTER TABLE projects
  MODIFY visibility ENUM('public', 'private') NOT NULL DEFAULT 'private';
ALTER TABLE photos
  MODIFY visibility ENUM('public', 'private') NOT NULL DEFAULT 'public';

COMMIT;
