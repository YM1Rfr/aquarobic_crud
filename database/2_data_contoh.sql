-- Data contoh (opsional). Jalankan SETELAH 1_schema.sql

INSERT INTO lokasi VALUES
    ('LOK01', 'Bogor'),
    ('LOK02', 'Tangerang'),
    ('LOK03', 'BSD')
ON CONFLICT DO NOTHING;

INSERT INTO member (id_member, id_lokasi, nama, alamat, tanggal_lahir, jenis_kelamin, jenis_member, status_keanggotaan) VALUES
    ('MBR001', 'LOK01', 'Budi Santoso', 'Jl. Mawar No. 12, Bogor', '1992-05-14', 'Laki-laki', 'Full', 'Aktif'),
    ('MBR002', 'LOK02', 'Siti Rahma', 'Jl. Anggrek No. 45, Tangerang', '1995-08-22', 'Perempuan', 'Reguler', 'Aktif'),
    ('MBR003', 'LOK03', 'Ahmad Fauzi', 'Jl. Melati No. 8, BSD', '1988-12-03', 'Laki-laki', 'Full', 'Aktif'),
    ('MBR004', 'LOK01', 'Dewi Lestari', 'Jl. Kenanga No. 3, Bogor', '1990-02-17', 'Perempuan', 'Full', 'Aktif'),
    ('MBR005', 'LOK02', 'Rina Marlina', 'Jl. Dahlia No. 21, Tangerang', '1985-11-09', 'Perempuan', 'Reguler', 'Keluar')
ON CONFLICT DO NOTHING;

INSERT INTO pembayaran VALUES
    ('PEM001', 'MBR001', '2026-10', 500000),
    ('PEM002', 'MBR002', '2026-10', 250000),
    ('PEM003', 'MBR003', '2026-10', 500000),
    ('PEM004', 'MBR001', '2026-11', 500000)
ON CONFLICT DO NOTHING;
