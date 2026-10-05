<?php
class FileHandler {
    // Membaca isi file, mengenkripsi, dan menyematkan ekstensi asli
    public static function encryptFile($tmpPath, $originalName, $key, $cipher) {
        $data = file_get_contents($tmpPath);
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        
        // Enkripsi seluruh byte file
        $encryptedData = $cipher->encrypt($data, $key);
        
        // Simpan ekstensi asli di depan data terenkripsi dipisah dengan delimiter '||'
        return $ext . "||" . $encryptedData;
    }

    // Mendekripsi file dan mengambil ekstensi asli yang disematkan
    public static function decryptFile($tmpPath, $key, $cipher) {
        $fileContent = file_get_contents($tmpPath);
        
        // Pisahkan ekstensi asli dan data terenkripsi
        $parts = explode("||", $fileContent, 2);
        
        if (count($parts) < 2) {
            die("Format file tidak valid atau bukan dari program ini.");
        }
        
        $ext = $parts[0];
        $encryptedData = $parts[1];
        
        $decryptedData = $cipher->decrypt($encryptedData, $key);
        return ['ext' => $ext, 'data' => $decryptedData];
    }
}