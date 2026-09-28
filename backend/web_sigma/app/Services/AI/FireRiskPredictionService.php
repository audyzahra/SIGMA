<?php

namespace App\Services\AI;


use App\Models\FireRisk;
use App\Models\Region;



class FireRiskPredictionService
{


    protected AIService $ai;



    public function __construct(
        AIService $ai
    )
    {
        $this->ai = $ai;
    }






    public function predict(
        Region $region
    )
    {


        /*
        |--------------------------------------------------------------------------
        | Ambil koordinat wilayah
        |--------------------------------------------------------------------------
        |
        | Saat ini geometry masih null,
        | jadi menggunakan latitude dan longitude
        |
        */


        if (
            !$region->latitude ||
            !$region->longitude
        ) {


            return null;


        }





        /*
        |--------------------------------------------------------------------------
        | Request ke AI Service
        |--------------------------------------------------------------------------
        |
        | Endpoint:
        | /predict-region
        |
        */


        $result = $this->ai->predictRegion(

            $region->latitude,

            $region->longitude

        );







        /*
        |--------------------------------------------------------------------------
        | Konversi Risk
        |--------------------------------------------------------------------------
        */


        $riskLevel = strtoupper(

            $result['risk_level'] ?? 'UNKNOWN'

        );




        $riskScore = match($riskLevel) {


            'LOW' => 30,


            'MEDIUM' => 60,


            'HIGH' => 90,


            'EXTREME' => 100,


            default => 0


        };








        /*
        |--------------------------------------------------------------------------
        | Simpan hasil AI
        |--------------------------------------------------------------------------
        */


        return FireRisk::create([



            'region_id' => $region->id,



            'risk_score' => $riskScore,



            'risk_level' => strtolower(
                $riskLevel
            ),



            'ai_confidence' =>
                $result['confidence'] ?? 0,





            'temperature' =>
                $result['temperature'] ?? 0,



            'humidity' =>
                $result['humidity'] ?? 0,



            'rainfall' =>
                $result['rainfall'] ?? 0,



            'wind_speed' =>
                $result['wind_speed'] ?? 0,




            'calculated_at' =>
                now(),


        ]);



    }



}
