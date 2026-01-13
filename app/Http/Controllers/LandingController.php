<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $orders = collect();
        $search = $request->input('q');

        if ($search) {
            $orders = Orden::query()
                ->where('numero_orden', $search)
                ->orWhere('cedula', $search)
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('welcome', compact('orders', 'search'));
    }
}
