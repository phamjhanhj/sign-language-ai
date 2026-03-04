<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Firestore\ContentPublishService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentSyncController extends Controller
{
    public function __construct(private readonly ContentPublishService $service)
    {
    }

    public function version(): JsonResponse
    {
        return response()->json($this->service->getCurrentVersionMeta());
    }

    /**
     * Flutter gửi version hiện tại, BE so sánh với version mới nhất.
     * Trả về up_to_date = true nếu bằng nhau, false nếu khác nhau.
     */
    public function checkVersion(Request $request): JsonResponse
    {
        $request->validate([
            'version' => 'required|integer|min:0',
        ]);

        $clientVersion = (int) $request->input('version');
        $latestMeta    = $this->service->getCurrentVersionMeta();
        $latestVersion = (int) $latestMeta['version'];

        return response()->json([
            'up_to_date'     => $clientVersion === $latestVersion,
            'latest_version' => $latestVersion,
        ]);
    }

    public function contentBootstrap(): JsonResponse
    {
        $payload = $this->service->getLearningBootstrapPayload();

        if (! $payload) {
            return response()->json(['message' => 'No published content version yet.'], 404);
        }

        return response()->json($payload);
    }

    public function dictionaryBootstrap(): JsonResponse
    {
        $payload = $this->service->getDictionaryBootstrapPayload();

        if (! $payload) {
            return response()->json(['message' => 'No published dictionary version yet.'], 404);
        }

        return response()->json($payload);
    }
}
