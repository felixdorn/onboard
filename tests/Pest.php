<?php

use Illuminate\Http\Request;

function createRequest(string $method, string $uri): Request
{
    $base = \Symfony\Component\HttpFoundation\Request::create(
        'http://localhost/' . ltrim($uri, '/'),
        $method
    );

    return Request::createFromBase($base);
}
