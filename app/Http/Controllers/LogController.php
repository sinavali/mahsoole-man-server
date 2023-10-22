<?php

namespace App\Http\Controllers;

use App\Models\Logs\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogController extends Controller
{

    public static function newLog($model, $by = 'system', $action = 'system_note', $message = '', $error = 0): void
    {
        Log::create(['model' => $model, 'by' => $by, 'action' => $action, 'message' => $message, 'error' => $error]);
    }

    public static function getAllLogs(Request $req): JsonResponse
    {
        return response()->json(Log::latest()->paginate($req->per_page ?? 50));
    }

    public static function getLogsByModel(Request $req): JsonResponse
    {
        return response()->json(Log::where('model', $req->model)->latest()->paginate($req->per_page ?? 50));
    }

    public static function getLogsByUUID(Request $req): JsonResponse
    {
        return response()->json(Log::where('by', $req->uuid)->latest()->paginate($req->per_page ?? 50));
    }

    public static function getLogsByAction(Request $req): JsonResponse
    {
        return response()->json(Log::where('action', $req->action)->latest()->paginate($req->per_page ?? 50));
    }

    public static function getErrorLogs(Request $req): JsonResponse
    {
        return response()->json(Log::where('error', 1)->latest()->paginate($req->per_page ?? 50));
    }
}
