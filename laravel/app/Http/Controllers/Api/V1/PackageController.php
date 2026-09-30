<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Package;

class PackageController extends Controller
{
    public function index()
    {
        return PackageResource::collection(
            Package::query()->where('is_active', true)->orderBy('sort_order')->get()
        );
    }
}
