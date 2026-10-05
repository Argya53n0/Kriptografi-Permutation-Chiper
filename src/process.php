<?php
require 'Permutation.php';
require 'FileHandler.php';

$cipher = new Permutation();

// ── Redirect jika bukan POST ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$action      = $_POST['action']      ?? 'encrypt';
$inputType   = $_POST['inputType']   ?? 'text';
$inputFormat = $_POST['inputFormat'] ?? 'text'; // text | binary | hex
$key         = strtoupper(trim($_POST['key'] ?? ''));

// ── Helper: konversi format input ke raw bytes ──────────────────────
function parseInput(string $raw, string $format): ?string {
    $clean = preg_replace('/\s+/', '', $raw);
    if ($format === 'binary') {
        if (!preg_match('/^[01]+$/', $clean) || strlen($clean) % 8 !== 0) return null;
        $bytes = '';
        foreach (str_split($clean, 8) as $byte) {
            $bytes .= chr(bindec($byte));
        }
        return $bytes;
    }
    if ($format === 'hex') {
        if (!preg_match('/^[0-9a-fA-F]+$/', $clean) || strlen($clean) % 2 !== 0) return null;
        return hex2bin($clean);
    }
    return $raw; // format 'text' — kembalikan apa adanya
}

// ── Helper: konversi bytes ke representasi biner ────────────────────
function toBinaryString(string $bytes): string {
    $bits = [];
    for ($i = 0; $i < strlen($bytes); $i++) {
        $bits[] = str_pad(decbin(ord($bytes[$i])), 8, '0', STR_PAD_LEFT);
    }
    return implode(' ', $bits);
}

// ── Validasi kunci ──────────────────────────────────────────────────
$errors = [];
if (empty($key))              $errors[] = 'Kunci tidak boleh kosong.';
elseif (!ctype_alpha($key))   $errors[] = 'Kunci hanya boleh berisi huruf alfabet.';
elseif (strlen($key) < 2)     $errors[] = 'Kunci minimal 2 huruf.';

// ── PROSES FILE (sebelum output HTML) ──────────────────────────────
if (!$errors && $inputType === 'file') {
    $uploadOk = isset($_FILES['inputFile']) && $_FILES['inputFile']['error'] === UPLOAD_ERR_OK;
    if (!$uploadOk) {
        $errors[] = 'Gagal mengupload file. Pastikan file dipilih dan ukurannya tidak melebihi 50 MB.';
    } else {
        $tmpPath      = $_FILES['inputFile']['tmp_name'];
        $originalName = $_FILES['inputFile']['name'];

        if ($action === 'encrypt') {
            $out = FileHandler::encryptFile($tmpPath, $originalName, $key, $cipher);
            $dlName = 'encrypted_' . pathinfo($originalName, PATHINFO_FILENAME) . '.dat';
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $dlName . '"');
            header('Access-Control-Expose-Headers: Content-Disposition');
            header('Content-Length: ' . strlen($out));
            echo $out;
            exit;
        } else {
            $result = FileHandler::decryptFile($tmpPath, $key, $cipher);
            if (!$result) {
                $errors[] = 'Format file tidak valid. Pastikan file merupakan hasil enkripsi dari program ini dan kunci yang digunakan benar.';
            } else {
                // Gunakan nama asli dari file yang didekripsi
                $dlName = 'decrypted_' . $result['originalName'];
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $dlName . '"');
                header('Access-Control-Expose-Headers: Content-Disposition');
                header('Content-Length: ' . strlen($result['data']));
                echo $result['data'];
                exit;
            }
        }
    }
}

// ── Respon JSON untuk AJAX request pada File jika ada error ────────
if ($errors && $inputType === 'file') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);
    if ($isAjax) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => implode("\n", $errors)]);
        exit;
    }
}

// ── PROSES TEKS ─────────────────────────────────────────────────────
$result = null;

