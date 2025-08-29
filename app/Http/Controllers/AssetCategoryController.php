<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\JsonResponse;

class AssetCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => AssetCategory::orderBy('nama')->get()]);
    }
}
