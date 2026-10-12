<?php

test('the application redirects the root path to the dashboard', function () {
    $this->get('/')->assertRedirectToRoute('dashboard');
});
