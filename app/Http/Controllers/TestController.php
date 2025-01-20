<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\JobDefinition;

class TestController extends Controller
{
    public function testProject($id): JsonResponse
    {
        try {
            $project = (new JobDefinition)->getInfosProject($id);
            return response()->json(['success' => true, 'data' => $project]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
