$files = Get-ChildItem -Path 'c:\xampp\htdocs\mystical' -Recurse -Filter '*.php' -File |
    Where-Object { $_.FullName -notmatch 'vendor' }

foreach ($f in $files) {
    $result = php -l $f.FullName 2>&1
    if ($LASTEXITCODE -ne 0 -or ($result -match 'No syntax errors' -and $result -match 'Errors parsing')) {
        Write-Host "FAIL: $($f.FullName)"
        Write-Host $result
    } else {
        Write-Host "OK:   $($f.Name)"
    }
}