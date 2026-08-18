$ip = "192.168.1.14"
& adb disconnect

Write-Host "Scanning active ports on $ip..."
$foundPort = $null

foreach ($port in 30000..49999) {
    $client = New-Object System.Net.Sockets.TcpClient
    try {
        $ar = $client.BeginConnect($ip, $port, $null, $null)
        if ($ar.AsyncWaitHandle.WaitOne(10, $false) -and $client.Connected) {
            $foundPort = $port
            Write-Host "Found open Wireless Debugging port: $port"
            $client.Close()
            break
        }
    } catch {}
    $client.Close()
}

if ($foundPort) {
    & adb connect "${ip}:${foundPort}"
    Start-Sleep -Seconds 1
    & adb -s "${ip}:${foundPort}" install -r "c:\xampp\htdocs\shreegiriraj\ShreeGirirajERP.apk"
    & adb -s "${ip}:${foundPort}" shell monkey -p com.shreegiriraj.erp -c android.intent.category.LAUNCHER 1
} else {
    Write-Host "No open ADB port found on $ip"
}

& adb devices -l
