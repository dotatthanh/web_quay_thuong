<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Quay thưởng</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: url('./background.jpg') no-repeat center center fixed;
            background-size: 100% 100%;
            height: 100vh;
            color: #fff;
            font-family: 'Avo', sans-serif;
        }

        @font-face {
            font-family: 'Dancing Script';
            src: url('/fonts/DancingScript-VariableFont_wght.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
    </style>
</head>

<body class="overflow-y-hidden">
    <div class="text-center text-[48px] mt-[230px] font-medium" style="font-family: 'Dancing Script';">
        <p>QUAY SỐ TRÚNG THƯỞNG</p>
        <p>CHÀO XUÂN MỚI - ĐÓN LỘC MỚI</p>
    </div>

    <form action="" class="text-center mt-[70px] text-[32px]">
        <select name="type"
            class="border border-gray-300 bg-transparent rounded p-2 w-[450px] px-3 text-center h-[64px]">
            <option value="GIẢI KHUYẾN KHÍCH">GIẢI KHUYẾN KHÍCH</option>
            <option value="GIẢI BA">GIẢI BA</option>
            <option value="GIẢI NHÌ">GIẢI NHÌ</option>
            <option value="GIẢI NHẤT">GIẢI NHẤT</option>
            <option value="GIẢI ĐẶC BIỆT">GIẢI ĐẶC BIỆT</option>
        </select>
        <button type="button" class="bg-[#e47093] text-white py-2 px-4 rounded font-medium ml-[15px]"
            onclick="startRandom()">QUAY
            THƯỞNG</button>
    </form>

    <div class="mt-[100px] px-[20px]">
        <p class="text-center text-[48px] font-medium">DANH SÁCH NGƯỜI MAY MẮN TRÚNG GIẢI</p>

        <div class="flex flex-wrap gap-[15px] justify-center mt-[30px]">
            @for ($i = 0; $i < 10; $i++)
                <div class="text-center border border-[#fff] w-[330px] p-[15px] flex-shrink-0 relative">
                    <p>SỐ {{ $i + 1 }}</p>
                    <p class="text-[26px]">ĐẶNG THỊ KIM LỆ THỦY</p>
                    <p>* B1 - Công nhân *</p>
                    <button
                        class="absolute right-[10px] top-[10px] font-bold text-[red] bg-white w-[25px] h-[25px] leading-[25px] border-none rounded-[5px]">X</button>
                </div>
            @endfor
        </div>
    </div>


    <div class="absolute top-0 bottom-0 right-0 left-0 hidden" id="fade"></div>
    <div class="absolute w-full flex justify-center top-[530px] hidden" id="resultBox">
        <div class="text-center border border-[#fff] p-[15px] w-[550px]">
            <p class="text-[26px]" id="result"></p>
        </div>
    </div>

    <script>
        let isRunning = false;

        // Danh sách người chơi
        let players = [
            "ĐẶNG THỊ KIM LỆ THỦY - B1 - Công nhân",
            "Đỗ Tất Thành - B1 - Công nhân",
            "Nam - B1 - Công nhân",
            "Thành - B1 - Công nhân",
            "ĐẶNG  - B1 - Công nhân",
            "Tuấn - B1 - Công nhân",
            "Huy - B1 - Công nhân"
        ];

        function startRandom() {
            const resultBox = document.getElementById('resultBox');
            const fade = document.getElementById('fade');
            const result = document.getElementById('result');
            resultBox.classList.remove('hidden');

            fade.classList.add('bg-gray-900/90');
            fade.classList.remove('hidden');
            
            if (isRunning) return;
            if (players.length < 2) {
                alert("Không đủ người chơi để quay 2 lần!");
                return;
            }

            isRunning = true;

            // let isRunning = 3;
            // Hàm quay thưởng
            const performRandom = (callback) => {
                let randomInterval;
                let currentIndex = -1;

                // Hiệu ứng nháy ngẫu nhiên
                randomInterval = setInterval(() => {
                    currentIndex = Math.floor(Math.random() * players.length);
                    result.textContent = players[currentIndex];
                }, 100);

                // Dừng quay sau 3 giây
                setTimeout(() => {
                    clearInterval(randomInterval);
                    const winner = players[currentIndex];
                    players.splice(currentIndex, 1); // Xóa người chiến thắng khỏi danh sách
                    setTimeout(() => {
                        alert(`Người chiến thắng: ${winner}`);
                        callback(); // Gọi callback sau khi kết thúc
                    }, 500);
                }, 3000);
            };

            // Quay 3 lần liên tiếp
            const spinThreeTimes = async () => {
                for (let i = 0; i < 3; i++) {
                    if (players.length === 0) {
                        alert("Không còn người chơi để quay tiếp!");
                        resultBox.textContent = "Kết quả";
                        isRunning = false;
                        return;
                    }
                    await performRandom(); // Đợi quay xong trước khi quay tiếp
                }
                resultBox.textContent = "Kết quả";
                isRunning = false;
            };

            // Bắt đầu quay 3 lần
            spinThreeTimes();
        }
    </script>
</body>

</html>
