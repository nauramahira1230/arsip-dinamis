CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TABLE IF NOT EXISTS kegiatan (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    nama_kegiatan varchar(255) NOT NULL,
    tahun int NOT NULL,
    jenis_naskah varchar(255) NOT NULL,
    target_lembar int NOT NULL CHECK (target_lembar > 0),
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE digitized_archives
    ADD COLUMN IF NOT EXISTS kegiatan_id uuid REFERENCES kegiatan(id) ON DELETE RESTRICT;

CREATE INDEX IF NOT EXISTS digitized_archives_kegiatan_id_idx
    ON digitized_archives(kegiatan_id);