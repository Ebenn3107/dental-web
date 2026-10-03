<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Service;
use App\Services\AvailabilityService;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            // Urut id = urutan seeder: Pemeriksaan jadi tile unggulan, bukan urut abjad
            'services' => Service::active()->orderBy('id')->get(),
            'doctors' => Doctor::active()->orderBy('name')->get(),
            'practiceDays' => DoctorSchedule::query()->distinct()->count('day_of_week'),
            'slotInterval' => AvailabilityService::SLOT_INTERVAL_MINUTES,
        ]);
    }
}
