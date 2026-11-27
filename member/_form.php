<?php /* variabel: $m (array data), $mode ('tambah'|'edit'), $kunci_jenis (bool) */ ?>
<div class="card" style="width:640px">
    <h2><?= $mode === 'tambah' ? 'Tambah Member Baru' : 'Edit Member ' . h($m['id_member'] ?? '') ?></h2>
    <form method="post" action="<?= h(app_url('/member/' . ($mode === 'tambah' ? 'create.php' : 'edit.php?id=' . rawurlencode($m['id_member'] ?? '')))) ?>">
        <label>Nama lengkap <span class="req">*</span></label>
        <input class="in" name="nama" value="<?= h($m['nama'] ?? '') ?>">

        <label>Alamat <span class="req">*</span></label>
        <textarea class="in" name="alamat" rows="2"><?= h($m['alamat'] ?? '') ?></textarea>

        <div class="half">
            <label>Tanggal lahir <span class="req">*</span></label>
            <input class="in" type="date" name="tanggal_lahir" value="<?= h($m['tanggal_lahir'] ?? '') ?>">
        </div><div class="half">
            <label>Jenis kelamin <span class="req">*</span></label>
            <select class="in" name="jenis_kelamin">
                <option value="">-- pilih --</option>
                <?php foreach (['Laki-laki', 'Perempuan'] as $j): ?>
                    <option <?= ($m['jenis_kelamin'] ?? '') === $j ? 'selected' : '' ?>><?= $j ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="half">
            <label>Lokasi latihan <span class="req">*</span></label>
            <select class="in" name="id_lokasi">
                <option value="">-- pilih --</option>
                <?php foreach (lokasi_semua() as $l): ?>
                    <option value="<?= h($l['id_lokasi']) ?>" <?= ($m['id_lokasi'] ?? '') === $l['id_lokasi'] ? 'selected' : '' ?>>
                        <?= h($l['nama_lokasi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div><div class="half">
            <label>Jenis member <span class="req">*</span></label>
            <?php if (!empty($kunci_jenis)): ?>
                <select class="in" disabled>
                    <?php foreach (['Full', 'Reguler'] as $j): ?>
                        <option <?= ($m['jenis_member'] ?? '') === $j ? 'selected' : '' ?>><?= $j ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="jenis_member" value="<?= h($m['jenis_member'] ?? '') ?>">
                <small class="mute" style="display:block;margin-top:4px;font-size:12px">
                    Terkunci karena sudah memiliki riwayat iuran. Perubahan paket dilakukan saat Tambah Iuran.
                </small>
            <?php else: ?>
                <select class="in" name="jenis_member">
                    <option value="">-- pilih --</option>
                    <?php foreach (['Full', 'Reguler'] as $j): ?>
                        <option <?= ($m['jenis_member'] ?? '') === $j ? 'selected' : '' ?>><?= $j ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <?php if ($mode === 'edit'): ?>
            <label>Status keanggotaan</label>
            <select class="in" name="status_keanggotaan">
                <?php foreach (['Aktif', 'Keluar'] as $s): ?>
                    <option <?= ($m['status_keanggotaan'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <p style="margin-top:20px">
            <button class="btn">Simpan</button>
            <a class="btn gray" href="<?= h(app_url('/member/index.php')) ?>">Batal</a>
        </p>
    </form>
</div>
