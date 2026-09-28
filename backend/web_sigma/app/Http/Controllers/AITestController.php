<?php

namespace App\Http\Controllers;


use App\Services\AI\AIService;


class AITestController extends Controller
{


    public function test(AIService $ai)
    {

        $result = $ai->predictRisk([

            "T2M" => 35,

            "RH2M" => 40,

            "PRECTOTCORR" => 1,

            "WS10M" => 5,

            "ALLSKY_SFC_SW_DWN" => 25,

            "brightness" => 320,

            "confidence" => 90,

            "frp" => 50,

            "latitude" => -6.2,

            "longitude" => 107.6

        ]);


        return response()->json($result);

    }

}
