

# **Php Chunk Upload System**

Bu proje, büyük boyutlu video dosyalarını bir web uygulaması aracılığıyla parçalara ayırarak yüklemeye olanak tanır. Yükleme işlemi esnasında her bir video parçası sunucuya gönderilir ve sunucuda bu parçalar birleştirilerek tek bir video dosyası oluşturulur. Bu yöntem birçok yerde kullanılır büyük çaplı dosya sistemlerinde video yükleme sistemlerinde,bu sistemin avantajı dosyaları parça parça gönderdiği için büyük boyutlu video dosyalarını yüklerken ağ kesintileri veya tarayıcı çökmesi gibi sorunlara karşı daha dayanıklıdır.

---

## **Proje Yapısı**

Aşağıda, projenin dosya yapısını ve her bir dosyanın ne işe yaradığını bulabilirsiniz.

```
/player/
  ├── Assets/
  │   ├── back.jpg            
  │   ├── playerjs.js         # Video oynatıcı scripti (Playerjs)
  ├── index.php               # Ana sayfa, video yükleme arayüzü
  ├── script.js               # Yükleme işlemi ve ilerleme çubuğu kontrol scripti
  ├── upload_video.php        # Parçalı yükleme işlemi ve dosya birleştirme
  └── uploads/                # Yüklenen videoların ve parçalarının depolandığı klasör
```

---

## **Projenin Amacı ve Çalışma Prensibi**

### **Büyük Boyutlu Video Yükleme**

Kullanıcıların çok büyük video dosyalarını web üzerinden yüklemeleri gerektiğinde, tek bir dosya gönderimi, dosya boyutunun ağ bağlantısını ve sunucu kapasitesini aşabileceği için sorun oluşturabilir. Bu yüzden video dosyaları, küçük parçalara (chunk) ayrılarak sırayla sunucuya gönderilir ve bu parçalar birleştirilerek tek bir dosya oluşturulur. Bu süreç, video dosyasının güvenli bir şekilde yüklenmesini sağlar ve dosya boyutu ne olursa olsun, yükleme işlemi tamamlanabilir.

---

## **Adım Adım Yükleme Süreci**

### **1. Kullanıcı Dosyayı Seçer ve Yüklemeyi Başlatır**

Kullanıcı, `index.php` sayfasındaki dosya seçme butonundan video dosyasını seçer ve ardından **"Yüklemeyi Başlat"** butonuna basarak yükleme işlemini başlatır. Bu işlem, JavaScript dosyasındaki `startUpload` fonksiyonu ile tetiklenir.

- **Dosya Seçimi**: HTML input elemanlarıyla dosya seçimi yapılır. `#videoFile` input alanı kullanılarak video dosyası seçilir.
- **İlerleme Çubuğu**: Yükleme başladıktan sonra, JavaScript'teki ilerleme çubuğu (progress bar) görünür hale gelir ve her yükleme parçası için güncellenir.

### **2. Video Parçalara Ayrılır ve Yüklenir**

Video dosyası, belirli bir boyuta sahip parçalara ayrılır (örneğin her parça 10 MB). Bu parçalar sırayla sunucuya gönderilir. 

- **Chunk Boyutu**: JavaScript'teki `chunkSize` değişkeni, her parçanın boyutunu belirler. Bu örnekte 10 MB olarak ayarlanmıştır.  
- **Parça Yükleme**: JavaScript fonksiyonu `uploadChunk(chunkIndex)` her bir parçayı sunucuya gönderir. Her parça için bir POST isteği yapılır ve video parçası `FormData` kullanılarak PHP sunucusuna yüklenir.

  ```javascript
  function uploadChunk(chunkIndex) {
      const start = chunkIndex * chunkSize;
      const end = Math.min((chunkIndex + 1) * chunkSize, videoFile.size);
      const blob = videoFile.slice(start, end); // Parçayı ayır
      const formData = new FormData();

      formData.append('chunk', blob); // Parçayı gönder
      formData.append('chunkIndex', chunkIndex); // Parça indeksini gönder
      formData.append('totalChunks', totalChunks); // Toplam parça sayısını gönder
      formData.append('fileName', videoFile.name); // Dosya adını gönder
  }
  ```

### **3. Yükleme İlerleme Çubuğu Güncellenir**

Her parça yüklendikçe, video yükleme ilerlemesi (progress) güncellenir. Yükleme ilerlemesi, sunucudan gelen `onUploadProgress` verisiyle takip edilir. Bu veriye göre ilerleme çubuğu ve metni güncellenir.

