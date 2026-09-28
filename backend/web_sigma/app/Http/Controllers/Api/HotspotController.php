<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Hotspot;



class HotspotController extends Controller
{


    public function index()
    {


        $hotspots = Hotspot::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest('detected_at')
            ->limit(1000)
            ->get();



        return response()->json([


            'success'=>true,


            'total'=>$hotspots->count(),


            'data'=>$hotspots->map(function($hotspot){


                return [


                    'id'=>$hotspot->id,


                    'latitude'=>$hotspot->latitude,


                    'longitude'=>$hotspot->longitude,


                    'satellite'=>$hotspot->satellite_name,


                    'brightness_temperature'=>
                        $hotspot->brightness_temperature,


                    'confidence'=>
                        $hotspot->confidence_level,


                    'frp'=>
                        $hotspot->frp,


                    'detected_at'=>

                        $hotspot->detected_at
                        ?
                        $hotspot->detected_at
                            ->format('Y-m-d H:i:s')
                        :
                        null,


                    'status'=>
                        $hotspot->status,


                ];


            })



        ]);


    }


}
