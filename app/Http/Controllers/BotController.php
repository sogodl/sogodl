<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LINE\LINEBot\EchoBot\Dependency;
use LINE\LINEBot\EchoBot\Route;
use LINE\LINEBot\EchoBot\Setting;

class BotController extends Controller
{
    public function Index()
    {
        return 'bot start';
//        $setting = Setting::getSetting();
//        $app = new Slim\App($setting);
//        (new Dependency())->register($app);
//        (new Route())->register($app);
//        $app->run();
    }
}
