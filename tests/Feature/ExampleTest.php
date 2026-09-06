<?php

it('exposes the framework health check', function () {
    $this->get('/up')->assertOk();
});
