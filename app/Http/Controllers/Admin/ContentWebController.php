<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Firestore\ContentPublishService;
use Illuminate\Http\Request;

class ContentWebController extends Controller
{
    public function __construct(private readonly ContentPublishService $service) {}

    public function index()
    {
        return view('admin.content.index', [
            'meta' => $this->service->getCurrentVersionMeta(),
        ]);
    }

    public function publish(Request $request)
    {
        try {
            $result = $this->service->publish('Published from Admin Panel', 'admin');

            return redirect()->route('admin.content.index')
                             ->with('success', 'Version '.($result['version'] ?? '?').' published successfully!');
        } catch (\Throwable $e) {
            return redirect()->route('admin.content.index')
                             ->with('error', 'Publish failed: '.$e->getMessage());
        }
    }
}
