<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MapObject;
use App\Services\MapObjectService;

class MapObjectController extends Controller
{
    private MapObjectService $mapObjectService;
    public function __construct(MapObjectService $mapObjectService)
    {
        $this->mapObjectService = $mapObjectService;
    }

    public function check_url(Request $request)
    {
        return $this->mapObjectService->check_url($request);
    }

    public function get_reviews(Request $request)
    {
        return $this->mapObjectService->get_reviews($request);
    }

}
