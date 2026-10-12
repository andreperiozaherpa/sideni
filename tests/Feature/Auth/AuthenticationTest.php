<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);

test('guests are redirected to login when opening the dashboard', function () {
    $this->get(route('dashboard'))->assertRedirectToRoute('login');
});

test('users can log in with their email', function () {
    $user = User::factory()->create(['password' => Hash::make('secret-password')]);

    $this->post(route('login.store'), ['login' => $user->email, 'password' => 'secret-password'])
        ->assertRedirectToRoute('dashboard');

    $this->assertAuthenticatedAs($user);
});

test('users can log in with their username', function () {
    $user = User::factory()->create(['username' => 'petugas', 'password' => Hash::make('secret-password')]);

    $this->post(route('login.store'), ['login' => 'petugas', 'password' => 'secret-password'])
        ->assertRedirectToRoute('dashboard');

    $this->assertAuthenticatedAs($user);
});

test('users cannot log in with an unknown username', function () {
    $this->post(route('login.store'), ['login' => 'tidak-ada', 'password' => 'secret-password'])
        ->assertInvalid(['login']);

    $this->assertGuest();
});
