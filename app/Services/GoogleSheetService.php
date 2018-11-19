<?php

namespace App\Services;


use Google_Client;
use Google_Service_Sheets;

class GoogleSheetService
{
    public function getGoogleSheet()
    {
        $client = new Google_Client();
        $client->setApplicationName("sogod-line-bot-test");
        $client->setDeveloperKey("AIzaSyDxrnkhPzX4_5WrI9cbdtb7RNk4moynjbY");

        $service = new Google_Service_Sheets($client);

// Prints the names and majors of students in a sample spreadsheet:
// https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
        $spreadsheetId = '1bEkmiTJt3Eep4Pgr2DaPftVEyP6NqXDPQ76hBvLqtzE';
        $range         = 'sheet1!A1:K';
        $response      = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values        = $response->getValues();
        return $values;
//        foreach ($values as $value)
//            dump($value);
    }
}