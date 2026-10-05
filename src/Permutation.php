<?php
class Permutation {
    // Menghasilkan urutan permutasi berdasarkan abjad kunci
    private function getKeyOrder($key) {
        $keyArray = str_split($key);
        $sortedKey = $keyArray;
        sort($sortedKey); // Urutkan abjad

        $order = [];
        foreach ($keyArray as $char) {
            $pos = array_search($char, $sortedKey);
            $order[] = $pos;
            $sortedKey[$pos] = null; // Hindari duplikasi karakter yang sama
        }
        return $order;
    }

    // Tambahkan padding agar jumlah byte habis dibagi panjang kunci
    private function addPadding($data, $blockSize) {
        $padLength = $blockSize - (strlen($data) % $blockSize);
        return $data . str_repeat(chr($padLength), $padLength);
    }

    // Hapus padding setelah dekripsi
    private function removePadding($data) {
        $padLength = ord($data[strlen($data) - 1]);
        return substr($data, 0, -$padLength);
    }

    public function encrypt($data, $key) {
        $keyOrder = $this->getKeyOrder($key);
        $blockSize = count($keyOrder);
        $data = $this->addPadding($data, $blockSize);
        $result = '';

        for ($i = 0; $i < strlen($data); $i += $blockSize) {
            $block = substr($data, $i, $blockSize);
            $cipherBlock = str_pad('', $blockSize, ' ');
            for ($j = 0; $j < $blockSize; $j++) {
                $cipherBlock[$keyOrder[$j]] = $block[$j];
            }
            $result .= $cipherBlock;
        }
        return $result;
    }

    public function decrypt($data, $key) {
        $keyOrder = $this->getKeyOrder($key);
        $blockSize = count($keyOrder);
        $result = '';

        for ($i = 0; $i < strlen($data); $i += $blockSize) {
            $block = substr($data, $i, $blockSize);
            $plainBlock = str_pad('', $blockSize, ' ');
            for ($j = 0; $j < $blockSize; $j++) {
                $plainBlock[$j] = $block[$keyOrder[$j]];
            }
            $result .= $plainBlock;
        }
        return $this->removePadding($result);
    }
}