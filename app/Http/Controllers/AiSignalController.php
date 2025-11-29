<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Signal; // pakai model Signal

class AiSignalController extends Controller
{
    public function dashboard()
    {
        // ambil 5 signal terbaru atau yang punya ai_probability tinggi
        $signals = Signal::orderBy('ai_probability', 'desc')->take(5)->get();

        // kirim ke view dashboard
        return view('livewire.dashboard', compact('signals'));
    }
}
