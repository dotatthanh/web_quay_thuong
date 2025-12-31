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
            background: url('./background.png') no-repeat center center fixed;
            background-size: 100% 100%;
            height: 100vh;
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
    <form action="" class="text-center mt-[500px] text-[22px]">
        <select name="type" id="type" onchange="changeType()"
            class="border border-black p-2 w-[310px] text-center h-[48px] bg-white appearance-none bg-none">
            <option value="GIẢI KHUYẾN KHÍCH">GIẢI KHUYẾN KHÍCH</option>
            <option value="GIẢI BA">GIẢI BA</option>
            <option value="GIẢI NHÌ">GIẢI NHÌ</option>
            <option value="GIẢI NHẤT">GIẢI NHẤT</option>
            <option value="GIẢI ĐẶC BIỆT">GIẢI ĐẶC BIỆT</option>
        </select>
        <button type="button" class="border border-black bg-[#ff2d20] text-white h-[48px] px-4 ml-[15px]" onclick="start()"
            id="spin-btn">QUAY THƯỞNG</button>

        <p class="text-center text-[28px] font-bold text-[rgb(252,238,33)] mt-[15px]" style="font-family: none;"
            id="show-award">50 GIẢI KHUYẾN KHÍCH</p>
    </form>

    <div class="px-[20px] hidden transition-all duration-500 text-[#00008b]" id="winners-list">
        <p class="text-center text-[48px] font-medium text-[rgb(252,238,33)]">DANH SÁCH NGƯỜI MAY MẮN TRÚNG GIẢI</p>

        <div class="flex flex-wrap gap-[15px] justify-center mt-[30px]" id="box-show-result"></div>
    </div>

    <div class="absolute w-full flex justify-center top-[620px] text-[#00008b] hidden" id="resultBox">
        <div class="text-center border border-[#00008b] p-[15px] w-[650px] bg-white">
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
        const spinBtn = document.getElementById('spin-btn');

        function calcNumberOfSpins(type) {
            let total = 0;
            let numberOfSpins = 0;

            switch (type) {
                case 'GIẢI KHUYẾN KHÍCH':
                    total = 50;
                    numberOfSpins = 10;
                    break;
                case 'GIẢI BA':
                    total = 10;
                    numberOfSpins = 10;
                    break;
                case 'GIẢI NHÌ':
                    total = 5;
                    numberOfSpins = 5;
                    break;
                case 'GIẢI NHẤT':
                    total = 2;
                    numberOfSpins = 2;
                    break;
                case 'GIẢI ĐẶC BIỆT':
                    total = 1;
                    numberOfSpins = 1;
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
                        total,
                        type
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

        async function start() {
            let [total, numberOfSpins] = calcNumberOfSpins(type.value);
            // call api checkTotalWinner
            total = await checkTotalWinner(total, type.value);

            // set lại numberOfSpins
            if (total < numberOfSpins) {
                numberOfSpins = total;
            }

            let resultFilterPlayers = players
            if (isRunning) return;
            if (resultFilterPlayers.length < numberOfSpins) {
                alert(`Không đủ người chơi để quay ${numberOfSpins} lần!`);
                return;
            }

            isRunning = true;
            let stt = 1;
            winnersList.classList.remove('hidden');
            resultBox.classList.remove('hidden');
            boxShowResult.innerHTML = "";
            winnersList.classList.toggle('mt-[100px]');
            spinBtn.disabled = true;

            // Hàm quay thưởng với Promise
            const performRandom = async () => {
                resultFilterPlayers = players
                return new Promise((resolve, reject) => {
                    let randomInterval;
                    let currentIndex = -1;


                    // Hiệu ứng nháy ngẫu nhiên
                    randomInterval = setInterval(() => {
                        currentIndex = Math.floor(Math.random() * resultFilterPlayers.length);
                        const playersRandom = resultFilterPlayers[currentIndex]
                        result.textContent = playersRandom.name + ' - ' + playersRandom.unit;
                    }, 100);

                    let timeout = 3000;
                    if (type.value == "GIẢI ĐẶC BIỆT") {
                        timeout = 10000;
                    }
                    // Dừng quay sau 3 giây
                    setTimeout(async () => {
                        clearInterval(randomInterval);

                        const winner = resultFilterPlayers[currentIndex];
                        const winnerName = winner.name + ' - ' + winner.unit;
                        resultFilterPlayers.splice(currentIndex,
                            1); // Xóa người chiến thắng khỏi danh sách
                        players = players.filter(player => player.id !== winner
                            .id); // Xóa người chiến thắng khỏi danh sách

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
                    }, timeout);
                });
            };

            // Quay 3 lần liên tiếp
            try {
                for (let i = 0; i < numberOfSpins; i++) {
                    if (resultFilterPlayers.length === 0) {
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
            resultBox.classList.add('hidden');
            winnersList.classList.toggle('mt-[100px]');
            isRunning = false;
            spinBtn.disabled = false;
        }

        function showResult(winner, stt) {
            const html = `
            <div class="text-center border border-[#00008b] w-[345px] px-[10px] py-[15px] flex-shrink-0 relative rounded-[8px] group bg-white">
                <p class="text-[26px]">${winner.name}</p>
                <p>* ${winner.unit} *</p>
                <button
                    class="absolute hidden group-hover:block right-[10px] top-[10px] font-bold text-[red] bg-white w-[25px] h-[25px] leading-[25px] border-none rounded-[5px] remove-result"
                    onclick="removeResult(this, ${winner.id}, '${winner.position}')">X</button>
            </div>`;

            boxShowResult.innerHTML += html;
        }

        async function removeResult(button, winnerId, winnerPosition) {
            // Tìm phần tử cha (ở đây là phần tử cha trực tiếp)
            const parent = button.parentElement;

            // Kiểm tra và xóa phần tử cha
            if (parent) {
                total = await removeWinner(winnerId, winnerPosition);
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

        async function removeWinner(id, position) {
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

        async function changeType() {
            clearResult();

            const showAward = document.getElementById('show-award');
            let award = '';
            switch (type.value) {
                case 'GIẢI KHUYẾN KHÍCH':
                    award = '50 GIẢI KHUYẾN KHÍCH';
                    break;
                case 'GIẢI BA':
                    award = '10 GIẢI BA';
                    break;
                case 'GIẢI NHÌ':
                    award = '05 GIẢI NHÌ';
                    break;
                case 'GIẢI NHẤT':
                    award = '02 GIẢI NHẤT';
                    break;
                case 'GIẢI ĐẶC BIỆT':
                    award = '01 GIẢI ĐẶC BIỆT';
                    break;
            }
            showAward.innerHTML = award;

        }

        document.addEventListener('keydown', function(event) {
            if (event.code === 'Space') { // Kiểm tra nếu phím Space được nhấn
                start(); // Gọi hàm start khi nhấn Space
            }
        });
    </script>
</body>

</html>