if (!$errors && $inputType === 'text') {
    $rawInput = $_POST['inputText'] ?? '';
    if (trim($rawInput) === '') {
        $errors[] = 'Input tidak boleh kosong.';
    } else {
        // Konversi format input ke bytes
        $text = parseInput($rawInput, $inputFormat);
        if ($text === null) {
            $errors[] = 'Format input tidak valid. Periksa kembali nilai yang dimasukkan sesuai format yang dipilih.';
        } elseif ($action === 'encrypt') {
            // Untuk enkripsi teks gaya klasik, kita HAPUS SELURUH WHITESPACE (spasi, enter, tab) dari plaintext.
            // Ini membuat ciphertext murni huruf, mencegah error karena ada 'enter' tak kasat mata di akhir teks.
            $cleanText  = preg_replace('/\s+/', '', $text);
            $raw        = $cipher->encrypt($cleanText, $key, true);
            $hex        = bin2hex($raw);
            $binary     = toBinaryString($raw);
            
            // Ciphertext murni
            $noSpace    = $raw; 
            $grouped    = trim(chunk_split($noSpace, 5, ' '));
            $table      = $cipher->buildTable($cleanText, $key, $inputFormat);
            $colNums    = $cipher->getColumnNumbers($key);

            $result = [
                'mode'        => 'encrypt',
                'key'         => $key,
                'inputFormat' => $inputFormat,
                'colNums'     => $colNums,
                'table'       => $table,
                'noSpace'     => $noSpace,
                'grouped'     => $grouped,
                'hex'         => $hex,
                'binary'      => $binary,
                'input'       => $text,
            ];
        } else {
            // Dekripsi: terima hex atau raw bytes
            $rawInput  = trim($text);
            $cleanHex  = preg_replace('/\s+/', '', $rawInput);
            $raw       = ctype_xdigit($cleanHex) ? hex2bin($cleanHex) : $rawInput;
            
            // Hapus seluruh whitespace (termasuk spasi dan enter) jika user paste format "Kelompok 5 huruf" atau ada enter terselip
            if (!ctype_xdigit($cleanHex)) {
                $raw = preg_replace('/\s+/', '', $raw);
            }
            
            $decoded   = $cipher->decrypt($raw, $key, true);
            $colNums   = $cipher->getColumnNumbers($key);
            $decTable  = $cipher->buildDecryptTable($raw, $key, $decoded);

            $result = [
                'mode'     => 'decrypt',
                'key'      => $key,
                'colNums'  => $colNums,
                'decTable' => $decTable,
                'plain'    => $decoded,
            ];
        }
    }
}

