<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

function getBobotJenisNasabah(string $jenis): int
{
    $jenis = strtolower($jenis);

    if ($jenis === 'rekomendasi') {
        return 100;
    }
    if ($jenis === 'baru') {
        return 70;
    }
    if ($jenis === 'bermasalah') {
        return 50;
    }
    if ($jenis === 'blacklist') {
        return 10;
    }

    return 0;
}

function getBobotPenghasilan(int $gaji): int
{
    if ($gaji > 3000000) {
        return 100;
    }
    if ($gaji === 3000000) {
        return 60;
    }
    if ($gaji >= 1000000) {
        return 40;
    }

    return 20;
}

function getBobotJaminan(string $jaminan): int
{
    $jaminan = strtolower($jaminan);

    if (str_contains($jaminan, 'sertifikat') || str_contains($jaminan, 'tanah')) {
        return 100;
    }
    if (str_contains($jaminan, 'mobil')) {
        return 60;
    }
    if (str_contains($jaminan, 'motor')) {
        return 50;
    }

    return 0;
}

function getBobotTanggungan(int $tanggungan): int
{
    if ($tanggungan === 1) {
        return 100;
    }
    if ($tanggungan === 2) {
        return 60;
    }
    if ($tanggungan === 3) {
        return 50;
    }

    return 20;
}

function getBobotPekerjaan(string $pekerjaan): int
{
    $pekerjaan = strtolower($pekerjaan);

    if (str_contains($pekerjaan, 'sendiri')) {
        return 100;
    }
    if (str_contains($pekerjaan, 'keluarga') || str_contains($pekerjaan, 'join')) {
        return 70;
    }
    if (str_contains($pekerjaan, 'karyawan')) {
        return 20;
    }

    return 0;
}

function inferKelayakan(int $pengajuan, float $skor): array
{
    if ($pengajuan <= 3000000) {
        if ($skor > 28) {
            return ['LAYAK', 1];
        }
        if ($skor == 28.0) {
            return ['DIPERTIMBANGKAN', 2];
        }
        return ['BELUM LAYAK', 3];
    }

    if ($pengajuan <= 7000000) {
        if ($skor > 58) {
            return ['LAYAK', 4];
        }
        if ($skor == 58.0) {
            return ['DIPERTIMBANGKAN', 5];
        }
        return ['BELUM LAYAK', 6];
    }

    if ($pengajuan <= 15000000) {
        if ($skor > 78) {
            return ['LAYAK', 7];
        }
        if ($skor == 78.0) {
            return ['DIPERTIMBANGKAN', 8];
        }
        return ['BELUM LAYAK', 9];
    }

    if ($skor > 92) {
        return ['LAYAK', 10];
    }
    if ($skor == 92.0) {
        return ['DIPERTIMBANGKAN', 11];
    }
    return ['BELUM LAYAK', 12];
}

$allowedOptions = [
    'jenis_nasabah' => ['Rekomendasi', 'Baru', 'Bermasalah', 'Blacklist'],
    'jaminan' => ['Sertifikat', 'BPKB Mobil', 'BPKB Motor', 'Tanah'],
    'pekerjaan' => ['Karyawan', 'Karyawan Kontrak', 'Usaha Sendiri', 'Usaha Keluarga', 'Usaha Kecil'],
];

$data = [
    'nama' => trim($_POST['nama'] ?? ''),
    'jenis_nasabah' => trim($_POST['jenis_nasabah'] ?? ''),
    'penghasilan' => (int) ($_POST['penghasilan'] ?? 0),
    'jaminan' => trim($_POST['jaminan'] ?? ''),
    'tanggungan' => (int) ($_POST['tanggungan'] ?? 0),
    'pekerjaan' => trim($_POST['pekerjaan'] ?? ''),
    'pengajuan' => (int) ($_POST['pengajuan'] ?? 0),
];

$queryData = [
    'nama' => $data['nama'],
    'jenis_nasabah' => $data['jenis_nasabah'],
    'penghasilan' => $data['penghasilan'],
    'jaminan' => $data['jaminan'],
    'tanggungan' => $data['tanggungan'],
    'pekerjaan' => $data['pekerjaan'],
    'pengajuan' => $data['pengajuan'],
];

if (
    $data['nama'] === '' ||
    !in_array($data['jenis_nasabah'], $allowedOptions['jenis_nasabah'], true) ||
    $data['penghasilan'] < 0 ||
    !in_array($data['jaminan'], $allowedOptions['jaminan'], true) ||
    $data['tanggungan'] < 0 ||
    !in_array($data['pekerjaan'], $allowedOptions['pekerjaan'], true) ||
    $data['pengajuan'] <= 0
) {
    $queryData['error'] = 'Data tidak valid. Pastikan semua field sudah diisi dengan benar.';
    header('Location: index.php?' . http_build_query($queryData));
    exit;
}

