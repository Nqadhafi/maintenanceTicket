<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\JsonResponse;

class VendorController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Vendor::orderBy('nama')->get()]);
    }
}
