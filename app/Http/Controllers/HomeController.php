<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Service;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'services' => Service::active()->orderBy('name')->get(),
            'doctors' => Doctor::active()->orderBy('name')->get(),
        ]);
    }
}
