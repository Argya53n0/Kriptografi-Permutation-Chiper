<?php
require 'Permutation.php';
require 'FileHandler.php';

$cipher = new Permutation();
$errors = [];

// ── Helper: validasi kunci ──────────────────────────────────────────
function validateKey(string $key): ?string {
    if (empty($key))          return 'Kunci tidak boleh kosong.';
    if (!ctype_alpha($key))   return 'Kunci hanya boleh berisi huruf alfabet.';
    if (strlen($key) < 2)     return 'Kunci minimal 2 huruf.';
    return null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$action    = $_POST['action']    ?? 'encrypt';
$inputType = $_POST['inputType'] ?? 'text';
$key       = strtoupper(trim($_POST['key'] ?? ''));

// ── Validasi kunci ──────────────────────────────────────────────────
$keyError = validateKey($key);
if ($keyError) {
    // Redirect balik dengan pesan error (simpan di session jika perlu, atau tampilkan langsung)
    $errors[] = $keyError;
}

// ── PROSES FILE ─────────────────────────────────────────────────────
if (!$errors && $inputType === 'file') {
    if (!isset($_FILES['inputFile']) || $_FILES['inputFile']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Gagal mengupload file. Pastikan file dipilih dan tidak melebihi 50MB.';
    } else {
        $tmpPath      = $_FILES['inputFile']['tmp_name'];
        $originalName = $_FILES['inputFile']['name'];

        if ($action === 'encrypt') {
            $encryptedContent = FileHandler::encryptFile($tmpPath, $originalName, $key, $cipher);
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="encrypted_' . pathinfo($originalName, PATHINFO_FILENAME) . '.dat"');
            header('Content-Length: ' . strlen($encryptedContent));
            echo $encryptedContent;
            exit;
        } else {
            $result = FileHandler::decryptFile($tmpPath, $key, $cipher);
            if (!$result) {
                $errors[] = 'Format file tidak valid. Pastikan file adalah hasil enkripsi dari program ini dengan kunci yang benar.';
            } else {
                $originalExt   = $result['ext'];
                $decryptedData = $result['data'];
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="decrypted_file.' . $originalExt . '"');
                header('Content-Length: ' . strlen($decryptedData));
                echo $decryptedData;
                exit;
            }
        }
    }
}

// ── PROSES TEKS ─────────────────────────────────────────────────────
$resultData = null;

if (!$errors && $inputType === 'text') {
    $text = $_POST['inputText'] ?? '';

    if (empty(trim($text))) {
        $errors[] = 'Teks input tidak boleh kosong.';
    } else {
        if ($action === 'encrypt') {
            $raw        = $cipher->encrypt($text, $key);
            $resultHex  = bin2hex($raw);
            $display    = preg_replace('/[^\x20-\x7E]/', '', $raw); // strip non-printable untuk display
            $formatted5 = trim(chunk_split(str_replace(' ', '', $display), 5, ' '));
            $noSpace    = str_replace(' ', '', $display);
            $table      = $cipher->buildTable($text, $key);
            $colNums    = $cipher->getColumnNumbers($key);
            $keyUpper   = strtoupper($key);

            $resultData = [
                'type'       => 'encrypt',
                'key'        => $keyUpper,
                'colNums'    => $colNums,
                'table'      => $table,
                'noSpace'    => $noSpace,
                'formatted5' => $formatted5,
                'hex'        => $resultHex,
                'inputText'  => $text,
            ];
        } else {
            // Dekripsi: terima input hex atau raw teks
            $hexText = trim($text);
            $rawText = ctype_xdigit($hexText) ? hex2bin($hexText) : $text;
            $result  = $cipher->decrypt($rawText, $key);

            $resultData = [
                'type'      => 'decrypt',
                'key'       => $key,
                'plaintext' => $result,
                'inputHex'  => $hexText,
            ];
        }
    }
}

// ── Siapkan variabel untuk tampilan ────────────────────────────────
$isEncrypt = ($action === 'encrypt');
$pageTitle = $isEncrypt ? 'Hasil Enkripsi' : 'Hasil Dekripsi';
$pageIcon  = $isEncrypt ? '🔒' : '🔓';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — Permutation Cipher</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="page-wrapper">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-icon">🔐</span>
            <span class="logo-text">PermCipher</span>
        </div>
        <nav class="sidebar-nav">
            <span class="nav-label">Navigasi</span>
            <a href="index.php" class="nav-link">⚙️ Form Enkripsi/Dekripsi</a>
        </nav>
        <div class="sidebar-info">
            <h3>Info Proses</h3>
            <?php if ($resultData): ?>
            <div class="info-stat">
                <span class="stat-label">Mode</span>
                <span class="stat-val"><?= $isEncrypt ? 'Enkripsi' : 'Dekripsi' ?></span>
            </div>
            <div class="info-stat">
                <span class="stat-label">Kunci</span>
                <span class="stat-val mono"><?= htmlspecialchars($key) ?></span>
            </div>
            <div class="info-stat">
                <span class="stat-label">Panjang Kunci</span>
                <span class="stat-val"><?= strlen($key) ?> kolom</span>
            </div>
            <?php if ($isEncrypt && $resultData): ?>
            <div class="info-stat">
                <span class="stat-label">Panjang Input</span>
                <span class="stat-val"><?= strlen($resultData['inputText']) ?> karakter</span>
            </div>
            <div class="info-stat">
                <span class="stat-label">Jumlah Baris</span>
                <span class="stat-val"><?= count($resultData['table']) ?> baris</span>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            <div class="info-box">
                <strong>Permutation Cipher</strong>
                <p>Cipher transposisi kolom — posisi karakter diacak, bukan karakternya.</p>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content">
        <div class="content-header">
            <h1><?= $pageIcon ?> <?= htmlspecialchars($pageTitle) ?></h1>
            <p>Kunci: <code class="inline-code"><?= htmlspecialchars($key) ?></code></p>
        </div>

        <?php if ($errors): ?>
        <!-- ── Error ── -->
        <div class="form-card error-card">
            <div class="card-header">
                <span class="card-step error-step">!</span>
                <h2>Terjadi Kesalahan</h2>
            </div>
            <ul class="error-list">
                <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="index.php" class="back-link">← Kembali dan perbaiki</a>
        </div>

        <?php elseif ($resultData && $isEncrypt): ?>
        <!-- ── Hasil Enkripsi ── -->

        <!-- Tabel Permutasi -->
        <div class="form-card">
            <div class="card-header">
                <span class="card-step">📊</span>
                <h2>Tabel Permutasi</h2>
            </div>
            <p class="card-desc">Plaintext ditulis baris per baris, lalu kolom dibaca sesuai urutan kunci.</p>
            <div class="perm-table-wrap">
                <table class="perm-table">
                    <thead>
                        <tr class="perm-head-key">
                            <?php foreach (str_split($resultData['key']) as $ch): ?>
                            <th class="perm-th"><?= htmlspecialchars($ch) ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr class="perm-head-num">
                            <?php foreach ($resultData['colNums'] as $n): ?>
                            <th class="perm-th-num"><?= $n ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultData['table'] as $row): ?>
                        <tr>
                            <?php foreach ($row as $cell): ?>
                            <td class="perm-td <?= $cell === '*' ? 'perm-pad' : '' ?>">
                                <?= $cell === '*' ? '<span title="Padding">*</span>' : htmlspecialchars($cell) ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="table-note">* = karakter padding (otomatis dihapus saat dekripsi)</p>
        </div>

        <!-- Hasil -->
        <div class="form-card">
            <div class="card-header">
                <span class="card-step">✅</span>
                <h2>Hasil Ciphertext</h2>
            </div>

            <div class="result-item">
                <label class="result-label">Tanpa Spasi</label>
                <div class="result-box mono" id="resNoSpace"><?= htmlspecialchars($resultData['noSpace']) ?></div>
            </div>

            <div class="result-item">
                <label class="result-label">Kelompok 5 Huruf <span class="label-note">(format standar cipher klasik)</span></label>
                <div class="result-box mono" id="resFmt5"><?= htmlspecialchars($resultData['formatted5']) ?></div>
            </div>

            <div class="result-item">
                <label class="result-label">
                    Hex Output
                    <span class="label-note badge-important">⚠️ Simpan ini untuk dekripsi</span>
                </label>
                <textarea class="result-textarea mono" id="resHex" readonly onclick="this.select()"><?= htmlspecialchars($resultData['hex']) ?></textarea>
                <button type="button" class="copy-btn" onclick="copyText('resHex', this)">📋 Salin Hex</button>
            </div>
        </div>

        <?php elseif ($resultData && !$isEncrypt): ?>
        <!-- ── Hasil Dekripsi ── -->
        <div class="form-card">
            <div class="card-header">
                <span class="card-step">✅</span>
                <h2>Hasil Plaintext</h2>
            </div>
            <div class="result-item">
                <label class="result-label">Teks Asli</label>
                <textarea class="result-textarea mono" id="resPlain" readonly onclick="this.select()"><?= htmlspecialchars($resultData['plaintext']) ?></textarea>
                <button type="button" class="copy-btn" onclick="copyText('resPlain', this)">📋 Salin Teks</button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tombol Kembali -->
        <a href="index.php" class="back-btn">← Kembali ke Form</a>

    </main>
</div>

<script>
function copyText(id, btn) {
    const el = document.getElementById(id);
    if (el.tagName === 'TEXTAREA') { el.select(); }
    else { navigator.clipboard.writeText(el.textContent.trim()); }
    try { document.execCommand('copy'); } catch(e) {}
    const orig = btn.textContent;
    btn.textContent = '✅ Tersalin!';
    btn.classList.add('copied');
    setTimeout(() => { btn.textContent = orig; btn.classList.remove('copied'); }, 2000);
}
</script>
</body>
</html>