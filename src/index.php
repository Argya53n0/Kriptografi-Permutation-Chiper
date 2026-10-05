<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permutation Cipher | Enkripsi & Dekripsi</title>
    <meta name="description" content="Aplikasi enkripsi dan dekripsi menggunakan algoritma Permutation Cipher untuk teks maupun file.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Background Orbs -->
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>

    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <h1 class="header-title">Permutation Cipher</h1>
            <p class="header-subtitle">Enkripsi & Dekripsi data menggunakan algoritma transposisi kolom</p>
            <div class="badge-row">
                <span class="badge">🔐 Columnar Transposition</span>
                <span class="badge">📁 Text & File Support</span>
                <span class="badge">🔑 Key-based</span>
            </div>
        </header>

        <!-- Main Card -->
        <main class="main-card">
            <form action="process.php" method="POST" enctype="multipart/form-data" id="cipherForm">

                <!-- Tab: Mode -->
                <section class="section">
                    <label class="section-label">Mode Operasi</label>
                    <div class="tab-group" id="actionTabs">
                        <button type="button" class="tab-btn active" data-value="encrypt" onclick="setAction('encrypt')">
                            <span class="tab-icon">🔒</span>
                            <span>Enkripsi</span>
                        </button>
                        <button type="button" class="tab-btn" data-value="decrypt" onclick="setAction('decrypt')">
                            <span class="tab-icon">🔓</span>
                            <span>Dekripsi</span>
                        </button>
                    </div>
                    <input type="hidden" name="action" id="actionInput" value="encrypt">
                </section>

                <!-- Tab: Input Type -->
                <section class="section">
                    <label class="section-label">Jenis Input</label>
                    <div class="tab-group" id="inputTypeTabs">
                        <button type="button" class="tab-btn active" data-value="text" onclick="setInputType('text')">
                            <span class="tab-icon">📝</span>
                            <span>Teks</span>
                        </button>
                        <button type="button" class="tab-btn" data-value="file" onclick="setInputType('file')">
                            <span class="tab-icon">📂</span>
                            <span>File</span>
                        </button>
                    </div>
                    <input type="hidden" name="inputType" id="inputTypeInput" value="text">
                </section>

                <!-- Input: Kunci -->
                <section class="section">
                    <label class="section-label" for="keyInput">Kunci Permutasi</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔑</span>
                        <input
                            type="text"
                            name="key"
                            id="keyInput"
                            class="input-field"
                            placeholder="Masukkan kunci (contoh: SECRET)"
                            autocomplete="off"
                            required
                        >
                    </div>
                    <p class="input-hint">Kunci menentukan urutan kolom permutasi. Gunakan huruf unik untuk hasil optimal.</p>
                </section>

                <!-- Input: Teks -->
                <section class="section" id="textSection">
                    <label class="section-label" for="inputText">Teks Input</label>
                    <div class="textarea-wrapper">
                        <textarea
                            name="inputText"
                            id="inputText"
                            class="textarea-field"
                            rows="6"
                            placeholder="Masukkan teks yang ingin dienkripsi atau didekripsi..."
                        ></textarea>
                        <div class="char-count" id="charCount">0 karakter</div>
                    </div>
                </section>

                <!-- Input: File -->
                <section class="section hidden" id="fileSection">
                    <label class="section-label">File Input</label>
                    <div class="file-drop-area" id="fileDropArea" onclick="document.getElementById('inputFile').click()">
                        <div class="file-drop-icon">📎</div>
                        <p class="file-drop-text">Klik atau drag & drop file di sini</p>
                        <p class="file-drop-hint">Semua jenis file didukung • Maks. 50MB</p>
                        <div class="file-name-display" id="fileNameDisplay" style="display:none;"></div>
                    </div>
                    <input type="file" name="inputFile" id="inputFile" class="hidden-file-input" onchange="showFileName(this)">
                </section>

                <!-- Submit Button -->
                <section class="section">
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span class="submit-icon" id="submitIcon">🔒</span>
                        <span id="submitText">Enkripsi Sekarang</span>
                        <div class="submit-arrow">→</div>
                    </button>
                </section>

            </form>
        </main>

        <!-- Info Cards -->
        <div class="info-grid">
            <div class="info-card">
                <div class="info-card-icon">🔀</div>
                <h3>Cara Kerja</h3>
                <p>Teks dibagi ke dalam baris berdasarkan panjang kunci, lalu kolom-kolom diacak sesuai urutan alfabetis kunci.</p>
            </div>
            <div class="info-card">
                <div class="info-card-icon">🛡️</div>
                <h3>Keamanan</h3>
                <p>Semakin panjang dan kompleks kunci, semakin sulit cipher dipecahkan dengan brute force.</p>
            </div>
            <div class="info-card">
                <div class="info-card-icon">📁</div>
                <h3>File Support</h3>
                <p>Enkripsi file semua tipe: dokumen, gambar, audio, dan lainnya. Download otomatis setelah proses.</p>
            </div>
        </div>

        <footer class="footer">
            <p>Kriptografi — Permutation Cipher &nbsp;|&nbsp; Tugas Kuliah</p>
        </footer>
    </div>

    <script>
        // === State Management ===
        let currentAction = 'encrypt';
        let currentInputType = 'text';

        function setAction(action) {
            currentAction = action;
            document.getElementById('actionInput').value = action;

            // Update tabs
            document.querySelectorAll('#actionTabs .tab-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.value === action);
            });

            // Update button label
            const isEncrypt = action === 'encrypt';
            document.getElementById('submitIcon').textContent = isEncrypt ? '🔒' : '🔓';
            document.getElementById('submitText').textContent = isEncrypt ? 'Enkripsi Sekarang' : 'Dekripsi Sekarang';
        }

        function setInputType(type) {
            currentInputType = type;
            document.getElementById('inputTypeInput').value = type;

            // Update tabs
            document.querySelectorAll('#inputTypeTabs .tab-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.value === type);
            });

            // Toggle sections
            document.getElementById('textSection').classList.toggle('hidden', type !== 'text');
            document.getElementById('fileSection').classList.toggle('hidden', type !== 'file');

            // Toggle required on textarea
            document.getElementById('inputText').required = (type === 'text');
        }

        function showFileName(input) {
            const display = document.getElementById('fileNameDisplay');
            const area = document.getElementById('fileDropArea');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const size = (file.size / 1024).toFixed(1);
                display.textContent = `📄 ${file.name} (${size} KB)`;
                display.style.display = 'block';
                area.classList.add('has-file');
            }
        }

        // Character count
        document.getElementById('inputText').addEventListener('input', function() {
            document.getElementById('charCount').textContent = this.value.length + ' karakter';
        });

        // Drag & Drop
        const dropArea = document.getElementById('fileDropArea');
        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropArea.classList.add('drag-over');
        });
        dropArea.addEventListener('dragleave', () => dropArea.classList.remove('drag-over'));
        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dropArea.classList.remove('drag-over');
            const dt = e.dataTransfer;
            const fileInput = document.getElementById('inputFile');
            fileInput.files = dt.files;
            showFileName(fileInput);
        });

        // Form submit animation
        document.getElementById('cipherForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            document.getElementById('submitText').textContent = 'Memproses...';
        });
    </script>
</body>
</html>