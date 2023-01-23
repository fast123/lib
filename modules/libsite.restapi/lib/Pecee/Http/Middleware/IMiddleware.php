<?php

namespace LibSite\RestApi\Pecee\Http\Middleware;

use LibSite\RestApi\Pecee\Http\Request;

interface IMiddleware
{
    /**
     * @param Request $request
     */
    public function handle(Request $request): void;

}