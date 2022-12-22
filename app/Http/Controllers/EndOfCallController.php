<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use Throwable;
use GuzzleHttp\Client;

class EndOfCallController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:200,1')->only([
            'endofcall',
        ]);
    }

    public function endofcall(Request $request)
    {
        $request_content = $request->getContent();
        Log::info('EndOfCallController::endofcall#request_data', compact('request_content'));
        $phoneNumber = '237' . explode(';', trim($request_content))[1];

        $client = new Client([
            'timeout'  => 20.0,
            'verify' => false,
        ]);

        try {

            $url = env('CREDIX_REDIRECT_URL');
            $response = $client->post($url, [
                'proxy' => ['http'  => 'http://10.252.34.55:3128'],
                'json' => [
                    'msisdn' => $phoneNumber,
                    'message' => 'Hello world form Credix!',
                    'callbackUrl' => env('CREDIX_CALLBACK_URL'),
                    'sessionId' => ''.microtime(true)
                ],
            ]);
            $response = (string)$response->getBody();
            Log::info('EndOfCallController::endofcall#credix_response', compact('response'));
        } catch (Throwable $th) {
            Log::error('EndOfCallController::endofcall#catch', [
                'th.message' => $th->getMessage(),
                'url' => $url,
            ]);
        }

        return 'OK';
    }
}
