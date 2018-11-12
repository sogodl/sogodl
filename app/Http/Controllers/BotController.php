<?php

namespace App\Http\Controllers;

use App\Services\ReplyMsgService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;

class BotController extends Controller
{
    private $bot;

    public function __construct()
    {
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(env('LINE_BOT_CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new \LINE\LINEBot($httpClient, ['channelSecret' => env('LINE_BOT_CHANNEL_SECRET')]);
    }

    /**
     * @param Request $request
     * @param ReplyMsgService $replyMsgService
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index(Request $request, ReplyMsgService $replyMsgService)
    {
        $signature = $request->header(HTTPHeader::LINE_SIGNATURE);

        //get the db content
        $url = 'https://spreadsheets.google.com/feeds/list/' . env('GOOGLE_SHEET_ID') . '/1/public/values?alt=json';

        $sheets      = json_decode(file_get_contents($url), true);
        $datas_array = [];
        foreach ($sheets['feed']['entry'] as $db_entry) {
            $type           = $db_entry['gsx$type']['$t'] ?? "";
            $photo_url      = $db_entry['gsx$photourl']['$t'] ?? "";
            $title          = $db_entry['gsx$title']['$t'] ?? "";
            $url            = $db_entry['gsx$url']['$t'] ?? "";
            $photo_url1     = $db_entry['gsx$photourl1']['$t'] ?? "";
            $title1         = $db_entry['gsx$title1']['$t'] ?? "";
            $url1           = $db_entry['gsx$url1']['$t'] ?? "";
            $button_name    = $db_entry['gsx$buttonname']['$t'] ?? "";
            $button_url     = $db_entry['gsx$buttonurl']['$t'] ?? "";
            $action_message = $db_entry['gsx$actionmessage']['$t'] ?? "";
            $keyword        = $db_entry['gsx$keyword']['$t'] ?? "";
            array_push($datas_array, compact('type', 'photo_url', 'title', 'url', 'keyword', 'photo_url1', 'title1', 'url1', 'button_name', 'button_url', 'action_message'));
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

            Storage::prepend('messages/bot_' . Carbon::now()->toDateString() . '.csv', '"' . Carbon::now() . '","' . $event->getUserId() . '","' . $event->getText() . '"');

            $sourceText = $event->getText();

            foreach ($datas_array as $data) {
                foreach (explode(',', $data['keyword']) as $keyword) {
                    Log::info(var_dump(explode(',', $data['keyword'])));
                    if (mb_strpos($sourceText, $keyword) !== false) {
                        switch ($data['type']) {
                            case 'text':
                                $replyMsg = $replyMsgService->TextMessage($data['title']);
                                break;
                            case 'template':
                                $replyMsg = $replyMsgService->TemplateMessage($data['title'], $data['url'], $data['photo_url']);
                                break 3;
                            case 'template-2button':
                                $replyMsg = $replyMsgService->Template2ButtonMessage($data['title'], $data['url'], $data['photo_url'], $data['button_name'], $data['button_url']);
                                break 3;
                            case 'template-2carousel':
                                $replyMsg = $replyMsgService->Template2CarouselMessage($data['title'], $data['url'], $data['photo_url'], $data['title1'], $data['url1'], $data['photo_url1']);
                                break 3;
                            case 'template-message':
                                $replyMsg = $replyMsgService->TemplateActionMessage($data['title'], $data['url'], $data['photo_url'], $data['action_message']);
                                break 3;
                            case 'image':
                                $replyMsg = $replyMsgService->ImageMessage($data['url'], $data['photo_url']);
                                break;
                            case 'video':
                                $replyMsg = $replyMsgService->VideoMessage($data['url'], $data['photo_url']);
                                break;
                        }
                    } else {
                        $replyMsg = $replyMsgService->TextMessage("有什麼我可以幫你的嗎？");
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
