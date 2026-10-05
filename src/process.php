<?php
require 'Permutation.php';
require 'FileHandler.php';

$cipher = new Permutation();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $inputType = $_POST['inputType'];
    $key = $_POST['key'];

    if (empty($key)) {
        die("Kunci tidak boleh kosong.");
    }

    // PROSES INPUT TEKS
    if ($inputType == 'text') {
        $text = $_POST['inputText'];
        if ($action == 'encrypt') {
            $result = $cipher->encrypt($text, $key);
            $resultHex = bin2hex($result); // Konversi ke hex untuk keamanan display byte null
            
            // Format cipherteks kelompok 5 huruf (abaikan spasi)
            $formatted5 = trim(chunk_split(str_replace(' ', '', $result), 5, ' '));
            $noSpace = str_replace(' ', '', $result);

            echo "<h3>Hasil Enkripsi:</h3>";
            echo "<p><strong>Tanpa Spasi:</strong> " . htmlspecialchars($noSpace) . "</p>";
            echo "<p><strong>Kelompok 5 Huruf:</strong> " . htmlspecialchars($formatted5) . "</p>";
            echo "<p><em>*Note: Simpan Hex text di bawah ini untuk dekripsi:</em><br><textarea rows='4' cols='50'>" . $resultHex . "</textarea></p>";
        } else {
            // Karena teks hasil enkripsi bisa mengandung karakter unprintable, kita terima Hex
            $hexText = trim($text);
            if(ctype_xdigit($hexText)){
                 $rawText = hex2bin($hexText);
            } else {
                 $rawText = $text;
            }
            $result = $cipher->decrypt($rawText, $key);
            echo "<h3>Hasil Dekripsi:</h3>";
            echo "<textarea rows='5' cols='50'>" . htmlspecialchars($result) . "</textarea>";
        }
        echo '<br><br><a href="index.php">Kembali</a>';
    } 
    // PROSES INPUT FILE
    else if ($inputType == 'file') {
        if (!isset($_FILES['inputFile']) || $_FILES['inputFile']['error'] != 0) {
            die("Gagal mengupload file.");
        }

        $tmpPath = $_FILES['inputFile']['tmp_name'];
        $originalName = $_FILES['inputFile']['name'];

        if ($action == 'encrypt') {
            $encryptedContent = FileHandler::encryptFile($tmpPath, $originalName, $key, $cipher);
            
            // Force download file terenkripsi dengan format sembarang (misal .dat)
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="encrypted_file.dat"');
            echo $encryptedContent;
            exit;
        } else {
            $decryptedResult = FileHandler::decryptFile($tmpPath, $key, $cipher);
            $originalExt = $decryptedResult['ext'];
            $decryptedData = $decryptedResult['data'];

            // Force download file asli
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="decrypted_file.' . $originalExt . '"');
            echo $decryptedData;
            exit;
        }
    }
}
?>