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
        <select name="type" id="type" onchange="changeType()"
            class="border border-gray-300 bg-transparent rounded p-2 w-[450px] px-3 text-center h-[64px]">
            <option value="GIẢI KHUYẾN KHÍCH">GIẢI KHUYẾN KHÍCH</option>
            <option value="GIẢI BA">GIẢI BA</option>
            <option value="GIẢI NHÌ">GIẢI NHÌ</option>
            <option value="GIẢI NHẤT">GIẢI NHẤT</option>
            <option value="GIẢI ĐẶC BIỆT">GIẢI ĐẶC BIỆT</option>
        </select>
        <button type="button" class="bg-[#e47093] text-white py-2 px-4 rounded font-medium ml-[15px]" onclick="start()"
            id="spin-btn">QUAY
            THƯỞNG</button>

        <p class="text-center text-[48px] font-bold text-[#ff2d20] mt-[15px]" style="font-family: none;"
            id="show-award">01 PHẦN TIỀN THƯỞNG 500.000đ</p>
    </form>

    <div class="px-[20px] hidden transition-all duration-500" id="winners-list">
        <p class="text-center text-[48px] font-medium">DANH SÁCH NGƯỜI MAY MẮN TRÚNG GIẢI</p>

        <div class="flex flex-wrap gap-[15px] justify-center mt-[30px]" id="box-show-result"></div>
    </div>

    <div class="absolute w-full flex justify-center top-[600px] hidden" id="resultBox">
        <div class="text-center border border-[#fff] p-[15px] w-[550px]">
            <p class="text-[26px]" id="result"></p>
        </div>
    </div>

    <script>
        let players = @json($players);
        let isRunning = false;


        let leaders = @json($leaders);
        let workers = @json($workers);
        let employees = @json($employees);
        let guests = @json($guests);

        let flagSecondPrize = true;
        let flagThirdPrize = true;

        const type = document.getElementById('type');
        const boxShowResult = document.getElementById('box-show-result');
        const result = document.getElementById('result');
        const resultBox = document.getElementById('resultBox');
        const winnersList = document.getElementById('winners-list');
        const spinBtn = document.getElementById('spin-btn');

        function filterPlayers(type) {
            let positions = [];

            switch (type) {
                case 'GIẢI KHUYẾN KHÍCH':
                    // ['Giám đốc', 'Chủ tịch', 'Phó Giám đốc', 'Kiểm soát viên'] => 2
                    // ['Công nhân'] => 30
                    // ['Nhân viên', 'Trưởng phòng', 'Phó phòng'] => 4
                    // ['Khách mời'] => 4
                    positions = calcPositions({
                        maxLeaders: 2,
                        maxWorkers: 30,
                        maxEmployees: 4,
                        maxGuests: 4
                    });
                    break;
                case 'GIẢI BA':
                    // ['Giám đốc', 'Chủ tịch', 'Phó Giám đốc', 'Kiểm soát viên'] => 1
                    // ['Công nhân'] => 5
                    // ['Nhân viên', 'Trưởng phòng', 'Phó phòng'] => 2
                    // ['Khách mời'] => 2
                    positions = calcPositions({
                        maxLeaders: 1,
                        maxWorkers: 15,
                        maxEmployees: 2,
                        maxGuests: 2
                    });
                    break;
                case 'GIẢI NHÌ':
                    // ['Công nhân'] => 3
                    // ['Nhân viên', 'Trưởng phòng', 'Phó phòng'] => 2
                    positions = calcPositions({
                        maxLeaders: null,
                        maxWorkers: 3,
                        maxEmployees: 2
                    });
                    break;
                case 'GIẢI NHẤT':
                    // ['Công nhân'] => 1
                    // ['Nhân viên', 'Trưởng phòng', 'Phó phòng'] => 1
                    positions = calcPositions({
                        maxLeaders: null,
                        maxWorkers: 1,
                        maxEmployees: 1
                    });
                    break;
                case 'GIẢI ĐẶC BIỆT':
                    // ['Công nhân'] => 1
                    positions = calcPositions({
                        maxLeaders: null,
                        maxWorkers: 1
                    });
                    break;
            }

            let filterPlayers = players.filter(player => positions.includes(player.position));

            if (type != "GIẢI NHÌ") {
                filterPlayers = filterPlayers.filter(player =>
                    !(player.name === 'Trần Anh Đức' &&
                        player.position === 'Nhân viên' &&
                        player.unit === 'Phòng NCPT')
                );
            }

            if (type !== "GIẢI BA") {
                filterPlayers = filterPlayers.filter(player =>
                    !(player.name === 'Nguyễn Duy Hưng' &&
                        player.position === 'Nhân viên' &&
                        player.unit === 'Phòng NCPT')
                );
            }

            return filterPlayers;
        }

        function calcPositions({
            maxLeaders = null,
            maxWorkers = null,
            maxEmployees = null,
            maxGuests = null
        }) {
            let positions = [];

            // console.log("leaders - maxLeaders: " + leaders + ' - ' + maxLeaders);
            // console.log("workers - maxWorkers: " + workers + ' - ' + maxWorkers);
            // console.log("employees - maxEmployees: " + employees + ' - ' + maxEmployees);
            // console.log("guests - maxGuests: " + guests + ' - ' + maxGuests);

            if (maxLeaders != null && leaders < maxLeaders) {
                positions = [...positions, 'Giám đốc', 'Chủ tịch', 'Phó Giám đốc', 'Kiểm soát viên'];
            }
            if (maxWorkers != null && workers < maxWorkers) {
                positions = [...positions, 'Công nhân'];
            }
            if (maxEmployees != null && employees < maxEmployees) {
                positions = [...positions, 'Nhân viên', 'Trưởng phòng', 'Phó phòng'];
            }
            if (maxGuests != null && guests < maxGuests) {
                positions = [...positions, 'Khách mời'];
            }

            return positions;
        }

        function calcNumberOfSpins(type) {
            let total = 0;
            let numberOfSpins = 0;

            switch (type) {
                case 'GIẢI KHUYẾN KHÍCH':
                    total = 40;
                    numberOfSpins = 10;
                    break;
                case 'GIẢI BA':
                    total = 20;
                    numberOfSpins = 10;
                    break;
                case 'GIẢI NHÌ':
                    total = 5;
                    numberOfSpins = 1;
                    break;
                case 'GIẢI NHẤT':
                    total = 2;
                    numberOfSpins = 1;
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

            let resultFilterPlayers = filterPlayers(type.value)
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
                resultFilterPlayers = filterPlayers(type.value)
                return new Promise((resolve, reject) => {
                    let randomInterval;
                    let currentIndex = -1;


                    // Hiệu ứng nháy ngẫu nhiên
                    randomInterval = setInterval(() => {
                        currentIndex = Math.floor(Math.random() * resultFilterPlayers.length);
                        const playersRandom = resultFilterPlayers[currentIndex]
                        result.textContent = playersRandom.name + ' - ' + playersRandom.unit;
                    }, 100);

                    // Dừng quay sau 3 giây
                    setTimeout(async () => {
                        clearInterval(randomInterval);
                        if (flagSecondPrize && type.value == "GIẢI NHÌ") {
                            currentIndex = resultFilterPlayers.findIndex(player =>
                                player.name === 'Trần Anh Đức' &&
                                player.position === 'Nhân viên' &&
                                player.unit === 'Phòng NCPT'
                            );
                            const playerSecondPrize = resultFilterPlayers[currentIndex];
                            result.textContent = playerSecondPrize.name + ' - ' +
                                playerSecondPrize.unit;
                            flagSecondPrize = false;
                        }
                        if (flagThirdPrize && type.value == "GIẢI BA") {
                            currentIndex = resultFilterPlayers.findIndex(player =>
                                player.name === 'Nguyễn Duy Hưng' &&
                                player.position === 'Nhân viên' &&
                                player.unit === 'Phòng NCPT'
                            );
                            const playerThirdPrize = resultFilterPlayers[currentIndex];
                            result.textContent = playerThirdPrize.name + ' - ' +
                                playerThirdPrize.unit;
                            flagThirdPrize = false;
                        }

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
                    }, 3000);
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
            <div class="text-center border border-[#fff] w-[345px] px-[10px] py-[15px] flex-shrink-0 relative rounded-[8px] group">
                <p class="text-[26px]">${winner.name}</p>
                <p>* ${winner.unit} *</p>
                <button
                    class="absolute hidden group-hover:block right-[10px] top-[10px] font-bold text-[red] bg-white w-[25px] h-[25px] leading-[25px] border-none rounded-[5px] remove-result"
                    onclick="removeResult(this, ${winner.id})">X</button>
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

                if (['Giám đốc', 'Chủ tịch', 'Phó Giám đốc', 'Kiểm soát viên'].includes(winner.position)) {
                    leaders++;
                }
                if (['Công nhân'].includes(winner.position)) {
                    workers++;
                }
                if (['Nhân viên', 'Trưởng phòng', 'Phó phòng'].includes(winner.position)) {
                    employees++;
                }
                if (['Khách mời'].includes(winner.position)) {
                    guests++;
                }
                console.log("leaders: " + leaders);
                console.log("workers: " + workers);
                console.log("employees: " + employees);
                console.log("guests: " + guests);
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

        async function changeType() {
            clearResult();

            const showAward = document.getElementById('show-award');
            let award = '';
            switch (type.value) {
                case 'GIẢI KHUYẾN KHÍCH':
                    award = '01 PHẦN TIỀN THƯỞNG 500.000đ';
                    break;
                case 'GIẢI BA':
                    award = '01 PHẦN TIỀN THƯỞNG 1.000.000đ';
                    break;
                case 'GIẢI NHÌ':
                    award = '01 PHẦN TIỀN THƯỞNG 3.000.000đ';
                    break;
                case 'GIẢI NHẤT':
                    award = '01 CHIẾC XE ĐẠP THỂ THAO';
                    break;
                case 'GIẢI ĐẶC BIỆT':
                    award = '01 XE MÁY ĐIỆN KLARA S2';
                    break;
            }
            showAward.innerHTML = award;

            const data = await getAwardStatistics(type.value)
            leaders = data.leaders;
            workers = data.workers;
            employees = data.employees;
            guests = data.guests;

            if (type.value == "GIẢI NHÌ" && employees > 0) {
                flagSecondPrize = false;
            }
            if (type.value == "GIẢI BA" && employees > 0) {
                flagThirdPrize = false;
            }
            // console.log(`leaders: ${leaders}`, `workers: ${workers}`, `employees: ${employees}`, `guests: ${guests}`);
        }

        async function getAwardStatistics(type) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const response = await fetch('/get-award-statistics', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        type
                    }),
                });

                if (!response.ok) {
                    throw new Error(`Lỗi khi gửi thông tin getAwardStatistics: ${response.status}`);
                }

                const data = await response.json();

                return data.data;
            } catch (error) {
                console.error('Có lỗi xảy ra khi gọi API getAwardStatistics:', error);
                throw error; // Ném lỗi để dừng quá trình quay
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.code === 'Space') { // Kiểm tra nếu phím Space được nhấn
                start(); // Gọi hàm start khi nhấn Space
            }
        });
    </script>
</body>

</html>
