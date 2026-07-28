<?php

namespace App\Http\Controllers;

use App\DoctorProfile;
use App\Helpers\ActivityLogger;
use App\User;
use Illuminate\Http\Request;

class DoctorProfileController extends Controller
{
    public function edit(Request $request, User $doctor)
    {
        $this->authorizeDoctor($request, $doctor);
        $profile = $doctor->doctorProfile()->firstOrCreate([], [
            'availability' => 'available',
            'prc_number' => $doctor->license_number,
            'signature_status' => 'not_uploaded',
        ]);
        return view('doctors.profile', compact('doctor', 'profile'));
    }

    public function update(Request $request, User $doctor)
    {
        $this->authorizeDoctor($request, $doctor);
        $data = $request->validate([
            'specialty' => 'nullable|string|max:255',
            'professional_title' => 'nullable|string|max:255',
            'clinic_designation' => 'nullable|string|max:255',
            'prc_number' => 'required|string|max:100',
            'ptr_number' => 'nullable|string|max:100',
            's2_number' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:100',
            'clinic_address' => 'nullable|string|max:1000',
            'prescription_footer' => 'nullable|string|max:1000',
        ]);
        $doctor->doctorProfile()->updateOrCreate([], $data);
        ActivityLogger::log('updated doctor prescription profile');
        return redirect()->back()->with('success', 'Doctor prescription profile updated.');
    }

    public function availability(Request $request, User $doctor)
    {
        $this->authorizeDoctor($request, $doctor);
        $data = $request->validate([
            'availability' => 'required|in:available,busy,temporarily_unavailable,off_duty',
        ]);
        $doctor->doctorProfile()->updateOrCreate([], $data + ['signature_status' => 'not_uploaded']);
        ActivityLogger::log('changed doctor availability', $data['availability']);
        return redirect()->back()->with('success', 'Availability updated.');
    }

    private function authorizeDoctor(Request $request, User $doctor)
    {
        abort_unless(optional($doctor->role)->name === 'Doctor', 404);
        $role = optional($request->user()->role)->name;
        abort_unless($role === 'Administrator' || ($role === 'Doctor' && $request->user()->is($doctor)), 403);
    }
}
