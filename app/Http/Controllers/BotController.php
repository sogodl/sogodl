<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BotController extends Controller
{
    private $bot;

    public function __construct()
    {
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient('6XWM1H47Fvjvw9nnwqGuLFXnIox2Ki5Oa7ZKPgagY/3Ue624DNx36ucyNXPUaQjbwoxZl5ut1+FHS5msnqqd4gEq/iwNZu5p0JTvwDGq0M97/YAcn495WzQCs8y5hYnVToMdVaPkouoCpNXRIxLZbgdB04t89/1O/w1cDnyilFU=');
        $this->bot  = new \LINE\LINEBot($httpClient, ['channelSecret' => '1b938ca560bc6dd17524c8b9ac4b63dc']);
    }

    public function index()
    {
        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('hello');
        $response           = $this->bot->replyMessage('<reply token>', $textMessageBuilder);
        if ($response->isSucceeded()) {
            echo 'Succeeded!';
            return;
        }

// Failed
        echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
    }
}