$bobotJenis = getBobotJenisNasabah($data['jenis_nasabah']);
$bobotPenghasilan = getBobotPenghasilan($data['penghasilan']);
$bobotJaminan = getBobotJaminan($data['jaminan']);
$bobotTanggungan = getBobotTanggungan($data['tanggungan']);
$bobotPekerjaan = getBobotPekerjaan($data['pekerjaan']);

$skorTotal = $bobotJenis + $bobotPenghasilan + $bobotJaminan + $bobotTanggungan + $bobotPekerjaan;
$skorRataRata = $skorTotal / 5;
[$keputusan, $rule] = inferKelayakan($data['pengajuan'], $skorRataRata);

$statusClass = $keputusan === 'LAYAK'
    ? 'status-layak'
    : ($keputusan === 'DIPERTIMBANGKAN' ? 'status-warning' : 'status-tidak');

$databaseMessage = '';

try {
    $connection = getConnection();
    $statement = $connection->prepare(
        'INSERT INTO analisis_kredit
        (nama, jenis_nasabah, penghasilan, jaminan, tanggungan, pekerjaan, pengajuan, skor_rata_rata, rule_keputusan, keputusan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->bind_param(
        'ssisisidis',
        $data['nama'],
        $data['jenis_nasabah'],
        $data['penghasilan'],
        $data['jaminan'],
        $data['tanggungan'],
        $data['pekerjaan'],
        $data['pengajuan'],
        $skorRataRata,
        $rule,
        $keputusan
    );
    $statement->execute();
    $databaseMessage = 'Hasil analisis berhasil disimpan ke database.';
} catch (Throwable $exception) {
    $databaseMessage = 'Hasil analisis tampil, tetapi data belum tersimpan ke database.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Analisis Kredit</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="page">
        <section class="card">
            <div class="hero">
                <p class="eyebrow">Hasil Analisis</p>
                <h1>Penentuan Kelayakan Kredit</h1>
                <p class="description">
                    Keputusan di bawah ini dihitung langsung dari rule.
                </p>
            </div>

            <div class="result-box <?php echo $statusClass; ?>">
                <h2><?php echo htmlspecialchars($keputusan, ENT_QUOTES, 'UTF-8'); ?></h2>
                <p><?php echo 'Rule ' . htmlspecialchars((string) $rule, ENT_QUOTES, 'UTF-8'); ?> digunakan untuk pengajuan <?php echo htmlspecialchars(formatRupiah($data['pengajuan']), ENT_QUOTES, 'UTF-8'); ?>.</p>
            </div>

            <div class="alert <?php echo str_contains($databaseMessage, 'berhasil') ? 'alert-success' : 'alert-error'; ?>">
                <?php echo htmlspecialchars($databaseMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>

            <div class="summary">
                <h3>Ringkasan Hasil</h3>
                <dl class="summary-list">
                    <div>
                        <dt>Nama Nasabah</dt>
                        <dd><?php echo htmlspecialchars($data['nama'], ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt>Jenis Nasabah</dt>
                        <dd><?php echo htmlspecialchars($data['jenis_nasabah'], ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt>Penghasilan</dt>
                        <dd><?php echo htmlspecialchars(formatRupiah($data['penghasilan']), ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt>Jaminan</dt>
                        <dd><?php echo htmlspecialchars($data['jaminan'], ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt>Jumlah Tanggungan</dt>
                        <dd><?php echo htmlspecialchars((string) $data['tanggungan'], ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt>Pekerjaan</dt>
                        <dd><?php echo htmlspecialchars($data['pekerjaan'], ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt>Jumlah Pengajuan</dt>
                        <dd><?php echo htmlspecialchars(formatRupiah($data['pengajuan']), ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt>Skor Rata-rata Bobot</dt>
                        <dd><?php echo htmlspecialchars(number_format($skorRataRata, 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                </dl>
            </div>

            <div class="summary score-box">
                <h3>Rincian Bobot</h3>
                <dl class="summary-list">
                    <div>
                        <dt>Bobot Jenis Nasabah</dt>
                        <dd><?php echo htmlspecialchars((string) $bobotJenis, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt>Bobot Penghasilan</dt>
                        <dd><?php echo htmlspecialchars((string) $bobotPenghasilan, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt>Bobot Jaminan</dt>
                        <dd><?php echo htmlspecialchars((string) $bobotJaminan, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt>Bobot Tanggungan</dt>
                        <dd><?php echo htmlspecialchars((string) $bobotTanggungan, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt>Bobot Pekerjaan</dt>
                        <dd><?php echo htmlspecialchars((string) $bobotPekerjaan, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt>Skor Total</dt>
                        <dd><?php echo htmlspecialchars((string) $skorTotal, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                </dl>
            </div>

            <div class="actions">
                <a href="index.php" class="button-secondary">Analisis Lagi</a>
                <a href="index.php?message=<?php echo urlencode($databaseMessage); ?>" class="button-secondary">Lihat Riwayat</a>
            </div>
        </section>
    </main>
</body>
</html>
