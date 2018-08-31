<?php
/**
 * Created by PhpStorm.
 * User: hanbz
 * Date: 2018/8/30
 * Time: 下午4:14
 */

namespace App\Services;


use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

class ReplyMsgService
{
    public function __construct()
    {
    }

    public function TextMessage(string $text)
    {
        $replyMsg = new TextMessageBuilder(mb_substr($text, 0, 1999));
        return $replyMsg;
    }

    public function TemplateMessage(string $title, string $url, string $photo_url): TemplateMessageBuilder
    {
        $action   = array(new UriTemplateActionBuilder('查看詳情', $url));
        $button   = new ButtonTemplateBuilder(mb_substr($title, 0, 39), mb_substr($title, 0, 39), $photo_url, $action);
        $replyMsg = new TemplateMessageBuilder(mb_substr($title, 0, 39), $button);
        return $replyMsg;
    }

    public function ImageMessage(string $url, string $photo_url)
    {
        $replyMsg = new ImageMessageBuilder($url,$photo_url);
        return $replyMsg;
    }

    public function VideoMessage($url, $video_url)
    {
        $replyMsg = new VideoMessageBuilder($url,$video_url);
        return $replyMsg;
    }
}