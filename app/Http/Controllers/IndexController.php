<?php

namespace App\Http\Controllers;

use App\Imports\PlayersImport;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class IndexController extends Controller
{
    public function index()
    {
        return view('home');
    }
}
