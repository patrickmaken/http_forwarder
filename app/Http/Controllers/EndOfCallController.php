<?php

namespace App\Http\Controllers;

use Log;
use Throwable;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

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
        Log::info('MTN_EOC_INPUT', compact('request_content'));
        $phoneNumber = Str::start(explode(';', trim($request_content))[1], '237');

        $sleepingTime = env('SLEEPING_TIME');
        if($sleepingTime > 0) {
            Log::info("sleep Sleeping for $sleepingTime seconds");
            sleep($sleepingTime);
        }

        $client = new Client([
            'timeout'  => 20.0,
            'verify' => false,
        ]);

        try {

            $url = env('CREDIX_REDIRECT_URL');
            $response = $client->post($url, [
                'proxy' => ['http'  => 'http://10.252.34.55:3128'],
                'json' => [
                    'msisdn' => $phoneNumber
                ],
            ]);
            $response = (string)$response->getBody();
            Log::info('MOMOKASH_GO_RESPONSE', compact('response'));
        } catch (Throwable $th) {
            Log::error('MOMOKASH_GO_ERROR', [
                'th.message' => $th->getMessage(),
                'url' => $url,
            ]);
        }

        return 'OK';
    }
}
