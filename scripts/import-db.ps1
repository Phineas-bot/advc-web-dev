<#
Simple helper to import database/cimen_hrms.sql into MySQL on Windows.
It will attempt to find `mysql` on PATH and run the import.
If `mysql` is not available, it prints the command you should run.
#>
param(
    [string]$DbUser = 'root',
    [string]$DbName = 'cimen_hrms',
    [string]$SqlFile = "database/cimen_hrms.sql"
)

Write-Host "Import helper for $SqlFile into database $DbName (user: $DbUser)"

$mysql = Get-Command mysql -ErrorAction SilentlyContinue
if ($null -ne $mysql) {
    Write-Host "Found mysql client at $($mysql.Path). Running import..."
    & $mysql.Path -u $DbUser -p $DbName < $SqlFile
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Import completed successfully."
    } else {
        Write-Host "mysql exited with code $LASTEXITCODE. Check credentials and that MySQL is running." -ForegroundColor Yellow
    }
} else {
    Write-Host "MySQL client not found on PATH. Run the following command manually (PowerShell):" -ForegroundColor Yellow
    Write-Host "mysql -u $DbUser -p $DbName < $SqlFile" -ForegroundColor Cyan
    Write-Host "Or install MySQL client or use a docker container: docker run --rm -v $(Resolve-Path .):/work -w /work mysql:8 mysql -u root -p $DbName < $SqlFile" -ForegroundColor Cyan
}
