<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permutation Cipher — Enkripsi & Dekripsi</title>
    <meta name="description" content="Implementasi Permutation Cipher (Columnar Transposition) untuk enkripsi dan dekripsi teks maupun file.">
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
            <a href="#" class="sidenav-item active">Enkripsi / Dekripsi</a>
            <a href="#algo" class="sidenav-item">Cara Kerja Algoritma</a>
        </nav>

        <div class="sidebar-section" id="algo">
            <div class="section-title">Cara Kerja</div>
            <ol class="algo-list">
                <li>Tulis plaintext dalam tabel berukuran lebar kunci</li>
                <li>Urutkan kolom berdasarkan urutan alfabetis huruf kunci</li>
                <li>Baca ciphertext dari kolom yang telah diurutkan</li>
                <li>Padding ditambahkan otomatis jika diperlukan</li>
            </ol>

            <div class="notice">
                <div class="notice-title">Catatan</div>
                <p>Permutation Cipher merupakan cipher klasik yang bersifat edukatif. Tidak direkomendasikan untuk keamanan data produksi.</p>
            </div>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <header class="page-header">
            <h1 class="page-title">Permutation Cipher</h1>
            <p class="page-desc">Enkripsi dan dekripsi teks atau file menggunakan algoritma transposisi kolom berbasis kunci.</p>
        </header>

        <form action="process.php" method="POST" enctype="multipart/form-data" id="mainForm" novalidate>

            <!-- STEP 1: Mode -->
            <section class="card">
                <div class="card-label">Langkah 1 — Pilih Mode</div>
                <div class="radio-group">
                    <label class="radio-card">
                        <input type="radio" name="action" value="encrypt" checked onchange="onModeChange()">
                        <div class="radio-body">
                            <div class="radio-title">Enkripsi</div>
                            <div class="radio-desc">Plaintext &rarr; Ciphertext</div>
                        </div>
                    </label>
                    <label class="radio-card">
                        <input type="radio" name="action" value="decrypt" onchange="onModeChange()">
                        <div class="radio-body">
                            <div class="radio-title">Dekripsi</div>
                            <div class="radio-desc">Ciphertext &rarr; Plaintext</div>
                        </div>
                    </label>
                </div>
            </section>

            <!-- STEP 2: Kunci -->
            <section class="card">
                <div class="card-label">Langkah 2 — Kunci Permutasi</div>
                <div class="field">
                    <label class="field-label" for="keyInput" id="keyLabel">Masukkan kunci enkripsi</label>
                    <input
                        type="text"
                        name="key"
                        id="keyInput"
                        class="input mono"
                        placeholder="Contoh: KUNCI"
                        autocomplete="off"
                        oninput="onKeyChange(this)"
                        required
                    >
                    <div class="field-hint" id="keyHint">Kunci digunakan untuk menentukan urutan kolom permutasi. Hanya huruf alfabet.</div>
                    <div class="field-err" id="keyErr"></div>
                </div>

                <!-- Preview urutan kolom -->
                <div id="colPreview" class="col-preview hidden">
                    <div class="col-preview-label">Urutan kolom berdasarkan kunci</div>
                    <div class="col-cells" id="colCells"></div>
                </div>
            </section>

            <!-- STEP 3: Input -->
            <section class="card">
                <div class="card-label">Langkah 3 — Data Input</div>

                <div class="tab-bar">
                    <button type="button" class="tab active" id="tabText" onclick="switchTab('text')">Teks</button>
                    <button type="button" class="tab" id="tabFile" onclick="switchTab('file')">File</button>
                </div>
                <input type="hidden" name="inputType" id="inputTypeVal" value="text">

                <!-- Teks -->
                <div id="secText">
                    <!-- Format selector -->
                    <div class="format-bar">
                        <span class="format-label">Format input:</span>
                        <div class="fmt-tabs" id="fmtTabs">
                            <button type="button" class="fmt-tab active" data-fmt="text"   onclick="onFormatChange('text')">Teks</button>
                            <button type="button" class="fmt-tab"        data-fmt="binary" onclick="onFormatChange('binary')">Biner</button>
                            <button type="button" class="fmt-tab"        data-fmt="hex"   onclick="onFormatChange('hex')">Hex</button>
                        </div>
                        <input type="hidden" name="inputFormat" id="inputFormat" value="text">
                    </div>

                    <div class="field">
                        <label class="field-label" id="textLabel" for="inputText">Teks yang akan diproses</label>
                        <div class="textarea-wrap">
                            <textarea
                                name="inputText"
                                id="inputText"
                                class="textarea"
                                rows="6"
                                placeholder="Tulis atau paste teks di sini..."
                                oninput="validateFormat()"
                            ></textarea>
                            <span class="char-count" id="charCount">0 karakter</span>
                        </div>
                        <div class="field-hint" id="textHint">Format <strong>Teks</strong>: masukkan plaintext biasa.</div>
                        <div class="field-err" id="fmtErr"></div>
                    </div>
                </div>

                <!-- File -->
                <div id="secFile" class="hidden">
                    <div class="field">
                        <label class="field-label">File yang akan diproses</label>
                        <div class="file-area" id="fileArea" onclick="document.getElementById('fileInput').click()">
                            <div class="file-area-title">Klik untuk memilih file</div>
                            <div class="file-area-sub">atau seret dan lepas file ke sini &mdash; semua tipe, maks. 50 MB</div>
                            <div class="file-selected" id="fileSelected"></div>
                        </div>
                        <input type="file" name="inputFile" id="fileInput" style="display:none" onchange="onFileSelect(this)">
                        <div class="field-hint" id="fileHint">Untuk dekripsi, upload file <code>.dat</code> hasil enkripsi dari program ini.</div>
                    </div>
                </div>
            </section>

            <!-- SUBMIT -->
            <button type="submit" class="btn-primary" id="submitBtn">
                <span id="btnText">Enkripsi</span>
            </button>

        </form>
    </main>
