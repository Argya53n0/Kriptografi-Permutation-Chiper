<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permutation Cipher | Enkripsi & Dekripsi</title>
    <meta name="description" content="Aplikasi enkripsi dan dekripsi teks/file menggunakan algoritma Permutation Cipher (Columnar Transposition).">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="page-wrapper">

    <!-- ===== SIDEBAR / ABOUT ===== -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-icon">🔐</span>
            <span class="logo-text">PermCipher</span>
        </div>

        <nav class="sidebar-nav">
            <span class="nav-label">Menu</span>
            <a href="#form-section" class="nav-link active">⚙️ Enkripsi / Dekripsi</a>
            <a href="#about-section" class="nav-link">📖 Tentang Algoritma</a>
        </nav>

        <div class="sidebar-info" id="about-section">
            <h3>Permutation Cipher</h3>
            <p>Cipher transposisi kolom yang mengacak posisi karakter berdasarkan urutan alfabetis kunci.</p>

            <div class="algo-step">
                <div class="step-num">1</div>
                <div class="step-text">Tulis plaintext dalam baris selebar panjang kunci</div>
            </div>
            <div class="algo-step">
                <div class="step-num">2</div>
                <div class="step-text">Urutkan kolom berdasarkan urutan alfabetis kunci</div>
            </div>
            <div class="algo-step">
                <div class="step-num">3</div>
                <div class="step-text">Baca ciphertext dari kolom yang telah diurutkan</div>
            </div>

            <div class="info-box">
                <strong>⚠️ Catatan</strong>
                <p>Cipher ini bersifat edukatif. Tidak disarankan untuk keamanan data sensitif.</p>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content" id="form-section">
        <div class="content-header">
            <h1>Permutation Cipher</h1>
            <p>Enkripsi & dekripsi teks atau file menggunakan algoritma transposisi kolom</p>
        </div>

        <form action="process.php" method="POST" enctype="multipart/form-data" id="cipherForm" novalidate>

            <!-- Step 1: Mode -->
            <div class="form-card">
                <div class="card-header">
                    <span class="card-step">1</span>
                    <h2>Pilih Mode</h2>
                </div>
                <div class="toggle-group" id="actionGroup">
                    <label class="toggle-option">
                        <input type="radio" name="action" value="encrypt" checked onchange="onActionChange()">
                        <div class="toggle-box">
                            <span class="toggle-icon">🔒</span>
                            <div>
                                <strong>Enkripsi</strong>
                                <small>Plaintext → Ciphertext</small>
                            </div>
                        </div>
                    </label>
                    <label class="toggle-option">
                        <input type="radio" name="action" value="decrypt" onchange="onActionChange()">
                        <div class="toggle-box">
                            <span class="toggle-icon">🔓</span>
                            <div>
                                <strong>Dekripsi</strong>
                                <small>Ciphertext → Plaintext</small>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Step 2: Kunci -->
            <div class="form-card">
                <div class="card-header">
                    <span class="card-step">2</span>
                    <h2>Masukkan Kunci</h2>
                </div>
                <div class="field-group">
                    <label for="keyInput" class="field-label">Kunci Permutasi</label>
                    <div class="input-row">
                        <input
                            type="text"
                            name="key"
                            id="keyInput"
                            class="text-input mono"
                            placeholder="Contoh: KUNCI, SECRET, HELLO"
                            autocomplete="off"
                            oninput="onKeyInput(this)"
                            required
                        >
                        <div class="key-preview" id="keyPreview"></div>
                    </div>
                    <p class="field-hint">Gunakan huruf alfabet saja. Kunci menentukan urutan kolom permutasi.</p>
                    <div class="field-error" id="keyError"></div>
                </div>
            </div>

            <!-- Step 3: Input -->
            <div class="form-card">
                <div class="card-header">
                    <span class="card-step">3</span>
                    <h2>Input Data</h2>
                </div>

                <!-- Sub-toggle: Teks / File -->
                <div class="sub-toggle" id="inputTypeGroup">
                    <button type="button" class="sub-tab active" id="tabText" onclick="switchTab('text')">📝 Teks</button>
                    <button type="button" class="sub-tab" id="tabFile" onclick="switchTab('file')">📂 File</button>
                </div>
                <input type="hidden" name="inputType" id="inputTypeHidden" value="text">

                <!-- Teks -->
                <div id="textSection">
                    <label for="inputText" class="field-label" id="textLabel">Teks yang ingin dienkripsi</label>
                    <div class="textarea-wrap">
                        <textarea
                            name="inputText"
                            id="inputText"
                            class="textarea-input"
                            rows="6"
                            placeholder="Tulis teks di sini..."
                        ></textarea>
                        <span class="char-counter" id="charCounter">0 karakter</span>
                    </div>
                    <div class="field-hint" id="textHint">
                        💡 Untuk dekripsi, masukkan teks <strong>Hex</strong> dari hasil enkripsi.
                    </div>
                </div>

                <!-- File -->
                <div id="fileSection" class="hidden">
                    <label class="field-label">Upload File</label>
                    <div class="file-zone" id="fileZone" onclick="document.getElementById('inputFile').click()">
                        <div class="file-zone-icon">📎</div>
                        <p class="file-zone-text">Klik atau drag & drop file di sini</p>
                        <p class="file-zone-sub">Semua tipe file didukung &bull; Maks. 50MB</p>
                        <div class="file-badge" id="fileBadge" style="display:none"></div>
                    </div>
                    <input type="file" name="inputFile" id="inputFile" style="display:none" onchange="onFileSelect(this)">
                    <div class="field-hint" id="fileHint">
                        💡 Untuk dekripsi, upload file <code>.dat</code> hasil enkripsi program ini.
                    </div>
                </div>
            </div>

            <!-- Step 4: Submit -->
            <button type="submit" class="submit-btn" id="submitBtn">
                <span id="btnIcon">🔒</span>
                <span id="btnLabel">Enkripsi Sekarang</span>
            </button>

        </form>
    </main>
