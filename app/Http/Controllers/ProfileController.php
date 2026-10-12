<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Support\UploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly UploadService $uploads) {}

    /**
     * Show the current user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    /**
     * Update the current user's profile.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $attributes = $request->safe()->only(['name', 'username', 'email']);

        if ($request->filled('password')) {
            $attributes['password'] = $request->string('password')->toString();
        }

        if ($request->hasFile('avatar')) {
            $this->uploads->delete('public', $user->avatar_path);
            $attributes['avatar_path'] = $this->uploads->store($request->file('avatar'), 'avatars', 'public')['path'];
        }

        $user->update($attributes);

        return redirect()->route('profile.edit')->with('status', 'Profil berhasil diperbarui.');
    }
}
