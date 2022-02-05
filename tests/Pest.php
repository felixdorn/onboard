<?php

use Illuminate\Http\Request;

function createRequest(string $method, string $uri): Request
{
    $base = \Symfony\Component\HttpFoundation\Request::create(
        $uri,
        $method
    );

    return Request::createFromBase($base);
}
