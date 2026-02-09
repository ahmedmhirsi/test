Write-Host "Downloading ngrok..." -ForegroundColor Cyan
$url = "https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-windows-amd64.zip"
$output = "ngrok.zip"
Invoke-WebRequest -Uri $url -OutFile $output

Write-Host "Unzipping..." -ForegroundColor Cyan
Expand-Archive -Path $output -DestinationPath . -Force

Write-Host "Starting Tunnel on Port 8000..." -ForegroundColor Green
Write-Host "Look for the URL starting with https:// in the output below!" -ForegroundColor Yellow
Write-Host "--------------------------------------------------------" -ForegroundColor White

./ngrok http 8000
