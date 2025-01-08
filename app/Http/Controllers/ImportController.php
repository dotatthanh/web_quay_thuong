<?php

namespace App\Http\Controllers;

use App\Imports\PlayersImport;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index()
    {
        return view('import');
    }

    public function import(Request $request)
    {
        if (! $request->hasFile('file_import')) {
            return redirect()->back()->with('alert-error', 'Vui lòng chọn file để import.');
        }

        $file = $request->file('file_import');

        if (! in_array($file->getClientOriginalExtension(), ['xlsx'])) {
            return redirect()->back()->with('alert-error', 'Định dạng file không hợp lệ! Chỉ hỗ trợ xlsx.');
        }

        try {
            Player::truncate();
            Excel::import(new PlayersImport, $file);

            return redirect()->back()->with('alert-success', 'Import dữ liệu thành công!');
        } catch (\Exception $e) {
            Log::error('Import error: '.$e->getMessage());

            return redirect()->back()->with('alert-error', 'Có lỗi xảy ra! Import dữ liệu thất bại!');
        }
    }
}
