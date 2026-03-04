$ErrorActionPreference = 'Stop'

$base = 'http://127.0.0.1:8000'

$v1 = Invoke-WebRequest -Uri ($base + '/api/content/version') -Method GET
Write-Output ('VERSION_BEFORE=' + $v1.Content)

$publishBody = @{ notes = 'step4 publish test'; published_by = 'local-script' } | ConvertTo-Json
$pub = Invoke-WebRequest -Uri ($base + '/api/admin/content/publish') -Method POST -ContentType 'application/json' -Body $publishBody
Write-Output ('PUBLISH_STATUS=' + $pub.StatusCode)
Write-Output ('PUBLISH_BODY=' + $pub.Content)

$v2 = Invoke-WebRequest -Uri ($base + '/api/content/version') -Method GET
Write-Output ('VERSION_AFTER=' + $v2.Content)

$boot = Invoke-WebRequest -Uri ($base + '/api/content/bootstrap') -Method GET
Write-Output ('CONTENT_BOOTSTRAP_STATUS=' + $boot.StatusCode)

$dict = Invoke-WebRequest -Uri ($base + '/api/dictionary/bootstrap') -Method GET
Write-Output ('DICT_BOOTSTRAP_STATUS=' + $dict.StatusCode)
