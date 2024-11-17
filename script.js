document.getElementById('startUpload').addEventListener('click', function () {
    const videoFile = document.getElementById('videoFile').files[0];
    if (!videoFile) {
        alert("Lütfen bir video dosyası seçin!");
        return;
    }

    const chunkSize = 10 * 1024 * 1024; // 10MB
    const totalChunks = Math.ceil(videoFile.size / chunkSize);
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    // Progress bar'ı görünür yapıyoruz
    document.getElementById('progressContainer').classList.remove('hidden');

    let chunksUploaded = 0;  // Yüklenen parça sayısını takip edeceğiz
    let overallProgress = 0;  // Genel ilerleme yüzdesi

    function updateProgress() {
        overallProgress = Math.round((chunksUploaded / totalChunks) * 100);
        progressBar.style.width = overallProgress + '%';
        progressBar.setAttribute('aria-valuenow', overallProgress);
        progressText.textContent = `${overallProgress}% Yüklendi...`;
    }

    function uploadChunk(chunkIndex) {
        const start = chunkIndex * chunkSize;
        const end = Math.min((chunkIndex + 1) * chunkSize, videoFile.size);
        const blob = videoFile.slice(start, end);
        const formData = new FormData();

        formData.append('chunk', blob);
        formData.append('chunkIndex', chunkIndex);
        formData.append('totalChunks', totalChunks);
        formData.append('fileName', videoFile.name);

        axios.post('upload_video.php', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: function (progressEvent) {
                // Bu kısmı sadece her parça için ilerleme göstermek için kullanıyoruz
                if (progressEvent.lengthComputable) {
                    const percentCompleted = Math.round((progressEvent.loaded / progressEvent.total) * 100);
                    // Parça yükleme ilerlemesi
                    progressBar.style.width = `${overallProgress + (percentCompleted / totalChunks)}%`;
                    progressBar.setAttribute('aria-valuenow', overallProgress + (percentCompleted / totalChunks));
                    progressText.textContent = `${Math.round(overallProgress + (percentCompleted / totalChunks))}% Parça Yüklendi...`;
                }
            }
        })
        .then(function () {
            chunksUploaded++;  // Yüklenen parça sayısını artırıyoruz
            updateProgress();  // Genel ilerlemeyi güncelliyoruz

            if (chunkIndex + 1 < totalChunks) {
                uploadChunk(chunkIndex + 1); // Bir sonraki parçayı yükle
            } else {
                alert("Video başarıyla yüklendi ve birleştirildi!");
                
                const videoList = document.getElementById('uploadedVideosList');
                const videoElement = document.createElement('div');
                videoElement.classList.add('col-md-6', 'col-lg-4', 'mb-4');
                videoElement.innerHTML = `
                    <div class="bg-white p-4 rounded shadow-sm">
                        <h3 class="text-center text-xl mb-3">${videoFile.name}</h3>
                        <div id="videoPlayer_${videoFile.name}" class="w-100" style="height: 300px;"></div>
                    </div>
                `;
                videoList.appendChild(videoElement);
                new Playerjs({ id: `videoPlayer_${videoFile.name}`, file: `uploads/${videoFile.name}` });
            }
        })
        .catch(function (error) {
            alert("Bir hata oluştu: " + error);
        });
    }

    uploadChunk(0); // İlk parça ile yükleme işlemini başlat
});
