Add-Type -TypeDefinition @"
using System;
using System.Net.Sockets;
using System.Threading.Tasks;
using System.Collections.Concurrent;

public class PortScanner {
    public static int[] Scan(string ip, int startPort, int endPort, int timeoutMs) {
        var openPorts = new ConcurrentBag<int>();
        Parallel.For(startPort, endPort + 1, new ParallelOptions { MaxDegreeOfParallelism = 250 }, p => {
            try {
                using (var client = new TcpClient()) {
                    var result = client.BeginConnect(ip, p, null, null);
                    bool success = result.AsyncWaitHandle.WaitOne(timeoutMs, false);
                    if (success && client.Connected) {
                        client.EndConnect(result);
                        openPorts.Add(p);
                        Console.WriteLine(">>> Found open port: " + p);
                    }
                }
            } catch {}
        });
        return openPorts.ToArray();
    }
}
"@

$ips = @("192.168.1.14", "192.168.1.1", "192.168.1.12", "192.168.1.15", "192.168.1.16")

foreach ($ip in $ips) {
    Write-Host "Scanning $ip..."
    $ports = [PortScanner]::Scan($ip, 30000, 50000, 80)
    if ($ports.Length -gt 0) {
        Write-Host "Open ports on $ip : $($ports -join ', ')"
        foreach ($p in $ports) {
            & adb connect "${ip}:${p}"
        }
    }
    # Also check 5555
    $p5555 = [PortScanner]::Scan($ip, 5555, 5555, 100)
    if ($p5555.Length -gt 0) {
        Write-Host "Port 5555 open on $ip!"
        & adb connect "${ip}:5555"
    }
}

& adb devices -l
