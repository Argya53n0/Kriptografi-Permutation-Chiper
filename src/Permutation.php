<?php
class Permutation {

    /**
     * Menghasilkan urutan permutasi berdasarkan urutan alfabetis kunci.
     */
    public function getKeyOrder(string $key): array {
        $keyArray  = str_split(strtoupper($key));
        $sortedKey = $keyArray;
        sort($sortedKey);

        $order = [];
        foreach ($keyArray as $char) {
            $pos     = array_search($char, $sortedKey);
            $order[] = $pos;
            $sortedKey[$pos] = null;
        }
        return $order;
    }

    /**
     * Nomor urut kolom (1-based) berdasarkan alfabet kunci.
     */
    public function getColumnNumbers(string $key): array {
        $keyArray = str_split(strtoupper($key));
        $indexed  = [];
        foreach ($keyArray as $i => $char) {
            $indexed[] = ['char' => $char, 'idx' => $i];
        }
        usort($indexed, fn($a, $b) => strcmp($a['char'], $b['char']) ?: $a['idx'] <=> $b['idx']);

        $colNums = array_fill(0, count($keyArray), 0);
        foreach ($indexed as $rank => $item) {
            $colNums[$item['idx']] = $rank + 1;
        }
        return $colNums;
    }

    /**
     * Padding: Gunakan PKCS untuk file, spasi untuk teks biasa (agar aman di copy-paste).
     */
    private function addPadding(string $data, int $blockSize, bool $isText = false): string {
        $padLength = $blockSize - (strlen($data) % $blockSize);
        if ($padLength === $blockSize) {
            $padLength = 0; // Jika sudah kelipatan, PKCS tetap nambah 1 blok, tapi untuk teks kita biarkan saja
            if (!$isText) $padLength = $blockSize;
            else return $data;
        }
        $padChar = $isText ? ' ' : chr($padLength);
        return $data . str_repeat($padChar, $padLength);
    }

    /**
     * Hapus padding setelah dekripsi.
     */
    private function removePadding(string $data, bool $isText = false): string {
        if ($data === '') return '';
        if ($isText) {
            return rtrim($data, ' ');
        }
        $padLength = ord($data[strlen($data) - 1]);
        if ($padLength < 1 || $padLength > strlen($data)) return $data;
        return substr($data, 0, -$padLength);
    }

    /**
     * Enkripsi: transposisi kolom.
     */
    public function encrypt(string $data, string $key, bool $isText = false): string {
        $keyOrder  = $this->getKeyOrder($key);
        $blockSize = count($keyOrder);
        $data      = $this->addPadding($data, $blockSize, $isText);
        $result    = '';

        for ($i = 0; $i < strlen($data); $i += $blockSize) {
            $block       = substr($data, $i, $blockSize);
            $cipherBlock = str_repeat(' ', $blockSize);
            for ($j = 0; $j < $blockSize; $j++) {
                $cipherBlock[$keyOrder[$j]] = $block[$j];
            }
            $result .= $cipherBlock;
        }
        return $result;
    }

    /**
     * Dekripsi: kebalikan transposisi kolom.
     */
    public function decrypt(string $data, string $key, bool $isText = false): string {
        $keyOrder  = $this->getKeyOrder($key);
        $blockSize = count($keyOrder);

        // Mencegah error jika user memasukkan teks yang terpotong paddingnya
        if (strlen($data) % $blockSize !== 0) {
            $padLen = $blockSize - (strlen($data) % $blockSize);
            $data .= str_repeat(' ', $padLen);
        }

        $result    = '';

        for ($i = 0; $i < strlen($data); $i += $blockSize) {
            $block      = substr($data, $i, $blockSize);
            $plainBlock = str_repeat(' ', $blockSize);
            for ($j = 0; $j < $blockSize; $j++) {
                $plainBlock[$j] = $block[$keyOrder[$j]];
            }
            $result .= $plainBlock;
        }
        return $this->removePadding($result, $isText);
    }