$isEncrypt = ($action === 'encrypt');
$pageTitle = $isEncrypt ? 'Hasil Enkripsi' : 'Hasil Dekripsi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — Permutation Cipher</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">PC</div>
            <div>
                <div class="brand-name">Permutation Cipher</div>
                <div class="brand-sub">Columnar Transposition</div>
            </div>
        </div>

        <nav class="sidenav">
            <a href="index.php" class="sidenav-item">Kembali ke Form</a>
        </nav>

        <?php if ($result): ?>
        <div class="sidebar-section">
            <div class="section-title">Ringkasan Proses</div>
            <div class="info-rows">
                <div class="info-row">
                    <span class="ir-label">Mode</span>
                    <span class="ir-val"><?= $isEncrypt ? 'Enkripsi' : 'Dekripsi' ?></span>
                </div>
                <div class="info-row">
                    <span class="ir-label">Kunci</span>
                    <span class="ir-val mono"><?= htmlspecialchars($key) ?></span>
                </div>
                <div class="info-row">
                    <span class="ir-label">Panjang kunci</span>
                    <span class="ir-val"><?= strlen($key) ?> kolom</span>
                </div>
                <?php if ($isEncrypt && $result): ?>
                <div class="info-row">
                    <span class="ir-label">Panjang input</span>
                    <span class="ir-val"><?= strlen($result['input']) ?> karakter</span>
                </div>
                <div class="info-row">
                    <span class="ir-label">Jumlah baris</span>
                    <span class="ir-val"><?= count($result['table']) ?> baris</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="sidebar-section">
            <div class="notice">
                <div class="notice-title">Permutation Cipher</div>
                <p>Cipher transposisi kolom — posisi karakter diacak sesuai urutan kunci, bukan karakternya.</p>
            </div>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <header class="page-header">
            <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="page-desc">Kunci: <code class="code-inline"><?= htmlspecialchars($key) ?></code></p>
        </header>

        <?php if ($errors): ?>
        <!-- ERROR -->
        <section class="card">
            <div class="card-label">Kesalahan</div>
            <ul class="err-list">
                <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="index.php" class="link-back">Kembali ke form</a>
        </section>

        <?php elseif ($result && $isEncrypt): ?>
        <!-- TABEL PERMUTASI -->
        <section class="card">
            <div class="card-label">Tabel Permutasi</div>
            <p class="card-desc">Plaintext ditulis baris demi baris, kemudian dibaca per kolom sesuai urutan kunci.</p>
            <div class="table-scroll">
                <table class="perm-table">
                    <thead>
                        <tr class="row-key">
                            <?php foreach (str_split($result['key']) as $ch): ?>
                            <th><?= htmlspecialchars($ch) ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr class="row-num">
                            <?php foreach ($result['colNums'] as $n): ?>
                            <th><?= $n ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['table'] as $row): ?>
                        <tr>
                            <?php foreach ($row as $cell): ?>
                            <td class="<?= $cell['isPad'] ? 'pad-cell' : '' ?>"><?= htmlspecialchars($cell['value']) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="table-note"><?php
                if ($result['inputFormat'] === 'binary') echo '(pad) = byte padding, dihapus otomatis saat dekripsi';
                elseif ($result['inputFormat'] === 'hex') echo '** = byte padding, dihapus otomatis saat dekripsi';
                else echo '* = karakter padding, dihapus otomatis saat dekripsi';
            ?></p>
        </section>

        <!-- HASIL CIPHERTEXT -->
        <section class="card">
            <div class="card-label">Ciphertext</div>

            <div class="result-field">
                <label class="result-label">Tanpa spasi</label>
                <div class="result-box mono"><?= htmlspecialchars($result['noSpace']) ?></div>
            </div>

            <div class="result-field">
                <label class="result-label">Kelompok 5 huruf <span class="label-sub">(format standar cipher klasik)</span></label>
                <div class="result-box mono"><?= htmlspecialchars($result['grouped']) ?></div>
            </div>

            <div class="result-field">
                <label class="result-label">Output Biner <span class="label-sub">(setiap byte dalam 8 bit)</span></label>
                <textarea class="result-textarea mono" id="resBin" readonly onclick="this.select()" style="font-size:0.75rem; min-height:70px;"><?= htmlspecialchars($result['binary']) ?></textarea>
                <div style="display:flex; gap:0.5rem; margin-top:0.5rem;">
                    <button type="button" class="btn-copy" onclick="copyEl('resBin', this)">Salin Biner</button>
                    <button type="button" class="btn-copy btn-download" onclick="downloadTxt('resBin', 'ciphertext_binary.txt')">Download Biner (.txt)</button>
                </div>
            </div>

            <div class="result-field">
                <label class="result-label">
                    Hex output
                    <span class="label-warn">Simpan untuk keperluan dekripsi</span>
                </label>
                <textarea class="result-textarea mono" id="hexOut" readonly onclick="this.select()"><?= htmlspecialchars($result['hex']) ?></textarea>
                <div style="display:flex; gap:0.5rem; margin-top:0.5rem;">
                    <button type="button" class="btn-copy" onclick="copyEl('hexOut', this)">Salin Hex</button>
                    <button type="button" class="btn-copy btn-download" onclick="downloadTxt('hexOut', 'ciphertext_hex.txt')">Download Hex (.txt)</button>
                </div>
            </div>
        </section>

        <?php elseif ($result && !$isEncrypt): ?>

        <!-- TABEL PROSES DEKRIPSI -->
        <section class="card">
            <div class="card-label">Tabel Proses Dekripsi</div>
            <p class="card-desc">
                Ciphertext dibagi ke dalam kolom sesuai urutan kunci, kemudian kolom-kolom
                dikembalikan ke posisi semula. Membaca tabel baris per baris menghasilkan plaintext.
            </p>
            <div class="table-scroll">
                <table class="perm-table">
                    <thead>
                        <tr class="row-key">
                            <?php foreach (str_split($result['key']) as $ch): ?>
                            <th><?= htmlspecialchars($ch) ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr class="row-num">
                            <?php foreach ($result['colNums'] as $n): ?>
                            <th><?= $n ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['decTable'] as $row): ?>
                        <tr>
                            <?php foreach ($row as $cell): ?>
                            <td class="<?= $cell['isPad'] ? 'pad-cell' : '' ?>"><?= htmlspecialchars($cell['value']) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="table-note">* = karakter padding yang dihapus. Baca baris per baris dari kiri ke kanan untuk mendapat plaintext.</p>
        </section>

        <!-- HASIL PLAINTEXT -->
        <section class="card">
            <div class="card-label">Plaintext</div>
            <div class="result-field">
                <label class="result-label">Teks hasil dekripsi</label>
                <textarea class="result-textarea mono" id="plainOut" readonly onclick="this.select()"><?= htmlspecialchars($result['plain']) ?></textarea>
                <div style="display:flex; gap:0.5rem; margin-top:0.5rem;">
                    <button type="button" class="btn-copy" onclick="copyEl('plainOut', this)">Salin Teks</button>
                    <button type="button" class="btn-copy btn-download" onclick="downloadTxt('plainOut', 'decrypted_plaintext.txt')">Download Teks (.txt)</button>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <a href="index.php" class="btn-secondary">Kembali ke Form</a>
    </main>
</div>

<script>
function copyEl(id, btn) {
    const el = document.getElementById(id);
    el.select();
    try { document.execCommand('copy'); } catch (e) {
        navigator.clipboard?.writeText(el.value);
    }
    const orig = btn.textContent;
    btn.textContent = 'Tersalin';
    btn.classList.add('copied');
    setTimeout(() => { btn.textContent = orig; btn.classList.remove('copied'); }, 2000);
}

function downloadTxt(id, filename) {
    const text = document.getElementById(id).value;
    const blob = new Blob([text], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>
</body>
</html>