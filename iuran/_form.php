<?php /* variabel: $mem (member), $p (array iuran) */ ?>
<div class="card" style="width:560px">
    <h2>Tambah Iuran</h2>
    <p class="mute" style="margin:0">
        Member: <b style="color:#1e293b"><?= h($mem['nama']) ?></b> (<?= h($mem['id_member']) ?>) · Paket saat ini: <?= h($mem['jenis_member']) ?>
    </p>

    <form method="post" action="<?= h(app_url('/iuran/create.php?id_member=' . rawurlencode($mem['id_member']))) ?>">
        <label>Periode bulan <span class="req">*</span></label>
        <input class="in" type="month" name="periode_bulan" value="<?= h($p['periode_bulan'] ?? '') ?>" placeholder="YYYY-MM">

        <label>Paket iuran (Jenis member) <span class="req">*</span></label>
        <select class="in" name="jenis_member" id="jenis_member">
            <?php foreach (['Full', 'Reguler'] as $j): ?>
                <option value="<?= $j ?>" <?= ($p['jenis_member'] ?? $mem['jenis_member']) === $j ? 'selected' : '' ?>>
                    <?= $j ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Nominal iuran (Rp) <span class="req">*</span></label>
        <input class="in" type="number" name="nominal_iuran" id="nominal_iuran" min="1" step="1" value="<?= h(isset($p['nominal_iuran']) ? (int) $p['nominal_iuran'] : '') ?>">
        <p class="mute" style="font-size:12.5px;margin:6px 0 0">
            Acuan: Full Rp500.000, Reguler Rp250.000 (terisi otomatis sesuai paket terpilih, dapat disesuaikan).
        </p>

        <p style="margin-top:20px">
            <button class="btn">Simpan</button>
            <a class="btn gray" href="<?= h(app_url('/member/detail.php?id=' . rawurlencode($mem['id_member']))) ?>">Batal</a>
        </p>
    </form>
</div>

<script>
document.getElementById('jenis_member').addEventListener('change', function () {
    var nominalInput = document.getElementById('nominal_iuran');
    nominalInput.value = this.value === 'Full' ? 500000 : 250000;
});
</script>
