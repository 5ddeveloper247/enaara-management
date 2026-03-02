<?php

namespace App\Traits;

trait ResponseTrait
{

    private function errorResponse($message)
    {

        $response = ['responseCode' => 0, 'msg' => $message];

        return json_encode($response);

    }

    private function successResponse($message)
    {

        $response = ['responseCode' => 1, 'msg' => $message];

        return json_encode($response);

    }

    private function successHtmlResponse($html)
    {

        $response = ['responseCode' => 1, 'html' => $html];

        return json_encode($response);

    }


}
