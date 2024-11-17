<?php

$uploadDir = 'uploads/';
$maxFileSize = 10 * 1024 * 1024 * 1024; // 10 GB

// Desteklenen video formatları
$allowedExtensions = ['mp4', 'webm'];  

// Dosya uzantısını kontrol et
function checkFileExtension($fileName) {
    global $allowedExtensions;
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    return in_array($fileExtension, $allowedExtensions);
}

if (isset($_FILES['chunk']) && isset($_POST['fileName']) && isset($_POST['chunkIndex'])) {
    $fileName = $_POST['fileName'];
    $chunkIndex = (int)$_POST['chunkIndex'];
    $totalChunks = (int)$_POST['totalChunks'];

    // Dosya formatı kontrolü
    if (!checkFileExtension($fileName)) {
        echo 'Geçersiz dosya formatı! Lütfen geçerli bir video dosyası yükleyin.';
        exit;
    }

    $chunkFile = $_FILES['chunk'];
    $chunkTempPath = $chunkFile['tmp_name'];
    $finalFilePath = $uploadDir . $fileName;

    $chunkPath = $finalFilePath . '.part.' . $chunkIndex;

    if (move_uploaded_file($chunkTempPath, $chunkPath)) {
        if ($chunkIndex + 1 === $totalChunks) {
            $finalFile = fopen($finalFilePath, 'wb');
            
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = $finalFilePath . '.part.' . $i;
                $chunk = fopen($chunkPath, 'rb');
                
                while ($buffer = fread($chunk, 4096)) {
                    fwrite($finalFile, $buffer);
                }
                fclose($chunk);
                unlink($chunkPath);
            }
            fclose($finalFile);
            echo 'Yükleme tamamlandı ve birleştirildi';
        } else {
            echo 'Parça başarıyla yüklendi';
        }
    } else {
        echo 'Parça yüklenirken bir hata oluştu';
    }
} else {
    echo 'Eksik parametreler!';
}
?>
