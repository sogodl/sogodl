<?php

namespace App\Services;


use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
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
        $replyMsg = new ImageMessageBuilder($url, $photo_url);
        return $replyMsg;
    }

    public function VideoMessage($url, $video_url)
    {
        $replyMsg = new VideoMessageBuilder($url, $video_url);
        return $replyMsg;
    }

    public function Template2ButtonMessage(string $title, string $url, string $photo_url, string $button_name, string $button_url)
    {
        $actions  = array(new UriTemplateActionBuilder('查看詳情', $url),
                          new UriTemplateActionBuilder($button_name, $button_url));
        $button   = new ButtonTemplateBuilder(mb_substr($title, 0, 39), mb_substr($title, 0, 39), $photo_url, $actions);
        $replyMsg = new TemplateMessageBuilder(mb_substr($title, 0, 39), $button);
        return $replyMsg;
    }

    public function Template2CarouselMessage(string $title, string $url, string $photo_url, string $title1, string $url1, string $photo_url1)
    {
        $action1 = array(new UriTemplateActionBuilder('查看詳情', $url));
        $column1 = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder(mb_substr($title, 0, 39), mb_substr($title, 0, 39), $photo_url, $action1);
        $action2 = array(new UriTemplateActionBuilder('查看詳情', $url1));
        $column2 = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder(mb_substr($title1, 0, 39), mb_substr($title1, 0, 39), $photo_url1, $action2);

        $carousel = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder(compact('column1', 'column2'));
        $replyMsg = new TemplateMessageBuilder(mb_substr($title, 0, 39), $carousel);
        return $replyMsg;
    }

    public function TemplateActionMessage($title, $photo_url, $action_message)
    {
        $actions       = array();
        $message_array = explode('|', $action_message);
        foreach ($message_array as $message) {
            $action_array = explode('@', $message);
            array_push($actions, new MessageTemplateActionBuilder($action_array[0], $action_array[1]));
//            $actions = array(new MessageTemplateActionBuilder($action_message, $action_message));
        }
        $button   = new ButtonTemplateBuilder(mb_substr($title, 0, 39), mb_substr($title, 0, 39), $photo_url, $actions);
        $replyMsg = new TemplateMessageBuilder(mb_substr($title, 0, 39), $button);
        return $replyMsg;
    }
}