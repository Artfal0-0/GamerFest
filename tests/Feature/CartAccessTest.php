<?php

test('guest visitors are prompted to log in before opening their cart', function () {
    $this->get('/carrito')
        ->assertRedirect(route('login'));

    expect(session('url.intended'))->toBe(url('/carrito'));
});
