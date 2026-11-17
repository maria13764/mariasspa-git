<?php

it('redirects guests to sign in from the home page', function () {
    $this->get('/')
        ->assertRedirect(route('login'));
});
