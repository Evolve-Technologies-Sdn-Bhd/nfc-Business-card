<?php

test('the api root redirects (no welcome page is served)', function () {
    $this->get('/')->assertStatus(302);
});
