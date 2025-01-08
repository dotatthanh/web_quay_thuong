<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
        <select name="type" id="type" onchange="clearResult()"
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

    <div class="mt-[100px] px-[20px] hidden" id="winners-list">
        <p class="text-center text-[48px] font-medium">DANH SÁCH NGƯỜI MAY MẮN TRÚNG GIẢI</p>

        <div class="flex flex-wrap gap-[15px] justify-center mt-[30px]" id="box-show-result"></div>
    </div>


    {{-- <div class="absolute top-0 bottom-0 right-0 left-0 hidden" id="fade"></div> --}}
    <div class="absolute w-full flex justify-center top-[530px] hidden" id="resultBox">
        <div class="text-center border border-[#fff] p-[15px] w-[550px]">
            <p class="text-[26px]" id="result"></p>
        </div>
    </div>

    <script>
        let players = @json($players);
        let isRunning = false;
        const type = document.getElementById('type');
        const boxShowResult = document.getElementById('box-show-result');
        const result = document.getElementById('result');
        const resultBox = document.getElementById('resultBox');
        const winnersList = document.getElementById('winners-list');

        function calcNumberOfSpins(type) {
            let total = 0;
            let numberOfSpins = 0;

            switch (type) {
                case 'GIẢI KHUYẾN KHÍCH':
                    // total = 40;
                    total = 5;
                    numberOfSpins = 2;
                    // Khối xưởng, Khối phòng ban, LĐCH, Khách mời
                    break;
                case 'GIẢI BA':
                    // total = 20;
                    total = 3;
                    numberOfSpins = 10;
                    // Khối xưởng, Khối phòng ban, LĐCH, Khách mời
                    break;
                case 'GIẢI NHÌ':
                    // total = 5;
                    total = 2;
                    numberOfSpins = 1;
                    // Khối xưởng, Khối phòng ban
                    break;
                case 'GIẢI NHẤT':
                    // total = 2;
                    total = 1;
                    numberOfSpins = 1;
                    // Khối xưởng, Khối phòng ban
                    break;
                case 'GIẢI ĐẶC BIỆT':
                    total = 1;
                    numberOfSpins = 1;
                    // Khối xưởng
                    break;
            }

            return [total, numberOfSpins];
        }

        async function checkTotalWinner(total, type) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const response = await fetch('/check-total-winner', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        total, type
                    }),
                });

                if (!response.ok) {
                    throw new Error(`Lỗi khi gửi thông tin checkTotalWinner: ${response.status}`);
                }

                const data = await response.json();
                if (data.data.result == false) {
                    alert('Giải đã quay đủ số người!');
                    throw new Error(`Giải đã quay đủ số người!`);
                }

                return data.data.total;
            } catch (error) {
                console.error('Có lỗi xảy ra khi gọi API checkTotalWinner:', error);
                throw error; // Ném lỗi để dừng quá trình quay
            }
        }

        async function startRandom() {
            let [total, numberOfSpins] = calcNumberOfSpins(type.value);
            // call api checkTotalWinner
            total = await checkTotalWinner(total, type.value);

            // set lại numberOfSpins
            if (total < numberOfSpins) {
                numberOfSpins = total;
            }

            if (isRunning) return;
            if (players.length < numberOfSpins) {
                alert(`Không đủ người chơi để quay ${numberOfSpins} lần!`);
                return;
            }

            isRunning = true;
            let stt = 1;
            winnersList.classList.remove('hidden');
            resultBox.classList.remove('hidden');
            boxShowResult.innerHTML = "";
            // const fade = document.getElementById('fade');
            // fade.classList.add('bg-gray-900/90');
            // fade.classList.remove('hidden');

            // Hàm quay thưởng với Promise
            const performRandom = async () => {
                return new Promise((resolve, reject) => {
                    let randomInterval;
                    let currentIndex = -1;

                    // Hiệu ứng nháy ngẫu nhiên
                    randomInterval = setInterval(() => {
                        currentIndex = Math.floor(Math.random() * players.length);
                        const playersRandom = players[currentIndex]
                        result.textContent = playersRandom.name + ' - ' + playersRandom.unit +
                            ' - ' + playersRandom.position;
                    }, 100);

                    // Dừng quay sau 3 giây
                    setTimeout(async () => {
                        clearInterval(randomInterval);
                        const winner = players[currentIndex];
                        const winnerName = winner.name + ' - ' + winner.unit + ' - ' +
                            winner.position;
                        players.splice(currentIndex,
                            1); // Xóa người chiến thắng khỏi danh sách

                        // call api update chiến thắng giải
                        try {
                            await updateWinner(winner, type.value,
                                stt); // Gửi thông tin người chiến thắng
                            resolve(); // Hoàn tất vòng quay
                        } catch (error) {
                            reject(error); // Dừng quá trình quay
                        }

                        setTimeout(() => {
                            resolve(); // Kết thúc mỗi lần quay
                        }, 2000);
                    }, 3000);
                });
            };

            // Quay 3 lần liên tiếp
            try {
                for (let i = 0; i < numberOfSpins; i++) {
                    if (players.length === 0) {
                        alert("Không còn người chơi để quay tiếp!");
                        break;
                    }
                    await performRandom(); // Đợi hoàn tất mỗi vòng quay
                }
            } catch (error) {
                console.error("Quá trình quay dừng do lỗi:", error);
                alert("Đã xảy ra lỗi, dừng quay thưởng!");
            } finally {
                resultBox.classList.add('hidden');
                isRunning = false;
            }

            // Kết thúc
            // result.textContent = "";
            // fade.classList.add('hidden'); // Ẩn fade overlay
            resultBox.classList.add('hidden');
            isRunning = false;
        }

        function showResult(winner, stt) {
            const html = `
                <div class="text-center border border-[#fff] w-[330px] p-[15px] flex-shrink-0 relative">
                    <p>SỐ ${stt++}</p>
                    <p class="text-[26px]">${winner.name}</p>
                    <p>* ${winner.unit} - ${winner.position} *</p>
                    <button
                        class="absolute right-[10px] top-[10px] font-bold text-[red] bg-white w-[25px] h-[25px] leading-[25px] border-none rounded-[5px] remove-result" onclick="removeResult(this, ${winner.id})">X</button>
                </div>`;

            boxShowResult.innerHTML += html;
        }

        async function removeResult(button, winnerId) {
            // Tìm phần tử cha (ở đây là phần tử cha trực tiếp)
            const parent = button.parentElement;

            // Kiểm tra và xóa phần tử cha
            if (parent) {
                total = await removeWinner(winnerId);
                parent.remove();
            }
        }

        async function updateWinner(winner, type, stt) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const response = await fetch('/update-winner', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        winner,
                        type
                    }),
                });

                if (!response.ok) {
                    throw new Error(`Lỗi khi gửi thông tin người chiến thắng: ${response.status}`);
                }

                showResult(winner, stt); // Hiển thị kết quả
            } catch (error) {
                console.error('Có lỗi xảy ra khi gọi API updateWinner:', error);
                throw error; // Ném lỗi để dừng quá trình quay
            }
        }

        async function removeWinner(id) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const response = await fetch('/remove-winner', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        id
                    }),
                });

                if (!response.ok) {
                    throw new Error(`Lỗi khi gửi thông tin removeWinner: ${response.status}`);
                }

            } catch (error) {
                console.error('Có lỗi xảy ra khi gọi API removeWinner:', error);
                throw error; // Ném lỗi để dừng quá trình quay
            }
        }

        function clearResult() {
            winnersList.classList.add('hidden');
            boxShowResult.innerHTML = "";
            resultBox.classList.add('hidden');
        }
    </script>
</body>

</html>
