# Kriptografi Permutation Cipher

Aplikasi web untuk enkripsi dan dekripsi data menggunakan algoritma Permutation Cipher (Transposisi Kolom). Program ini dibangun menggunakan Vanilla PHP dan dirancang untuk keperluan edukasi dan tugas mata kuliah Kriptografi.

## 1. Tentang Program

Program ini mengimplementasikan algoritma Permutation Cipher dengan dukungan untuk dua mode input:
- **Mode Teks:** Mengenkskripsi dan mendekripsi string atau pesan teks. Pesan teks akan dihapus spasinya sebelum enkripsi untuk mengamankan struktur kalimat (standar Kriptografi Klasik). Tersedia output dalam format string mentah (tanpa spasi), kelompok 5 huruf, hexadecimal, dan biner.
- **Mode File:** Mendukung enkripsi dan dekripsi byte-per-byte pada semua jenis file (misalnya dokumen, PDF, gambar, dll). File terenkripsi otomatis disimpan dengan ekstensi `.dat`. Pada saat didekripsi, program akan mengembalikan ekstensi dan nama asli dari file tersebut secara otomatis.

## 2. Dasar Teori Algoritma

Permutation Cipher (Transposisi Kolom) tidak mengganti identitas asli dari sebuah karakter (bukan cipher substitusi), melainkan hanya mengubah posisi baris dan kolom karakter tersebut. 

Langkah-langkah proses algoritma:
1. **Penentuan Kunci:** Program meminta kunci (string alfabet) dari pengguna.
2. **Pengurutan Kunci:** Huruf-huruf pada kunci diurutkan secara alfabetis. Indeks dari pengurutan ini digunakan sebagai penentu urutan kolom. Sebagai contoh, kunci `KUNCI` jika diurutkan menjadi `C-I-K-N-U`, menghasilkan urutan posisi: `3 5 4 1 2`.
3. **Padding:** Jika panjang pesan teks tidak kelipatan panjang kunci, pesan akan ditambahkan spasi kosong (atau byte khusus PKCS7 untuk mode file) sebagai padding di akhir.
4. **Enkripsi:** Plaintext disusun ke dalam tabel dengan lebar sesuai panjang kunci. Ciphertext kemudian dihasilkan dengan membaca isi tabel per kolom (vertikal), mengikuti urutan indeks kunci dari langkah 2.
5. **Dekripsi:** Mengembalikan ciphertext ke dalam bentuk tabel berdasarkan panjang kunci dan urutan kolom, kemudian membaca tabel secara horizontal per baris untuk mendapatkan plaintext semula.

## 3. Cara Menjalankan Program

Program ini telah dilengkapi dengan konfigurasi Docker agar dapat dijalankan dengan mudah tanpa perlu mengatur web server PHP secara manual.

**Prasyarat:**
Pastikan Docker dan Docker Compose telah terinstall di perangkat Anda.

**Langkah Instalasi:**
1. Buka terminal atau command prompt.
2. Clone repository ini dan masuk ke dalam direktorinya:
   ```bash
   git clone https://github.com/Argya53n0/Kriptografi-Permutation-Chiper.git
   cd Kriptografi-Permutation-Chiper
   ```
3. Lakukan proses build dan jalankan container:
   ```bash
   docker-compose up -d --build
   ```
4. Buka browser dan akses aplikasi melalui:
   ```
   http://localhost:8081
   ```

*(Catatan: Jika port 8081 bertabrakan dengan layanan lain, Anda dapat mengubahnya melalui file docker-compose.yml pada bagian ports).*

Untuk mematikan program, jalankan perintah:
```bash
docker-compose down
```