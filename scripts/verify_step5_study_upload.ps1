$ErrorActionPreference = 'Stop'

$base = 'http://127.0.0.1:8000'
$stamp = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
$uid = "user_test_$stamp"
$sessionId = "session_test_$stamp"

$payload = @{
    uid = $uid
    session_id = $sessionId
    started_at = (Get-Date).AddMinutes(-10).ToString('o')
    completed_at = (Get-Date).ToString('o')
    summary = @{
        total_questions = 15
        correct_answers = 12
        total_points = 120
    }
    lesson_progress = @(
        @{
            lesson_cloud_id = 'ls_01_01_tu_vung'
            topic_cloud_id = 'topic_01_chao_hoi'
            is_completed = $true
            score = 80
            completed_at = (Get-Date).ToString('o')
        },
        @{
            lesson_cloud_id = 'ls_01_02_luyen_tap'
            topic_cloud_id = 'topic_01_chao_hoi'
            is_completed = $true
            score = 90
            completed_at = (Get-Date).ToString('o')
        }
    )
    learned_words = @(
        @{ vocab_cloud_id = 'voc_xin_chao'; learned_at = (Get-Date).ToString('o') },
        @{ vocab_cloud_id = 'voc_cam_on'; learned_at = (Get-Date).ToString('o') }
    )
}

$body = $payload | ConvertTo-Json -Depth 20

$first = Invoke-WebRequest -Uri ($base + '/api/study-sessions/upload') -Method POST -ContentType 'application/json' -Body $body
$firstJson = $first.Content | ConvertFrom-Json
Write-Output ('FIRST_STATUS=' + $first.StatusCode)
Write-Output ('FIRST_ALREADY_PROCESSED=' + $firstJson.already_processed)

$second = Invoke-WebRequest -Uri ($base + '/api/study-sessions/upload') -Method POST -ContentType 'application/json' -Body $body
$secondJson = $second.Content | ConvertFrom-Json
Write-Output ('SECOND_STATUS=' + $second.StatusCode)
Write-Output ('SECOND_ALREADY_PROCESSED=' + $secondJson.already_processed)
Write-Output ('SESSION_ID=' + $sessionId)
Write-Output ('UID=' + $uid)