</div>

<script>
// ─── State ───────────────────────────────────────
let currentAction    = 'encrypt';
let currentInputType = 'text';

// ─── Action change ────────────────────────────────
function onActionChange() {
    const val = document.querySelector('input[name="action"]:checked').value;
    currentAction = val;
    const isEncrypt = val === 'encrypt';
    document.getElementById('btnIcon').textContent  = isEncrypt ? '🔒' : '🔓';
    document.getElementById('btnLabel').textContent = isEncrypt ? 'Enkripsi Sekarang' : 'Dekripsi Sekarang';
    // Update label & hint textarea
    document.getElementById('textLabel').textContent = isEncrypt
        ? 'Teks yang ingin dienkripsi'
        : 'Teks Hex hasil enkripsi';
    document.getElementById('textHint').innerHTML = isEncrypt
        ? '💡 Masukkan plaintext yang ingin dienkripsi.'
        : '💡 Masukkan teks <strong>Hex</strong> yang didapat dari hasil enkripsi.';
    document.getElementById('inputText').placeholder = isEncrypt
        ? 'Tulis teks di sini...'
        : 'Paste teks Hex di sini...';
    document.getElementById('fileHint').innerHTML = isEncrypt
        ? '💡 Upload file apapun untuk dienkripsi. Hasil download sebagai <code>.dat</code>.'
        : '💡 Upload file <code>.dat</code> hasil enkripsi program ini.';
}

// ─── Kunci preview ───────────────────────────────
function onKeyInput(el) {
    // Hanya izinkan huruf alfabet
    el.value = el.value.replace(/[^a-zA-Z]/g, '').toUpperCase();
    const key = el.value;
    const preview = document.getElementById('keyPreview');
    const err = document.getElementById('keyError');

    if (key.length === 0) { preview.innerHTML = ''; err.textContent = ''; return; }
    if (key.length < 2)   { err.textContent = 'Kunci minimal 2 huruf.'; preview.innerHTML = ''; return; }
    err.textContent = '';

    // Tampilkan urutan kolom
    const sorted = key.split('').map((c,i) => ({c,i})).sort((a,b) => a.c.localeCompare(b.c) || a.i-b.i);
    const order  = Array(key.length);
    sorted.forEach((item, rank) => { order[item.i] = rank + 1; });

    let html = '<div class="key-order-row">';
    for (let i = 0; i < key.length; i++) {
        html += `<div class="key-cell"><span class="kc-char">${key[i]}</span><span class="kc-num">${order[i]}</span></div>`;
    }
    html += '</div><div class="key-order-label">Urutan kolom permutasi</div>';
    preview.innerHTML = html;
}

// ─── Tab input type ──────────────────────────────
function switchTab(type) {
    currentInputType = type;
    document.getElementById('inputTypeHidden').value = type;
    document.getElementById('tabText').classList.toggle('active', type === 'text');
    document.getElementById('tabFile').classList.toggle('active', type === 'file');
    document.getElementById('textSection').classList.toggle('hidden', type !== 'text');
    document.getElementById('fileSection').classList.toggle('hidden', type !== 'file');
    document.getElementById('inputText').required = (type === 'text');
}

// ─── File select ──────────────────────────────────
function onFileSelect(input) {
    if (!input.files[0]) return;
    const f = input.files[0];
    const size = f.size > 1048576
        ? (f.size/1048576).toFixed(1) + ' MB'
        : (f.size/1024).toFixed(1) + ' KB';
    const badge = document.getElementById('fileBadge');
    badge.textContent = `📄 ${f.name} (${size})`;
    badge.style.display = 'block';
    document.getElementById('fileZone').classList.add('has-file');
}

// ─── Char counter ─────────────────────────────────
document.getElementById('inputText').addEventListener('input', function() {
    document.getElementById('charCounter').textContent = this.value.length + ' karakter';
});

// ─── Drag & drop ──────────────────────────────────
const fz = document.getElementById('fileZone');
fz.addEventListener('dragover', e => { e.preventDefault(); fz.classList.add('drag-over'); });
fz.addEventListener('dragleave', () => fz.classList.remove('drag-over'));
fz.addEventListener('drop', e => {
    e.preventDefault();
    fz.classList.remove('drag-over');
    document.getElementById('inputFile').files = e.dataTransfer.files;
    onFileSelect(document.getElementById('inputFile'));
});

// ─── Form submit ──────────────────────────────────
document.getElementById('cipherForm').addEventListener('submit', function(e) {
    const key = document.getElementById('keyInput').value.trim();
    if (key.length < 2) {
        e.preventDefault();
        document.getElementById('keyError').textContent = 'Kunci minimal 2 huruf.';
        document.getElementById('keyInput').focus();
        return;
    }
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('btnLabel').textContent = 'Memproses...';
});
</script>
</body>
</html>