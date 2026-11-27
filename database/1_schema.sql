-- Skema fisik Aquarobic Health Club (PostgreSQL) - Fase 3 + kolom status_keanggotaan

CREATE TABLE IF NOT EXISTS lokasi (
    id_lokasi   VARCHAR(10) PRIMARY KEY,
    nama_lokasi VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS pengurus (
    id_pengurus   VARCHAR(10) PRIMARY KEY,
    nama          VARCHAR(100) NOT NULL,
    nomor_kontak  VARCHAR(15) NOT NULL,
    alamat        TEXT NOT NULL,
    tanggal_lahir DATE NOT NULL CHECK (tanggal_lahir <= CURRENT_DATE),
    jenis_kelamin VARCHAR(10) NOT NULL CHECK (jenis_kelamin IN ('Laki-laki', 'Perempuan'))
);

CREATE TABLE IF NOT EXISTS admin (
    id_pengurus VARCHAR(10) PRIMARY KEY REFERENCES pengurus(id_pengurus) ON DELETE RESTRICT,
    id_lokasi   VARCHAR(10) NOT NULL REFERENCES lokasi(id_lokasi) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS pelatih (
    id_pengurus        VARCHAR(10) PRIMARY KEY REFERENCES pengurus(id_pengurus) ON DELETE RESTRICT,
    is_koordinator     BOOLEAN NOT NULL DEFAULT FALSE,
    status_sertifikasi VARCHAR(50) NOT NULL
);

CREATE UNIQUE INDEX IF NOT EXISTS uq_satu_koordinator
    ON pelatih (is_koordinator)
    WHERE is_koordinator;

CREATE TABLE IF NOT EXISTS member (
    id_member          VARCHAR(10) PRIMARY KEY,
    id_lokasi          VARCHAR(10) NOT NULL REFERENCES lokasi(id_lokasi) ON DELETE RESTRICT,
    nama               VARCHAR(100) NOT NULL,
    alamat             TEXT NOT NULL,
    tanggal_lahir      DATE NOT NULL CHECK (tanggal_lahir <= CURRENT_DATE),
    jenis_kelamin      VARCHAR(10) NOT NULL CHECK (jenis_kelamin IN ('Laki-laki', 'Perempuan')),
    jenis_member       VARCHAR(30) NOT NULL CHECK (jenis_member IN ('Full', 'Reguler')),
    status_keanggotaan VARCHAR(10) NOT NULL DEFAULT 'Aktif' CHECK (status_keanggotaan IN ('Aktif', 'Keluar'))
);

CREATE TABLE IF NOT EXISTS jadwal (
    id_jadwal     VARCHAR(10) PRIMARY KEY,
    id_lokasi     VARCHAR(10) NOT NULL REFERENCES lokasi(id_lokasi) ON DELETE RESTRICT,
    id_pengurus   VARCHAR(10) NOT NULL REFERENCES pelatih(id_pengurus) ON DELETE RESTRICT,
    nama_kegiatan VARCHAR(100) NOT NULL,
    hari          VARCHAR(10) NOT NULL CHECK (hari IN ('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')),
    tanggal       DATE NOT NULL,
    waktu         TIME NOT NULL,
    UNIQUE (id_pengurus, tanggal, waktu)
);

CREATE TABLE IF NOT EXISTS peralatan (
    id_peralatan   VARCHAR(10) PRIMARY KEY,
    id_lokasi      VARCHAR(10) NOT NULL REFERENCES lokasi(id_lokasi) ON DELETE RESTRICT,
    nama_peralatan VARCHAR(100) NOT NULL,
    kondisi        VARCHAR(50) NOT NULL CHECK (kondisi IN ('Baik', 'Rusak Sebagian/Sedang', 'Rusak Total'))
);

CREATE TABLE IF NOT EXISTS pemeliharaan (
    id_peralatan         VARCHAR(10) REFERENCES peralatan(id_peralatan) ON DELETE RESTRICT,
    tanggal_pemeliharaan DATE NOT NULL,
    keterangan_tindakan  TEXT NOT NULL,
    PRIMARY KEY (id_peralatan, tanggal_pemeliharaan)
);

CREATE TABLE IF NOT EXISTS sponsor (
    id_sponsor   VARCHAR(10) PRIMARY KEY,
    nama_sponsor VARCHAR(100) NOT NULL,
    nama_event   VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS pembayaran (
    id_pembayaran VARCHAR(10) PRIMARY KEY,
    id_member     VARCHAR(10) NOT NULL REFERENCES member(id_member) ON DELETE RESTRICT,
    periode_bulan CHAR(7) NOT NULL CHECK (periode_bulan ~ '^[0-9]{4}-(0[1-9]|1[0-2])$'),
    nominal_iuran DECIMAL(12,2) NOT NULL CHECK (nominal_iuran > 0),
    UNIQUE (id_member, periode_bulan)
);

CREATE TABLE IF NOT EXISTS transaksi_kas (
    id_transaksi      VARCHAR(10) PRIMARY KEY,
    id_pengurus       VARCHAR(10) NOT NULL REFERENCES admin(id_pengurus) ON DELETE RESTRICT,
    id_sponsor        VARCHAR(10) REFERENCES sponsor(id_sponsor) ON DELETE RESTRICT,
    id_pembayaran     VARCHAR(10) UNIQUE REFERENCES pembayaran(id_pembayaran) ON DELETE RESTRICT,
    jenis_transaksi   VARCHAR(20) NOT NULL CHECK (jenis_transaksi IN ('Pemasukan', 'Pengeluaran')),
    nominal           DECIMAL(12,2) NOT NULL CHECK (nominal > 0),
    tanggal           DATE NOT NULL,
    metode_pembayaran VARCHAR(50) NOT NULL,
    kuitansi          VARCHAR(100) NOT NULL,
    CHECK (id_sponsor IS NULL OR id_pembayaran IS NULL),
    CHECK ((id_sponsor IS NULL AND id_pembayaran IS NULL) OR jenis_transaksi = 'Pemasukan')
);

CREATE TABLE IF NOT EXISTS kehadiran (
    id_member    VARCHAR(10) REFERENCES member(id_member) ON DELETE RESTRICT,
    id_jadwal    VARCHAR(10) REFERENCES jadwal(id_jadwal) ON DELETE RESTRICT,
    status_hadir VARCHAR(20) NOT NULL CHECK (status_hadir IN ('Hadir', 'Izin', 'Alpha')),
    PRIMARY KEY (id_member, id_jadwal)
);

CREATE TABLE IF NOT EXISTS pemeriksaan_kesehatan (
    id_member         VARCHAR(10) REFERENCES member(id_member) ON DELETE RESTRICT,
    tanggal_periksa   DATE NOT NULL,
    hasil_pemeriksaan TEXT NOT NULL,
    PRIMARY KEY (id_member, tanggal_periksa)
);
