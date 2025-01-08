<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Exception;

class IndexController extends Controller
{
    public function index()
    {
        $players = Player::whereNull('won')->get();
        $data = [
            'players' => $players,
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
}
