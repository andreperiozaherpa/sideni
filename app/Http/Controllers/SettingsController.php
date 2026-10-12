<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\UploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Show the operational parameters form.
     */
    public function edit(): View
    {
        return view('settings.edit', ['settings' => [
            'institution_name' => Setting::get('institution_name', 'Badan Kesatuan Bangsa dan Politik'),
            'institution_address' => Setting::get('institution_address'),
            'institution_phone' => Setting::get('institution_phone'),
            'institution_email' => Setting::get('institution_email'),
            'institution_logo' => Setting::get('institution_logo'),
            'head_name' => Setting::get('head_name'),
            'head_rank' => Setting::get('head_rank'),
            'head_nip' => Setting::get('head_nip'),
        ]]);
    }

    /**
     * Persist the operational parameters.
     */
    public function update(Request $request, UploadService $uploads): RedirectResponse
    {
        $data = $request->validate([
            'institution_name' => ['required', 'string', 'max:255'],
            'institution_address' => ['nullable', 'string', 'max:255'],
            'institution_phone' => ['nullable', 'string', 'max:50'],
            'institution_email' => ['nullable', 'email', 'max:255'],
            'head_name' => ['nullable', 'string', 'max:255'],
            'head_rank' => ['nullable', 'string', 'max:255'],
            'head_nip' => ['nullable', 'string', 'max:50'],
            'institution_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('institution_logo')) {
            $uploads->delete('public', Setting::get('institution_logo'));
            $data['institution_logo'] = $uploads->store($request->file('institution_logo'), 'settings', 'public')['path'];
        } else {
            unset($data['institution_logo']);
        }

        Setting::putMany($data);

        return redirect()->route('settings.edit')->with('status', 'Pengaturan berhasil disimpan.');
    }
}
