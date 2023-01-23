<?php

use LibSite\RestApi\Exceptions\NotAuthorizedHttpException;
use LibSite\RestApi\Middlewares\Authenticate;
use LibSite\RestApi\Middlewares\ProccessRawBody;
use LibSite\RestApi\Pecee\{Http\Request};
use LibSite\RestApi\Pecee\SimpleRouter\SimpleRouter as Router;

Router::setDefaultNamespace('\LibSite\RestApi\Controllers');

Router::group([
    'prefix' => 'api/v1',
    'middleware' => [
        ProccessRawBody::class,
    ]
], function () {

    Router::get('/project', 'ProjectController@index');
    Router::group([
        'middleware' => [
            Authenticate::class
        ]
    ], function () {
        // authenticated routes
        Router::post('/project/create', 'ProjectController@create');
        Router::post('/project/update/{id}', 'ProjectController@update')
            ->where(['id' => '[\d]+']);
    });
});

Router::error(function(Request $request, Exception $exception) {
    $response = Router::response();
    switch (get_class($exception)) {
        case NotAuthorizedHttpException::class: {
            $response->httpCode(401);
            break;
        }
        case Exception::class: {
            $response->httpCode(500);
            break;
        }
    }

    return $response->json([
        'status' => 'error',
        'message' => $exception->getMessage()
    ]);

});

