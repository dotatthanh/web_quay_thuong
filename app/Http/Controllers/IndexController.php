<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    public function index()
    {
        $players = Player::whereNull('won')->get();
        $data = [
            'players' => $players,
            'leaders' => Player::whereIn('position', ['Giám đốc', 'Chủ tịch', 'Phó Giám đốc', 'Kiểm soát viên'])->where('won', 'GIẢI KHUYẾN KHÍCH')->count(),
            'workers' => Player::whereIn('position', ['Công nhân'])->where('won', 'GIẢI KHUYẾN KHÍCH')->count(),
            'employees' => Player::whereIn('position', ['Nhân viên', 'Trưởng phòng', 'Phó phòng'])->where('won', 'GIẢI KHUYẾN KHÍCH')->count(),
            'guests' => Player::whereIn('position', ['Khách mời'])->where('won', 'GIẢI KHUYẾN KHÍCH')->count(),
        ];

        return view('home', $data);
    }

    public function updateWinner(Request $request)
    {
        try {
            DB::beginTransaction();

            $params = $request->all();
            $player = Player::find($params['winner']['id']);
            $player->update(['won' => $params['type']]);
            DB::commit();

            return $this->responseSuccess(Response::HTTP_OK, null);
        } catch (Exception $e) {
            DB::rollback();

            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }

    public function checkTotalWinner(Request $request)
    {
        try {
            $total = Player::where('won', $request->type)->count();
            if ($total >= $request->total) {
                return $this->responseSuccess(Response::HTTP_OK, ['result' => false]);
            }

            return $this->responseSuccess(Response::HTTP_OK, ['result' => true, 'total' => $request->total - $total]);
        } catch (Exception $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }

    public function removeWinner(Request $request)
    {
        try {
            DB::beginTransaction();

            $player = Player::find($request->id);
            $player->update(['won' => null]);
            DB::commit();

            return $this->responseSuccess(Response::HTTP_OK, null);
        } catch (Exception $e) {
            DB::rollback();

            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }

    public function getAwardStatistics(Request $request)
    {
        try {
            $data = [
                'leaders' => Player::whereIn('position', ['Giám đốc', 'Chủ tịch', 'Phó Giám đốc', 'Kiểm soát viên'])->where('won', $request->type)->count(),
                'workers' => Player::whereIn('position', ['Công nhân'])->where('won', $request->type)->count(),
                'employees' => Player::whereIn('position', ['Nhân viên', 'Trưởng phòng', 'Phó phòng'])->where('won', $request->type)->count(),
                'guests' => Player::whereIn('position', ['Khách mời'])->where('won', $request->type)->count(),
            ];

            return $this->responseSuccess(Response::HTTP_OK, $data);
        } catch (Exception $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }
}
