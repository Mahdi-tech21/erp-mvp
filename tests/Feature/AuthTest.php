<?php

use App\Models\User;

it('redirects a guest to the login page', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
});

it('shows the login form', function () {
    $this->get('/login')->assertOk()->assertSee('Sign in');
});

it('logs a user in with the right credentials', function () {
    $user = User::factory()->create(['password' => 'correct-horse']);

    $this->post('/login', ['email' => $user->email, 'password' => 'correct-horse'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects a wrong password', function () {
    $user = User::factory()->create(['password' => 'correct-horse']);

    $this->from('/login')
        ->post('/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('throttles repeated failures', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $ignored) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
    }

    $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertSessionHasErrors('email');

    expect(session('errors')->getBag('default')->first('email'))
        ->toContain('Too many login attempts');
});

it('logs a user out', function () {
    $this->actingAs(User::factory()->create())
        ->post('/logout')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('sends an authenticated user away from the login page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/login')
        ->assertRedirect(route('dashboard'));
});
