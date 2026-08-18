$desktops = @(
    [Environment]::GetFolderPath('Desktop'),
    "C:\Users\$env:USERNAME\OneDrive\Desktop",
    "C:\Users\$env:USERNAME\Desktop"
) | Select-Object -Unique

$wsh = New-Object -ComObject WScript.Shell

foreach ($d in $desktops) {
    if (Test-Path $d) {
        $shortcutPath = Join-Path $d "Shree Giriraj ERP.lnk"
        $shortcut = $wsh.CreateShortcut($shortcutPath)
        $shortcut.TargetPath = "wscript.exe"
        $shortcut.Arguments = "`"C:\xampp\htdocs\shreegiriraj\launch-erp.vbs`""
        $shortcut.WorkingDirectory = "C:\xampp\htdocs\shreegiriraj"
        $shortcut.Description = "Shree Giriraj Poly Plast ERP Desktop Application"
        if (Test-Path "C:\xampp\htdocs\shreegiriraj\laravel\public\favicon.ico") {
            $shortcut.IconLocation = "C:\xampp\htdocs\shreegiriraj\laravel\public\favicon.ico,0"
        }
        $shortcut.Save()
        Write-Host "Created shortcut successfully: $shortcutPath"
    }
}
