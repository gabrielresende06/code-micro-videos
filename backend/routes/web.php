<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $client = new \GuzzleHttp\Client();
    $data = [
        'userEmail' => 'solar-planit@baywa-re.de',
        'billOfMaterial' => [
            [
                "materialId"=> "01-000068",
                "amount" => 58
            ]
        ],
        "itemNumbers" => [
            "01-000682"
        ]
    ];
    $result = $client->request('POST', 'https://solar-distribution.baywa-re.pl/solarplanit/requestItemAvailabilityStatus', [
        'headers' => [
            'Authorization' => 'Basic YmF5d2Etb3hpZDpCYXl3YU94aWQ=',
            'Accept' => 'application/json',
            'Content-Type' => 'application/gzip',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Content-Encoding' => 'gzip',
        ],
        'body' => gzencode(json_encode($data))
    ]);

    /**
     * {
    "planningId": "1234567",
    "planningName": "string",
    "timestamp": 1598881963,
    "userEmail": "solar-planit@baywa-re.de",
    "organizationId": "218251",
    "billOfMaterial": [
    {
    "materialId": "01-000068",
    "amount": 58
    }
    ],
    "itemNumbers": [
    "01-000682"
    ]
    }
     */
        echo '<pre>';
    echo ($result->getBody()->getContents());
});
