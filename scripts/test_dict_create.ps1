$ErrorActionPreference = "Stop"
$base = "http://127.0.0.1:8000"

Write-Output "=== TEST: Dictionary Topic Create ==="

# Step 1: GET create page + extract CSRF token
Write-Output "[1] GET /admin/dictionary-topics/create ..."
$page = Invoke-WebRequest -Uri "$base/admin/dictionary-topics/create" -UseBasicParsing -SessionVariable sess -TimeoutSec 15
Write-Output "    Status: $($page.StatusCode)"

$page.Content -match 'name="_token"\s+value="([^"]+)"' | Out-Null
$token = $matches[1]
Write-Output "    CSRF Token: $($token.Substring(0,8))..."

# Step 2: POST create form
Write-Output "[2] POST /admin/dictionary-topics ..."
$body = @{
    _token        = $token
    title         = "Dong vat"
    description   = "Tu dien ky hieu cac loai dong vat: cho, meo, chim, ca, ga"
    thumbnail_url = "https://placehold.co/400x300/f97316/white?text=Dong+Vat"
    order         = "2"
}

try {
    $r = Invoke-WebRequest -Uri "$base/admin/dictionary-topics" -Method POST -Body $body -UseBasicParsing -WebSession $sess -MaximumRedirection 5
    Write-Output "    Status: $($r.StatusCode) (followed redirect)"
} catch {
    $msg = $_.ToString()
    if ($msg -match "redirect" -or $msg -match "302") {
        Write-Output "    Status: 302 (redirect - OK)"
    } else {
        Write-Output "    ERROR: $msg"
        exit 1
    }
}

# Step 3: Follow redirect - check index page
Write-Output "[3] GET /admin/dictionary-topics (index) ..."
$idx = Invoke-WebRequest -Uri "$base/admin/dictionary-topics" -UseBasicParsing -TimeoutSec 15
Write-Output "    Status: $($idx.StatusCode)"

if ($idx.Content -match "Dong vat") {
    Write-Output "    FOUND 'Dong vat' in topic list!"
} else {
    Write-Output "    WARNING: 'Dong vat' NOT found in page"
}

# Step 4: Cleanup - delete via API
Write-Output "[4] Cleanup - finding created topic ID ..."
$topics = Invoke-RestMethod -Uri "$base/api/admin/dictionary-topics" -Method GET -TimeoutSec 15
$found = $topics | Where-Object { $_.title -eq "Dong vat" } | Select-Object -First 1

if ($found) {
    $cid = $found.cloud_id
    Write-Output "    Found cloud_id: $cid"
    Invoke-RestMethod -Uri "$base/api/admin/dictionary-topics/$cid" -Method DELETE -TimeoutSec 15
    Write-Output "    Deleted."
} else {
    Write-Output "    No matching topic to clean up."
}

Write-Output ""
Write-Output "=== ALL DONE ==="
