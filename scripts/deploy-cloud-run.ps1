<# ============================================================
   Deploy Laravel to Google Cloud Run — Step by step
   Project: my-application-sign-7bed2
   ============================================================ #>

$PROJECT_ID   = "my-application-sign-7bed2"
$REGION       = "asia-southeast1"          # Singapore — gần Việt Nam nhất
$SERVICE_NAME = "sign-language-api"
$IMAGE        = "${REGION}-docker.pkg.dev/${PROJECT_ID}/cloud-run/${SERVICE_NAME}"

Write-Host "=== 1. Set active project ===" -ForegroundColor Cyan
gcloud config set project $PROJECT_ID

Write-Host "=== 2. Enable required APIs ===" -ForegroundColor Cyan
gcloud services enable `
    run.googleapis.com `
    cloudbuild.googleapis.com `
    artifactregistry.googleapis.com `
    secretmanager.googleapis.com

Write-Host "=== 3. Create Artifact Registry repo (if not exists) ===" -ForegroundColor Cyan
gcloud artifacts repositories describe cloud-run --location=$REGION 2>$null
if ($LASTEXITCODE -ne 0) {
    gcloud artifacts repositories create cloud-run `
        --repository-format=docker `
        --location=$REGION `
        --description="Docker images for Cloud Run"
}

Write-Host "=== 4. Configure Docker auth ===" -ForegroundColor Cyan
gcloud auth configure-docker "${REGION}-docker.pkg.dev" --quiet

Write-Host "=== 5. Build & push image ===" -ForegroundColor Cyan
docker build -t "${IMAGE}:latest" .
docker push "${IMAGE}:latest"

Write-Host "=== 6. Create secret for service account key ===" -ForegroundColor Cyan
$SECRET_NAME = "firestore-service-account"
$secretExists = gcloud secrets describe $SECRET_NAME 2>$null
if ($LASTEXITCODE -ne 0) {
    gcloud secrets create $SECRET_NAME --replication-policy="automatic"
    Get-Content "storage/app/keys/firestore-service-account.json" -Raw | `
        gcloud secrets versions add $SECRET_NAME --data-file=-
    Write-Host "Secret created and key uploaded." -ForegroundColor Green
} else {
    Write-Host "Secret already exists, skipping." -ForegroundColor Yellow
}

Write-Host "=== 7. Deploy to Cloud Run ===" -ForegroundColor Cyan
gcloud run deploy $SERVICE_NAME `
    --image "${IMAGE}:latest" `
    --region $REGION `
    --platform managed `
    --allow-unauthenticated `
    --memory 512Mi `
    --cpu 1 `
    --min-instances 0 `
    --max-instances 3 `
    --port 8080 `
    --set-env-vars "APP_NAME=SignLanguageAI" `
    --set-env-vars "APP_ENV=production" `
    --set-env-vars "APP_DEBUG=false" `
    --set-env-vars "APP_KEY=$(php artisan key:generate --show)" `
    --set-env-vars "APP_URL=https://${SERVICE_NAME}-HASH.a.run.app" `
    --set-env-vars "GOOGLE_CLOUD_PROJECT=${PROJECT_ID}" `
    --set-env-vars "GOOGLE_APPLICATION_CREDENTIALS=/var/www/html/storage/app/keys/firestore-service-account.json" `
    --set-env-vars "FIRESTORE_TRANSPORT=rest" `
    --set-env-vars "LOG_CHANNEL=stderr" `
    --set-env-vars "SESSION_DRIVER=cookie" `
    --set-env-vars "CACHE_STORE=file" `
    --set-secrets "GOOGLE_CREDENTIALS_JSON=firestore-service-account:latest"

Write-Host ""
Write-Host "=== DONE ===" -ForegroundColor Green
Write-Host "Service URL:" -ForegroundColor Cyan
gcloud run services describe $SERVICE_NAME --region $REGION --format="value(status.url)"
