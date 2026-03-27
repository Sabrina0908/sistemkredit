<?php
require_once __DIR__ . '/config.php';

$options = [
    'jenis_nasabah' => [
        'Rekomendasi',
        'Baru',
        'Bermasalah',
        'Blacklist',
    ],
    'jaminan' => [
        'Sertifikat',
        'BPKB Mobil',
        'BPKB Motor',
        'Tanah',
    ],
    'pekerjaan' => [
        'Karyawan',
        'Karyawan Kontrak',
        'Usaha Sendiri',
        'Usaha Keluarga',
        'Usaha Kecil',
    ],
];

$old = [
    'nama' => $_GET['nama'] ?? '',
    'jenis_nasabah' => $_GET['jenis_nasabah'] ?? '',
    'penghasilan' => $_GET['penghasilan'] ?? '',
    'jaminan' => $_GET['jaminan'] ?? '',
    'tanggungan' => $_GET['tanggungan'] ?? '',
    'pekerjaan' => $_GET['pekerjaan'] ?? '',
    'pengajuan' => $_GET['pengajuan'] ?? '',
];

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Penentuan Kelayakan Pemberian Kredit</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="page">
        <section class="card">
            <div class="hero">
                <h1>Sistem Penentuan Kelayakan Pemberian Kredit</h1>
                <p class="description">
                    Sistem ini menghitung bobot, skor rata-rata, rule keputusan, dan status kelayakan kredit nasabah.
                </p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="proses.php" method="post" class="form-grid">
                <div class="field field-full">
                    <label for="nama">Nama Nasabah</label>
                    <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($old['nama'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Masukkan nama nasabah" required>
                </div>

                <div class="field">
                    <label for="jenis_nasabah">Jenis Nasabah</label>
                    <select id="jenis_nasabah" name="jenis_nasabah" required>
                        <option value="">Pilih jenis nasabah</option>
                        <?php foreach ($options['jenis_nasabah'] as $value): ?>
                            <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $old['jenis_nasabah'] === $value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="penghasilan">Penghasilan per Bulan</label>
                    <input type="number" id="penghasilan" name="penghasilan" value="<?php echo htmlspecialchars($old['penghasilan'], ENT_QUOTES, 'UTF-8'); ?>" min="0" step="1" placeholder="Contoh: 3000000" required>
                </div>

                <div class="field">
                    <label for="jaminan">Jaminan</label>
                    <select id="jaminan" name="jaminan" required>
                        <option value="">Pilih jaminan</option>
                        <?php foreach ($options['jaminan'] as $value): ?>
                            <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $old['jaminan'] === $value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="tanggungan">Jumlah Tanggungan</label>
                    <input type="number" id="tanggungan" name="tanggungan" value="<?php echo htmlspecialchars($old['tanggungan'], ENT_QUOTES, 'UTF-8'); ?>" min="0" step="1" placeholder="Contoh: 2" required>
                </div>

                <div class="field">
                    <label for="pekerjaan">Pekerjaan</label>
                    <select id="pekerjaan" name="pekerjaan" required>
                        <option value="">Pilih pekerjaan</option>
                        <?php foreach ($options['pekerjaan'] as $value): ?>
                            <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $old['pekerjaan'] === $value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="pengajuan">Jumlah Pengajuan Kredit</label>
                    <input type="number" id="pengajuan" name="pengajuan" value="<?php echo htmlspecialchars($old['pengajuan'], ENT_QUOTES, 'UTF-8'); ?>" min="0" step="1" placeholder="Contoh: 5000000" required>
                </div>

                <button type="submit" class="button-primary">Analisis Sekarang</button>
            </form>
        </section>
    </main>
</body>
</html>
