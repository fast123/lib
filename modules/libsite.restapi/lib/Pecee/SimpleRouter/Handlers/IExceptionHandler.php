<?php

namespace LibSite\RestApi\Pecee\SimpleRouter\Handlers;

use Exception;
use LibSite\RestApi\Pecee\Http\Request;

interface IExceptionHandler
{
    /**
     * @param Request $request
     * @param Exception $error
     */
    public function handleError(Request $request, Exception $error): void;

}