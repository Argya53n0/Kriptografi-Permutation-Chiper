# 🔐 Permutation Cipher

Aplikasi web untuk **enkripsi dan dekripsi** data menggunakan algoritma **Permutation Cipher (Transposisi Kolom)**, dibangun dengan PHP dan dideploy menggunakan Docker.

---

## 📋 Daftar Isi

- [Tentang Program](#-tentang-program)
- [Teori Permutation Cipher](#-teori-permutation-cipher)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Struktur Proyek](#-struktur-proyek)
- [Instalasi & Menjalankan Program](#-instalasi--menjalankan-program)
- [Cara Penggunaan](#-cara-penggunaan)
- [Contoh Enkripsi](#-contoh-enkripsi)

---

## 💡 Tentang Program

Program ini adalah implementasi **Permutation Cipher** berbasis web yang mendukung dua mode input:

- **Teks** — Enkripsi atau dekripsi pesan teks secara langsung di browser
- **File** — Enkripsi dan dekripsi semua jenis file (dokumen, gambar, audio, dll.)

Fitur utama:
| Fitur | Keterangan |
|-------|-----------|
| 🔒 Enkripsi Teks | Mengacak karakter berdasarkan kunci permutasi |
| 🔓 Dekripsi Teks | Mengembalikan teks terenkripsi ke bentuk aslinya |
| 📁 Enkripsi File | Mengenkripsi seluruh byte file, download otomatis sebagai `.dat` |
| 📂 Dekripsi File | Mengembalikan file ke format aslinya, download otomatis |
| 🖥️ Web UI Modern | Antarmuka gelap modern dengan drag & drop support |
| 🐳 Docker Support | Mudah dijalankan tanpa perlu install PHP secara manual |

---

## 📚 Teori Permutation Cipher

### Apa itu Permutation Cipher?

**Permutation Cipher** (atau **Columnar Transposition Cipher**) adalah jenis cipher klasik yang termasuk dalam kategori **cipher transposisi**. Berbeda dengan cipher substitusi yang mengganti karakter, cipher transposisi **mengacak posisi** karakter tanpa mengubah karakternya sendiri.

### Cara Kerja

Algoritma ini menggunakan **kunci berupa kata** untuk menentukan urutan kolom permutasi.

#### 1. Proses Enkripsi

**Langkah-langkah:**

1. Tentukan kunci, misalnya `KUNCI`
2. Urutkan huruf kunci secara alfabetis untuk mendapat nomor kolom:

```
Kunci   :  K  U  N  C  I
Alfabet :  C  I  K  N  U
Urutan  :  3  5  4  1  2
```

3. Tulis plaintext ke dalam baris dengan lebar = panjang kunci:

```
Plaintext: HALO DUNIA
(Diisi ke tabel, padding jika perlu)

   K  U  N  C  I
   3  5  4  1  2
   H  A  L  O  D
   U  N  I  A  X  (X = padding)
```

4. Baca kolom sesuai urutan numerik (1, 2, 3, 4, 5):

```
Kolom 1 (C): O A
Kolom 2 (I): D X
Kolom 3 (K): H U
Kolom 4 (N): L I
Kolom 5 (U): A N

Ciphertext: OADXHULIAN
```

#### 2. Proses Dekripsi

Proses kebalikan dari enkripsi:
1. Hitung jumlah baris = `ceil(panjang ciphertext / panjang kunci)`
2. Isi kembali kolom-kolom sesuai urutan kunci
3. Baca baris per baris untuk mendapat plaintext
4. Hapus padding di akhir

### Padding

Karena teks tidak selalu habis dibagi panjang kunci, program menambahkan **PKCS-style padding**:
- Sisa slot diisi dengan karakter `chr(n)` dimana `n` = jumlah byte padding
- Saat dekripsi, padding otomatis dihapus

### Keamanan

> Permutation Cipher adalah cipher **klasik** dan **tidak aman** untuk penggunaan kriptografi modern. Mudah dipecahkan dengan analisis frekuensi atau brute force. Gunakan hanya untuk **keperluan edukasi**.

| Aspek | Keterangan |
|-------|-----------|
| Tipe | Cipher Transposisi |
| Kunci | Kata/frasa alfanumerik |
| Kelemahan | Rentan terhadap analisis frekuensi |
| Penggunaan | Edukasi kriptografi |

---

## 🛠️ Teknologi yang Digunakan

| Teknologi | Versi | Fungsi |
|-----------|-------|--------|
| PHP | 8.2 | Backend & logika enkripsi |
| Apache | 2.4 | Web server |
| Docker | latest | Containerisasi aplikasi |
| HTML/CSS/JS | - | Frontend & UI |
| Google Fonts | - | Tipografi (Inter, JetBrains Mono) |

---

## 📂 Struktur Proyek

```
Kriptografi-Permutation-Chiper/
│
├── Dockerfile              # Konfigurasi Docker image
├── docker-compose.yml      # Konfigurasi Docker Compose
├── README.md               # Dokumentasi ini
│
└── src/                    # Source code aplikasi
    ├── index.php           # Halaman utama (UI form)
    ├── process.php         # Handler POST request (enkripsi/dekripsi)
    ├── Permutation.php     # Class inti algoritma Permutation Cipher
    ├── FileHandler.php     # Class handler enkripsi/dekripsi file
    │
    ├── css/
    │   └── style.css       # Stylesheet (dark mode, animasi)
    │
    └── uploads/            # Folder temporary upload file
```

### Penjelasan File Utama

| File | Deskripsi |
|------|-----------|
| `Permutation.php` | Implementasi core algoritma: `encrypt()`, `decrypt()`, padding |
| `FileHandler.php` | Membaca file binary, enkripsi byte-per-byte, simpan ekstensi asli |
| `process.php` | Menerima form POST, memanggil cipher, mengirim hasil/download |
| `index.php` | UI web dengan form interaktif, tab switching, drag & drop |

---

## 🚀 Instalasi & Menjalankan Program

### Prasyarat

Pastikan sudah terinstall:
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) — untuk Windows/Mac/Linux

### Langkah Instalasi

**1. Clone repository**
```bash
git clone https://github.com/Argya53n0/Kriptografi-Permutation-Chiper.git
cd Kriptografi-Permutation-Chiper
```

**2. Jalankan dengan Docker Compose**
```bash
docker-compose up -d --build
```

**3. Buka di browser**
```
http://localhost:8081
```

**4. Untuk menghentikan aplikasi**
```bash
docker-compose down
```

### Troubleshooting Port

Jika port `8081` sudah digunakan, ubah di `docker-compose.yml`:
```yaml
ports:
  - "9090:80"   # Ganti 8081 dengan port yang tersedia
```
Lalu jalankan ulang:
```bash
docker-compose down
docker-compose up -d
```

---

## 📖 Cara Penggunaan

### Enkripsi Teks

1. Buka `http://localhost:8081`
2. Pilih mode **Enkripsi** (tab kiri atas)
3. Pilih jenis input **Teks**
4. Masukkan **kunci permutasi** (contoh: `SECRET`)
5. Masukkan **teks** yang ingin dienkripsi
6. Klik tombol **Enkripsi Sekarang**
7. Hasil akan ditampilkan dalam format:
   - **Tanpa Spasi** — ciphertext mentah
   - **Kelompok 5 Huruf** — format standar cipher klasik
   - **Hex** — simpan ini untuk proses dekripsi!

> **Penting:** Simpan output **Hex** untuk keperluan dekripsi, karena hasil enkripsi bisa mengandung karakter yang tidak terbaca.

### Dekripsi Teks

1. Pilih mode **Dekripsi**
2. Pilih jenis input **Teks**
3. Masukkan **kunci yang sama** saat enkripsi
4. Paste **teks Hex** hasil enkripsi ke kolom input
5. Klik **Dekripsi Sekarang**
6. Teks asli akan ditampilkan

### Enkripsi File

1. Pilih mode **Enkripsi**
2. Pilih jenis input **File**
3. Masukkan **kunci permutasi**
4. Upload file dengan klik atau **drag & drop**
5. Klik **Enkripsi Sekarang**
6. File terenkripsi akan otomatis ter-download sebagai `encrypted_file.dat`

### Dekripsi File

1. Pilih mode **Dekripsi**
2. Pilih jenis input **File**
3. Masukkan **kunci yang sama** saat enkripsi
4. Upload file `.dat` hasil enkripsi
5. Klik **Dekripsi Sekarang**
6. File asli akan otomatis ter-download dengan ekstensi aslinya

---

## 🔢 Contoh Enkripsi

### Contoh Enkripsi Teks

**Input:**
```
Plaintext : HALO DUNIA
Kunci     : KUNCI
```

**Proses:**
```
Urutan kolom berdasarkan alfabet kunci:
K=3, U=5, N=4, C=1, I=2

Tabel permutasi:
 K  U  N  C  I
[3][5][4][1][2]
 H  A  L  O  D
 U  N  I  A  X   <- X = padding

Baca per kolom (urutan 1,2,3,4,5):
Col 1 (C) -> O A
Col 2 (I) -> D X
Col 3 (K) -> H U
Col 4 (N) -> L I
Col 5 (U) -> A N
```

**Output:**
```
Ciphertext : OADXHULIAN
Kelompok 5 : OADXH ULIAN
```

---

## 👨‍💻 Kontributor

| Nama | Branch |
|------|--------|
| Argya Seno | `Seno` |

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan **tugas mata kuliah Kriptografi**. Bebas digunakan untuk tujuan edukasi.