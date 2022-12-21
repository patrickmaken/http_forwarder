<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use Throwable;
use GuzzleHttp\Client;

class EndOfCallController extends Controller
{
    public function endofcall(Request $request)
    {
        Log::info('[EndOfCallController::endofcall#request_data]', [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->header(),
            'all_data' => $request->all(),
            '$_REQUEST' => $_REQUEST,
        ]);

        $client = new Client([
            'timeout'  => 20.0,
            'verify' => false,
        ]);

        try {

            $url = env('CREDIX_REDIRECT_URL');
            $response = $client->get($url, ['query' => $request->all()]);
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
