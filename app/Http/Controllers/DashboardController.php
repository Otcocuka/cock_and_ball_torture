<?php

namespace App\Http\Controllers;


use App\Models\Timer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index() {

        $timer = Timer::all();
        dd($timer);

        return view('dashboard.index', [
            'user' => auth()->user()
        ]);
    }

    public function statistics() {
        return view('dashboard.statistics', [
            'stats' => auth()->user()
        ]);
    }

    public function settings() {

        
        $timer = Timer::all();
      

        return view('dashboard.settings', [
            'settings' => auth()->user(),
            'timers' => $timer
        ]);
    }
}