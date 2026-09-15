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

ALTER TABLE digitized_archives
    ADD COLUMN IF NOT EXISTS no text,
    ADD COLUMN IF NOT EXISTS kode_klasifikasi text,
    ADD COLUMN IF NOT EXISTS unit_pencipta text,
    ADD COLUMN IF NOT EXISTS unit_pengolah text,
    ADD COLUMN IF NOT EXISTS jenis_naskah text,
    ADD COLUMN IF NOT EXISTS kategori_arsip text,
    ADD COLUMN IF NOT EXISTS nama_berkas text,
    ADD COLUMN IF NOT EXISTS uraian_arsip text,
    ADD COLUMN IF NOT EXISTS jumlah_lembar text,
    ADD COLUMN IF NOT EXISTS kurun_waktu text,
    ADD COLUMN IF NOT EXISTS semula text,
    ADD COLUMN IF NOT EXISTS menjadi text,
    ADD COLUMN IF NOT EXISTS alat_scan text,
    ADD COLUMN IF NOT EXISTS waktu_scan text,
    ADD COLUMN IF NOT EXISTS tingkat_perkembangan text,
    ADD COLUMN IF NOT EXISTS no_sampul text,
    ADD COLUMN IF NOT EXISTS no_item text,
    ADD COLUMN IF NOT EXISTS boks text,
    ADD COLUMN IF NOT EXISTS rak text,
    ADD COLUMN IF NOT EXISTS ro text,
    ADD COLUMN IF NOT EXISTS lokasi text,
    ADD COLUMN IF NOT EXISTS status_authentication text,
    ADD COLUMN IF NOT EXISTS created_at timestamp NULL,
    ADD COLUMN IF NOT EXISTS updated_at timestamp NULL,
    ADD COLUMN IF NOT EXISTS kegiatan_id uuid;

ALTER TABLE digitized_archives
    ALTER COLUMN jumlah_lembar TYPE text USING jumlah_lembar::text,
    ALTER COLUMN kurun_waktu TYPE text USING kurun_waktu::text,
    ALTER COLUMN no_sampul TYPE text USING no_sampul::text,
    ALTER COLUMN waktu_scan TYPE text USING waktu_scan::text;