- **Progres Çubuğu Güncellemesi**: `updateProgress()` fonksiyonu, her parça yüklendikçe genel ilerlemeyi hesaplar ve görsel olarak ilerleme çubuğunu günceller.

```javascript
function updateProgress() {
    overallProgress = Math.round((chunksUploaded / totalChunks) * 100);
    progressBar.style.width = overallProgress + '%';
    progressText.textContent = `${overallProgress}% Yüklendi...`;
}
```

### **4. PHP Sunucusu Parçaları Alır ve Birleştirir**

Her bir parça sunucuya yüklendikçe, PHP backend'inde bu parçalar geçici olarak `uploads/` klasörüne `.part.{chunkIndex}` formatında kaydedilir.

#### PHP backend işlemleri:
- **Parça Alımı**: `upload_video.php` dosyası, her parça için yükleme işlemini alır ve geçici olarak depolar. Parçalar PHP'nin `$_FILES` dizisi üzerinden alınır.
- **Parçaların Birleştirilmesi**: Son parça yüklendiğinde, PHP dosyası, tüm parçaları okur ve birleştirir. Sonuç olarak, tam video dosyası oluşturulur.

```php
if ($chunkIndex + 1 === $totalChunks) {
    $finalFile = fopen($finalFilePath, 'wb');
    for ($i = 0; $i < $totalChunks; $i++) {
        $chunkPath = $finalFilePath . '.part.' . $i;
        $chunk = fopen($chunkPath, 'rb');
        while ($buffer = fread($chunk, 4096)) {
            fwrite($finalFile, $buffer);
        }
        fclose($chunk);
        unlink($chunkPath); // Parçayı sil
    }
    fclose($finalFile);
    echo 'Yükleme tamamlandı ve birleştirildi';
}
```

- **Dosya Uzantısı Kontrolü**: PHP dosyası, yalnızca `.mp4` ve `.webm` uzantılı video dosyalarını kabul eder. Bu, `checkFileExtension()` fonksiyonu ile yapılır.

```php
function checkFileExtension($fileName) {
    global $allowedExtensions;
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    return in_array($fileExtension, $allowedExtensions);
}
```

### **5. Video Oynatma**

Yüklenen video dosyası tamamlandığında, kullanıcıya video oynatma imkanı sunulur. Yüklenen videolar, `Playerjs` kütüphanesi kullanılarak oynatılır. PHP tarafından yüklenen videolar, ana sayfada listelenir ve her video için bir video oynatıcı eklenir.

```php
<?php
$uploadedVideos = glob('uploads/*.{mp4,webm}', GLOB_BRACE);
foreach ($uploadedVideos as $video):
?>
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="card-header text-center">
        <h5 class="text-lg font-semibold text-gray-800 mb-3"><?= basename($video); ?></h5>
    </div>
    <div class="card-body p-3">
        <div id="videoPlayer_<?= basename($video); ?>" class="mb-3"></div>
        <script>
            new Playerjs({
                id: 'videoPlayer_<?= basename($video); ?>',
                file: 'uploads/<?= basename($video); ?>',
            });
        </script>
    </div>
</div>
<?php endforeach; ?>
```

---

## **Kullanıcı Arayüzü ve Tasarım**

Proje, basit ve kullanıcı dostu bir arayüz sunar. Ana sayfa, Tailwind CSS ile stilize edilmiştir. Yükleme ilerlemesi görsel olarak gösterilir ve kullanıcıya video dosyasını yükledikten sonra başarıyla tamamlandığı bildirilir.

- **Progress Bar**: Yükleme sırasında videonun ne kadarının yüklendiği görsel olarak gösterilir.
- **Card Layout**: Yüklenen videolar grid sistemiyle sıralanır ve her video küçük bir kart şeklinde sunulur.

---

## **Kurulum ve Çalıştırma**

1. **Sunucu Hazırlığı**:
   - PHP ve Apache/Nginx yüklü bir sunucu gereklidir.
   - `uploads/` klasörünün yazılabilir olduğundan emin olun (chmod 777 veya benzeri).
   - PHP'nin dosya

her ne kadar chunk sistemi kullanarak yükleme yapsakda yükleme limitlerinin uygun olduğuna emin olun. `php.ini` dosyasındaki `upload_max_filesize` ve `post_max_size` değerlerini ihtiyacınıza göre ayarlayın.
   
2. **Proje Dosyalarını Yükleyin**:
   - Proje dosyalarını sunucuya yükleyin.

3. **Tarayıcıda Çalıştırma**:
   - `index.php` dosyasını tarayıcıda açın ve video yüklemeyi test edin.