    /**
     * Tabel proses ENKRIPSI.
     * Baris = plaintext per blok, kolom = posisi kunci.
     *
     * @param  string $format  'text' | 'binary' | 'hex'
     * @return array  Baris berisi cell ['value', 'isPad']
     */
    public function buildTable(string $data, string $key, string $format = 'text'): array {
        $key       = strtoupper($key);
        $blockSize = strlen($key);
        $padded    = $this->addPadding($data, $blockSize);
        $padByte   = ord($padded[strlen($padded) - 1]);
        $rows      = [];

        for ($i = 0; $i < strlen($padded); $i += $blockSize) {
            $block = substr($padded, $i, $blockSize);
            $row   = [];
            for ($j = 0; $j < $blockSize; $j++) {
                $byte  = $block[$j];
                $bVal  = ord($byte);
                $isPad = ($bVal === $padByte && ($i + $j) >= strlen($data));
                $row[] = $this->formatCell($byte, $bVal, $isPad, $format);
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Tabel proses DEKRIPSI.
     *
     * Ciphertext dibagi ke kolom sesuai urutan kunci, lalu
     * kolom-kolom disusun kembali ke urutan plaintext untuk
     * menampilkan bagaimana plaintext dipulihkan baris per baris.
     *
     * @param  string $cipherRaw  Ciphertext (raw bytes)
     * @param  string $key
     * @param  string $plainData  Hasil dekripsi (untuk tandai padding)
     * @return array  Baris berisi cell ['value', 'isPad']
     */
    public function buildDecryptTable(string $cipherRaw, string $key, string $plainData): array {
        $key       = strtoupper($key);
        $keyOrder  = $this->getKeyOrder($key);
        $blockSize = strlen($key);

        // Cegah error jika panjang data tidak pas
        if (strlen($cipherRaw) % $blockSize !== 0) {
            $padLen = $blockSize - (strlen($cipherRaw) % $blockSize);
            $cipherRaw .= str_repeat(' ', $padLen);
        }

        $numRows   = (int) ceil(strlen($cipherRaw) / $blockSize);
        $plainLen  = strlen($plainData);

        // Kumpulkan kolom-kolom plaintext dari ciphertext.
        // keyOrder[$j] = posisi di cipherblock → plaintext posisi $j
        $cols = [];
        for ($j = 0; $j < $blockSize; $j++) {
            $cipherCol = $keyOrder[$j];
            $cols[$j]  = [];
            for ($r = 0; $r < $numRows; $r++) {
                $pos       = $r * $blockSize + $cipherCol;
                $cols[$j][] = isset($cipherRaw[$pos]) ? $cipherRaw[$pos] : chr(0);
            }
        }

        // Bangun baris tabel (urutan kolom = urutan plaintext)
        $rows = [];
        for ($r = 0; $r < $numRows; $r++) {
            $row = [];
            for ($j = 0; $j < $blockSize; $j++) {
                $byte  = $cols[$j][$r];
                $bVal  = ord($byte);
                $pos   = $r * $blockSize + $j;
                $isPad = ($pos >= $plainLen);
                $row[] = $this->formatCell($byte, $bVal, $isPad, 'text');
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Helper: format satu cell tabel berdasarkan format tampilan.
     */
    private function formatCell(string $byte, int $bVal, bool $isPad, string $format): array {
        if ($format === 'binary') {
            $display = str_pad(decbin($bVal), 8, '0', STR_PAD_LEFT);
            if ($isPad) $display = '(pad)';
        } elseif ($format === 'hex') {
            $display = strtoupper(str_pad(dechex($bVal), 2, '0', STR_PAD_LEFT));
            if ($isPad) $display = '**';
        } else {
            $display = ($bVal < 32 || $bVal > 126 || $isPad) ? '*' : $byte;
            $isPad   = ($display === '*');
        }
        return ['value' => $display, 'isPad' => $isPad];
    }
}