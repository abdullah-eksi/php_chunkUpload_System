<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Büyük Boyutlu Video Yükleme</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="text/javascript" src="/player/Assets/playerjs.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        .progress-bar {
            transition: width 0.5s ease-in-out;
        }

        #progressText {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .progress-container {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .card-custom {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-gray-100">

    <!-- Banner Section -->
    <header style="background-image: url('/player/Assets/back.jpg'); background-repeat:no-repeat;background-size:cover;background-position: center;" class="bg-blue-600 text-white py-7 text-center">
        <h1 class="text-4xl font-semibold">Büyük Boyutlu Video Yükleme</h1>
        <p class="text-xl">büyük boyutlu video yükleme yöntemi</p>
    </header>

    <div class="container mx-auto my-10">


        <div class="grid grid-cols-1 md:grid-cols-1 gap-12 mb-12">

            <!-- Video Upload Card -->
            <div class="card-custom bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-semibold text-gray-800 mb-5 text-center">Video Yükleme</h2>

                <div class="text-center">
                    <input type="file" id="videoFile" accept="video/mp4,video/webm"
                        class="mb-4 px-4 py-3 border rounded-lg shadow-md hover:shadow-lg transition duration-300 ease-in-out">
                    <br>
                    <button id="startUpload" class="btn bg-blue-600 text-white px-6 py-3 rounded-lg shadow-lg hover:bg-blue-700 transition duration-300 ease-in-out">Yüklemeyi Başlat</button>
                </div>

                <div id="progressContainer" class="progress-container hidden mt-6">
                    <div class="progress w-full bg-gray-200 rounded-full">
                        <div id="progressBar" class="progress-bar bg-blue-600 h-2 rounded-full" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p id="progressText" class="text-center text-lg text-gray-700 mt-2">Yükleme Başladı...</p>
                </div>
            </div>

            <!-- Uploaded Videos Card -->
            <div class="card-custom bg-white p-3 rounded-lg shadow-lg">
                <h2 class="text-2xl font-semibold text-gray-800 mb-5 text-center">Yüklenen Videolar</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="uploadedVideosList">
                    <?php
                    // Sadece .mp4 ve .webm dosyalarını listele
                    $uploadedVideos = glob('uploads/*.{mp4,webm}', GLOB_BRACE);
                    foreach ($uploadedVideos as $video):
                        $videoExtension = pathinfo($video, PATHINFO_EXTENSION);
                    ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="card-header text-center ">
                        <h5 class="text-lg font-semibold text-gray-800 mb-3"><?= basename($video); ?></h5>
                        </div>
                        <div class="card-body p-3">
                          
                            <div id="videoPlayer_<?= basename($video); ?>" class=" mb-3"></div>
                            <script>
                                new Playerjs({
                                    id: 'videoPlayer_<?= basename($video); ?>',
                                    file: 'uploads/<?= basename($video); ?>',
                                });
                            </script>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer Section -->
    <footer class="bg-blue-600 text-white py-4 text-center">
        <p class="text-sm"> Tüm Hakları Saklıdır.</p>
    </footer>

    <!-- Video Yükleme Scripti -->
    <script src="script.js"></script>

</body>

</html>
