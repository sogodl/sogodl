<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

class BotController extends Controller
{
    private $bot;

    public function __construct()
    {
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(env('LINE_BOT_CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new \LINE\LINEBot($httpClient, ['channelSecret' => env('LINE_BOT_CHANNEL_SECRET')]);
    }

    public function index(Request $request)
    {
        $signature = $request->header(HTTPHeader::LINE_SIGNATURE);

        //get the db content
        $url = 'https://spreadsheets.google.com/feeds/list/' . env('GOOGLE_SHEET_ID') . '/1/public/values?alt=json';

        $sheets      = json_decode(file_get_contents($url), true);
        $datas_array = [];
        foreach ($sheets['feed']['entry'] as $db_entry) {
            $photo_url = $db_entry['gsx$photourl']['$t'] ?? "";
            $title     = $db_entry['gsx$title']['$t'] ?? "";
            $url       = $db_entry['gsx$url']['$t'] ?? "https://www.google.com";
            $keyword   = $db_entry['gsx$keyword']['$t'] ?? "";
            array_push($datas_array, compact('photo_url', 'title', 'url', 'keyword'));
        }

        try {
            $events = $this->bot->parseEventRequest($request->getContent(), $signature);
            Log::info($events);
        } catch (InvalidEventRequestException $e) {
            return response('Invalid signature', 400);
        } catch (InvalidSignatureException $e) {
            return response("Invalid event request", 400);
        }

        foreach ($events as $event) {
            if (!($event instanceof MessageEvent)) {
                Log::info('Non message event has come');
                continue;
            }

            if (!($event instanceof TextMessage)) {
                Log::info('Non tex message has come');
                continue;
            }

            $sourceText = $event->getText();
            $replyMsg   = new TextMessageBuilder("有什麼我可以幫你的嗎？");

            foreach ($datas_array as $data) {
                foreach (explode(',', $data['keyword']) as $keyword) {
                    if (mb_strpos($sourceText, $keyword) !== false) {
                        $action   = array(new UriTemplateActionBuilder('查看詳情', $data['url']));
                        $button   = new ButtonTemplateBuilder($data['title'], $data['title'], $data['photo_url'], $action);
                        $replyMsg = new TemplateMessageBuilder($data['title'], $button);
                        break 2;
                    }
                }
            }

            try {
                $resp = $this->bot->replyMessage($event->getReplyToken(), $replyMsg);
                Log::info($resp->getHTTPStatus() . ': ' . $resp->getRawBody());
            } catch (\ReflectionException $e) {
                Log::info($e);
            }
        }

        return response('OK');
    }
}
