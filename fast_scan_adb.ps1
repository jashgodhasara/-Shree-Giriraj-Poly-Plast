$ip = '192.168.1.14'
$openPorts = [System.Collections.Concurrent.ConcurrentBag[int]]::new()
$tasks = [System.Collections.Generic.List[System.Threading.Tasks.Task]]::new()

foreach ($port in 30000..49999) {
    $t = [System.Threading.Tasks.Task]::Run([Action]{
        try {
            $client = New-Object System.Net.Sockets.TcpClient
            $async = $client.BeginConnect($ip, $port, $null, $null)
            if ($async.AsyncWaitHandle.WaitOne(30, $false) -and $client.Connected) {
                $openPorts.Add($port)
            }
            $client.Close()
        } catch {}
    })
    $tasks.Add($t)
}

[System.Threading.Tasks.Task]::WaitAll($tasks.ToArray())

foreach ($p in $openPorts) {
    Write-Host "Found open port: $p"
    & adb connect "${ip}:${p}"
}

& adb devices -l