</div>

<script>
let currentMode   = 'encrypt';
let currentFormat = 'text';

const FORMAT_INFO = {
    text:   { label: 'Teks yang akan diproses',  placeholder: 'Tulis atau paste teks di sini...', hint: 'Format <strong>Teks</strong>: masukkan teks biasa (plaintext).' },
    binary: { label: 'String biner yang akan diproses', placeholder: 'Contoh: 01001000 01100101 01101100 01101100 01101111', hint: 'Format <strong>Biner</strong>: masukkan deretan bit 8-digit per karakter, boleh dipisah spasi.' },
    hex:    { label: 'String Hex yang akan diproses',    placeholder: 'Contoh: 48656c6c6f atau 48 65 6c 6c 6f', hint: 'Format <strong>Hex</strong>: masukkan pasangan hex per byte, boleh dipisah spasi.' },
};

function onModeChange() {
    currentMode = document.querySelector('input[name="action"]:checked').value;
    const enc = currentMode === 'encrypt';
    document.getElementById('btnText').textContent = enc ? 'Enkripsi' : 'Dekripsi';
    // Update label kunci
    document.getElementById('keyLabel').textContent = enc
        ? 'Masukkan kunci enkripsi'
        : 'Masukkan kunci dekripsi';
    document.getElementById('keyHint').innerHTML = enc
        ? 'Kunci digunakan untuk menentukan urutan kolom permutasi. Hanya huruf alfabet.'
        : '<strong>Gunakan kunci yang sama</strong> dengan saat melakukan enkripsi. Kunci yang salah akan menghasilkan output yang tidak bermakna.';
    updateFormatUI();
}

function onFormatChange(fmt) {
    currentFormat = fmt;
    document.getElementById('inputFormat').value = fmt;
    document.querySelectorAll('.fmt-tab').forEach(b => b.classList.toggle('active', b.dataset.fmt === fmt));
    updateFormatUI();
    document.getElementById('inputText').value = '';
    document.getElementById('charCount').textContent = '0 karakter';
    document.getElementById('fmtErr').textContent = '';
}

function updateFormatUI() {
    const info = FORMAT_INFO[currentFormat];
    document.getElementById('textLabel').textContent      = info.label;
    document.getElementById('inputText').placeholder      = info.placeholder;
    document.getElementById('textHint').innerHTML         = info.hint;
}

