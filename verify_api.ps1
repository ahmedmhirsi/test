$baseUrl = "http://127.0.0.1:8000/api/v1"

Write-Host "1. Testing Lead Creation (POST)..." -ForegroundColor Cyan
$body = @{
    email = "n8n_test@example.com"
    contactName = "N8N Automator"
    companyName = "Automation Co"
    channel = "LinkedIn"
    status = "new"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/leads" -Method Post -Body $body -ContentType "application/json"
    Write-Host "Success! Created Lead ID: $($response.id)" -ForegroundColor Green
    $leadId = $response.id
} catch {
    Write-Error "Failed to create lead. $_"
    exit 1
}

Write-Host "`n2. Testing Daily Report (GET)..." -ForegroundColor Cyan
try {
    $report = Invoke-RestMethod -Uri "$baseUrl/reports/daily" -Method Get
    Write-Host "Success! Report Date: $($report.date)" -ForegroundColor Green
    Write-Host "New Leads Today: $($report.leads.new)" -ForegroundColor Yellow
} catch {
    Write-Error "Failed to get report. $_"
}

if ($leadId) {
    Write-Host "`n3. Testing Lead Enrichment (PATCH)..." -ForegroundColor Cyan
    $patchBody = @{
        companyName = "Automation Co (Enriched)"
        position = "Head of Automation"
        phone = "+1-555-0199"
    } | ConvertTo-Json

    try {
        $update = Invoke-RestMethod -Uri "$baseUrl/leads/$leadId" -Method Patch -Body $patchBody -ContentType "application/json"
        Write-Host "Success! Updated Lead ID: $($update.id)" -ForegroundColor Green
        Write-Host "New Position: $($update.position)" -ForegroundColor Yellow
    } catch {
        Write-Error "Failed to update lead. $_"
    }
}
