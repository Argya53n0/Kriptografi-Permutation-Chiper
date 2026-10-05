<?php
class Permutation {

    /**
     * Menghasilkan urutan permutasi berdasarkan urutan alfabetis kunci.
     * Mengembalikan array posisi: $order[$j] = posisi tujuan kolom-j di cipherblock.
     */
    public function getKeyOrder(string $key): array {
        $keyArray  = str_split(strtoupper($key));
        $sortedKey = $keyArray;
        sort($sortedKey);

        $order = [];
        foreach ($keyArray as $char) {
            $pos     = array_search($char, $sortedKey);
            $order[] = $pos;
            $sortedKey[$pos] = null; // cegah duplikat
        }
        return $order;
    }

    /**
     * Menghasilkan urutan baca kolom (untuk tampilan tabel) —
     * yaitu nomor urut (1-based) tiap huruf kunci berdasarkan alfabet.
     */
    public function getColumnNumbers(string $key): array {
        $keyArray  = str_split(strtoupper($key));
        $indexed   = [];
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
     * Menambahkan PKCS-style padding agar panjang data habis dibagi blockSize.
     */
    private function addPadding(string $data, int $blockSize): string {
        $padLength = $blockSize - (strlen($data) % $blockSize);
        return $data . str_repeat(chr($padLength), $padLength);
    }

    /**
     * Menghapus PKCS-style padding setelah dekripsi.
     */
    private function removePadding(string $data): string {
        if ($data === '') return '';
        $padLength = ord($data[strlen($data) - 1]);
        // Validasi padding: nilai pad harus antara 1 dan strlen data
        if ($padLength < 1 || $padLength > strlen($data)) return $data;
        return substr($data, 0, -$padLength);
    }

    /**
     * Enkripsi data dengan transposisi kolom berdasarkan kunci.
     */
    public function encrypt(string $data, string $key): string {
        $keyOrder  = $this->getKeyOrder($key);
        $blockSize = count($keyOrder);
        $data      = $this->addPadding($data, $blockSize);
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
     * Dekripsi data hasil enkripsi.
     */
    public function decrypt(string $data, string $key): string {
        $keyOrder  = $this->getKeyOrder($key);
        $blockSize = count($keyOrder);
        $result    = '';

        for ($i = 0; $i < strlen($data); $i += $blockSize) {
            $block      = substr($data, $i, $blockSize);
            $plainBlock = str_repeat(' ', $blockSize);
            for ($j = 0; $j < $blockSize; $j++) {
                $plainBlock[$j] = $block[$keyOrder[$j]];
            }
            $result .= $plainBlock;
        }
        return $this->removePadding($result);
    }

    /**
     * Menghasilkan representasi tabel permutasi untuk ditampilkan ke user.
     * Mengembalikan array berisi baris-baris tabel (tanpa padding char).
     */
    public function buildTable(string $data, string $key): array {
        $key       = strtoupper($key);
        $blockSize = strlen($key);
        $padded    = $this->addPadding($data, $blockSize);
        $rows      = [];

        for ($i = 0; $i < strlen($padded); $i += $blockSize) {
            $block = substr($padded, $i, $blockSize);
            $row   = [];
            for ($j = 0; $j < $blockSize; $j++) {
                $c = $block[$j];
                // tandai padding dengan simbol khusus
                $row[] = (ord($c) < 32) ? '*' : $c;
            }
            $rows[] = $row;
        }
        return $rows;
    }
}