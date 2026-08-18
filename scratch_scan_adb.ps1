$ip = '192.168.1.14'
Write-Host "Scanning Wireless Debugging ports on $ip..."

$found = @()
foreach ($port in 30000..49999) {
    try {
        $socket = New-Object System.Net.Sockets.Socket([System.Net.Sockets.AddressFamily]::InterNetwork, [System.Net.Sockets.SocketType]::Stream, [System.Net.Sockets.ProtocolType]::Tcp)
        $connect = $socket.BeginConnect($ip, $port, $null, $null)
        if ($connect.AsyncWaitHandle.WaitOne(15, $false) -and $socket.Connected) {
            $socket.Close()
            $found += $port
            Write-Host "Found open port: $port"
            adb connect "$($ip):$($port)"
            break
        }
        $socket.Close()
    } catch {}
}

adb devices
