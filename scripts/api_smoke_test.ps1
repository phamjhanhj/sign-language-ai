$ErrorActionPreference = 'Stop'

$base = 'http://127.0.0.1:8000'
$stamp = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
$learningId = "topic_api_test_$stamp"
$dictId = "dic_topic_api_test_$stamp"
$vocabId = "voc_api_test_$stamp"
$payloadPath = 'D:\sign-language-ai\docs\postman\generate-lessons.payload.sample.json'
$payload = Get-Content $payloadPath -Raw | ConvertFrom-Json

$results = New-Object System.Collections.Generic.List[object]

function Add-Result {
    param($Name, $Pass, $Status, $Detail)
    $results.Add([pscustomobject]@{
        endpoint = $Name
        pass = $Pass
        status = $Status
        detail = $Detail
    }) | Out-Null
}

function Invoke-Test {
    param(
        [string]$Name,
        [string]$Method,
        [string]$Url,
        $Body = $null
    )

    try {
        if ($null -ne $Body) {
            $json = $Body | ConvertTo-Json -Depth 30
            $resp = Invoke-WebRequest -Uri $Url -Method $Method -ContentType 'application/json' -Body $json
        }
        else {
            $resp = Invoke-WebRequest -Uri $Url -Method $Method
        }

        Add-Result -Name $Name -Pass $true -Status $resp.StatusCode -Detail 'ok'
        if ($resp.Content) {
            try {
                return ($resp.Content | ConvertFrom-Json)
            }
            catch {
                return $null
            }
        }
        return $null
    }
    catch {
        $status = 0
        if ($_.Exception.Response) {
            $status = [int]$_.Exception.Response.StatusCode
        }
        Add-Result -Name $Name -Pass $false -Status $status -Detail $_.Exception.Message
        return $null
    }
}

Invoke-Test 'POST /api/admin/learning-topics' 'POST' "$base/api/admin/learning-topics" @{
    cloud_id = $learningId
    title = 'Topic API Test'
    description = 'smoke test'
    order_index = 99
    is_active = $true
} | Out-Null

Invoke-Test 'GET /api/admin/learning-topics' 'GET' "$base/api/admin/learning-topics" | Out-Null
Invoke-Test 'GET /api/admin/learning-topics/{id}' 'GET' "$base/api/admin/learning-topics/$learningId" | Out-Null
Invoke-Test 'PUT /api/admin/learning-topics/{id}' 'PUT' "$base/api/admin/learning-topics/$learningId" @{ title = 'Topic API Test Updated'; order_index = 100 } | Out-Null
Invoke-Test 'POST /api/admin/learning-topics/{id}/generate-lessons' 'POST' "$base/api/admin/learning-topics/$learningId/generate-lessons" $payload | Out-Null

Invoke-Test 'POST /api/admin/dictionary-topics' 'POST' "$base/api/admin/dictionary-topics" @{ cloud_id = $dictId; name = 'Dictionary API Test'; order_index = 77 } | Out-Null
Invoke-Test 'GET /api/admin/dictionary-topics' 'GET' "$base/api/admin/dictionary-topics" | Out-Null
Invoke-Test 'GET /api/admin/dictionary-topics/{id}' 'GET' "$base/api/admin/dictionary-topics/$dictId" | Out-Null
Invoke-Test 'PUT /api/admin/dictionary-topics/{id}' 'PUT' "$base/api/admin/dictionary-topics/$dictId" @{ name = 'Dictionary API Test Updated' } | Out-Null

Invoke-Test 'POST /api/admin/dictionary-topics/{id}/vocabularies' 'POST' "$base/api/admin/dictionary-topics/$dictId/vocabularies" @{
    cloud_id = $vocabId
    word = 'Tu test'
    video_url = 'https://example.com/video.mp4'
    definition = 'Dinh nghia test'
} | Out-Null

Invoke-Test 'GET /api/admin/dictionary-topics/{id}/vocabularies' 'GET' "$base/api/admin/dictionary-topics/$dictId/vocabularies" | Out-Null
Invoke-Test 'GET /api/admin/dictionary-topics/{id}/vocabularies/{vocabId}' 'GET' "$base/api/admin/dictionary-topics/$dictId/vocabularies/$vocabId" | Out-Null
Invoke-Test 'PUT /api/admin/dictionary-topics/{id}/vocabularies/{vocabId}' 'PUT' "$base/api/admin/dictionary-topics/$dictId/vocabularies/$vocabId" @{
    word = 'Tu test updated'
    video_url = 'https://example.com/video2.mp4'
    definition = 'Dinh nghia test updated'
} | Out-Null

Invoke-Test 'DELETE /api/admin/dictionary-topics/{id}/vocabularies/{vocabId}' 'DELETE' "$base/api/admin/dictionary-topics/$dictId/vocabularies/$vocabId" | Out-Null
Invoke-Test 'DELETE /api/admin/dictionary-topics/{id}' 'DELETE' "$base/api/admin/dictionary-topics/$dictId" | Out-Null
Invoke-Test 'DELETE /api/admin/learning-topics/{id}' 'DELETE' "$base/api/admin/learning-topics/$learningId" | Out-Null

$passCount = ($results | Where-Object { $_.pass }).Count
$total = $results.Count

Write-Output "PASS $passCount/$total"
$results | ConvertTo-Json -Depth 6
