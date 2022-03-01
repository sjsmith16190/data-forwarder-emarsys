<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function respond($content, $statusCode = Response::HTTP_OK, $headers = [])
    {
        $response = new Response();
        $response->setStatusCode($statusCode);
        $response->setContent(json_encode($content));
        $response->header('Content-Type', 'application/json');

        foreach ($headers as $name => $value) {
            $response->header($name, $value);
        }

        return $response;
    }
}
