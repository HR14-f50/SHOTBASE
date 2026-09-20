-- Existing photo visibility is preserved; only new rows default to public.
ALTER TABLE photos ALTER COLUMN visibility SET DEFAULT 'public';
