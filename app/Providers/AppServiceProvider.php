<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Google\Cloud\Firestore\FirestoreClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FirestoreClient::class, function () {
            $grpcRoots = config('firestore.grpc_roots');
            if (is_string($grpcRoots) && $grpcRoots !== '') {
                putenv("GRPC_DEFAULT_SSL_ROOTS_FILE_PATH={$grpcRoots}");
                $_ENV['GRPC_DEFAULT_SSL_ROOTS_FILE_PATH'] = $grpcRoots;
                $_SERVER['GRPC_DEFAULT_SSL_ROOTS_FILE_PATH'] = $grpcRoots;
            }

            return new FirestoreClient([
                'projectId' => trim((string) config('firestore.project_id')),
                'keyFilePath' => trim((string) config('firestore.credentials')),
                'transport' => (string) config('firestore.transport', 'rest'),
                'apiEndpoint' => (string) config('firestore.api_endpoint', 'firestore.googleapis.com:443'),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
