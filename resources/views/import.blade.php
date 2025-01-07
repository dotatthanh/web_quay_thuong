<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Nhập dữ liệu</title>

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="flex items-center justify-center min-h-screen">
        <div class="w-full max-w-md">
            <!-- Alert thông báo -->
            @if(session('alert-success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                {{ session('alert-success') }}
            </div>
            @endif
    
            @if(session('alert-error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                {{ session('alert-error') }}
            </div>
            @endif
    
            <!-- Form nhập liệu -->
            <form action="{{ route('import') }}" method="POST" class="bg-white p-6 rounded shadow" enctype="multipart/form-data">
                @csrf
    
                <input type="file" name="file_import" accept=".xlsx" required class="block w-full mb-4 border border-gray-300 rounded p-2" placeholder="Chọn tệp">
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                    Nhập dữ liệu
                </button>
            </form>
        </div>
    </div>
    
</body>

</html>