function validateFormat() {
    const val = document.getElementById('inputText').value.trim();
    const err = document.getElementById('fmtErr');
    document.getElementById('charCount').textContent = document.getElementById('inputText').value.length + ' karakter';
    if (!val) { err.textContent = ''; return true; }
    if (currentFormat === 'binary') {
        const clean = val.replace(/\s/g, '');
        if (!/^[01]+$/.test(clean)) { err.textContent = 'Format biner tidak valid. Hanya boleh berisi 0, 1, dan spasi.'; return false; }
        if (clean.length % 8 !== 0)  { err.textContent = 'Panjang string biner harus kelipatan 8 bit (' + clean.length + ' bit terdeteksi).'; return false; }
    }
    if (currentFormat === 'hex') {
        const clean = val.replace(/\s/g, '');
        if (!/^[0-9a-fA-F]+$/.test(clean)) { err.textContent = 'Format Hex tidak valid. Hanya boleh berisi 0-9 dan a-f.'; return false; }
        if (clean.length % 2 !== 0)         { err.textContent = 'Jumlah karakter Hex harus genap (' + clean.length + ' karakter terdeteksi).'; return false; }
    }
    err.textContent = ''; return true;
}

function onKeyChange(el) {
    el.value = el.value.replace(/[^a-zA-Z]/g, '').toUpperCase();
    const key = el.value;
    const errEl = document.getElementById('keyErr');
    const preview = document.getElementById('colPreview');
    const cells = document.getElementById('colCells');

    if (!key) { errEl.textContent = ''; preview.classList.add('hidden'); return; }
    if (key.length < 2) { errEl.textContent = 'Kunci minimal 2 huruf.'; preview.classList.add('hidden'); return; }
    errEl.textContent = '';

    // Hitung nomor urut kolom
    const arr = key.split('').map((c, i) => ({ c, i }));
    const sorted = [...arr].sort((a, b) => a.c.localeCompare(b.c) || a.i - b.i);
    const order = Array(key.length);
    sorted.forEach((item, rank) => { order[item.i] = rank + 1; });

    cells.innerHTML = key.split('').map((c, i) =>
        `<div class="col-cell"><span class="cc-letter">${c}</span><span class="cc-num">${order[i]}</span></div>`
    ).join('');
    preview.classList.remove('hidden');
}

function switchTab(type) {
    document.getElementById('inputTypeVal').value = type;
    document.getElementById('tabText').classList.toggle('active', type === 'text');
    document.getElementById('tabFile').classList.toggle('active', type === 'file');
    document.getElementById('secText').classList.toggle('hidden', type !== 'text');
    document.getElementById('secFile').classList.toggle('hidden', type !== 'file');
    document.getElementById('inputText').required = (type === 'text');
}

function onFileSelect(input) {
    if (!input.files[0]) return;
    const f = input.files[0];
    const sz = f.size > 1048576 ? (f.size/1048576).toFixed(1)+' MB' : (f.size/1024).toFixed(1)+' KB';
    const sel = document.getElementById('fileSelected');
    sel.textContent = f.name + ' (' + sz + ')';
    sel.style.display = 'block';
    document.getElementById('fileArea').classList.add('has-file');
}

document.getElementById('inputText').addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length + ' karakter';
});

const fa = document.getElementById('fileArea');
fa.addEventListener('dragover', e => { e.preventDefault(); fa.classList.add('drag-active'); });
fa.addEventListener('dragleave', () => fa.classList.remove('drag-active'));
fa.addEventListener('drop', e => {
    e.preventDefault(); fa.classList.remove('drag-active');
    document.getElementById('fileInput').files = e.dataTransfer.files;
    onFileSelect(document.getElementById('fileInput'));
});

document.getElementById('mainForm').addEventListener('submit', function(e) {
    const key = document.getElementById('keyInput').value.trim();
    if (key.length < 2) {
        e.preventDefault();
        document.getElementById('keyErr').textContent = 'Kunci minimal 2 huruf.';
        document.getElementById('keyInput').focus();
        return;
    }
    if (document.getElementById('inputTypeVal').value === 'text' && !validateFormat()) {
        e.preventDefault();
        document.getElementById('inputText').focus();
        return;
    }
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('btnText').textContent = 'Memproses...';
});
</script>
</body>
</html>