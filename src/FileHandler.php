<?php
class FileHandler {

    /**
     * Membaca isi file, mengenkripsi byte-per-byte, dan menyematkan ekstensi asli.
     * Format output: "<ext>||<encrypted_data>"
     */
    public static function encryptFile(string $tmpPath, string $originalName, string $key, Permutation $cipher): string {
        $data = file_get_contents($tmpPath);

        $encryptedData = $cipher->encrypt($data, $key);

        // Simpan nama file asli di depan data, dipisah delimiter
        return $originalName . '||' . $encryptedData;
    }

    /**
     * Mendekripsi file hasil enkripsi dan mengambil ekstensi asli.
     * Mengembalikan ['ext' => string, 'data' => string] atau null jika format tidak valid.
     *
     * @return array{ext: string, data: string}|null
     */
    public static function decryptFile(string $tmpPath, string $key, Permutation $cipher): ?array {
        $fileContent = file_get_contents($tmpPath);

        // Pisahkan ekstensi dan data terenkripsi
        $delimPos = strpos($fileContent, '||');
        if ($delimPos === false || $delimPos === 0) {
            return null; // Format tidak valid
        }

        $originalName  = substr($fileContent, 0, $delimPos);
        $encryptedData = substr($fileContent, $delimPos + 2);

        // Bersihkan nama file dari karakter aneh untuk keamanan (opsional)
        $originalName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $originalName);

        $decryptedData = $cipher->decrypt($encryptedData, $key);

        return [
            'originalName' => $originalName,
            'data'         => $decryptedData,
        ];
    }
}