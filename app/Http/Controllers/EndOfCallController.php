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
        Log::info('[EndOfCallController::endofcall#request_data]', [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->header(),
            'all_data' => $request->all(),
            'request_content' => $request->getContent(),
            '$_REQUEST' => $_REQUEST,
        ]);

        $client = new Client([
            'timeout'  => 20.0,
            'verify' => false,
        ]);

        try {

            $url = env('CREDIX_REDIRECT_URL');
            $response = $client->get($url, [
                'query' => $request->all(),
                'proxy' => ['http'  => 'http://10.252.34.55:3128']
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
