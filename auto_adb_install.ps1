Add-Type -TypeDefinition @"
using System;
using System.Net.Sockets;
using System.Threading.Tasks;
using System.Collections.Concurrent;

public class AdbPortScanner {
    public static int FindOpenPort(string ip, int startPort, int endPort) {
        int found = -1;
        Parallel.For(startPort, endPort + 1, new ParallelOptions { MaxDegreeOfParallelism = 300 }, (p, state) => {
            try {
                using (var client = new TcpClient()) {
                    var result = client.BeginConnect(ip, p, null, null);
                    if (result.AsyncWaitHandle.WaitOne(60, false) && client.Connected) {
                        client.EndConnect(result);
                        found = p;
                        state.Stop();
                    }
                }
            } catch {}
        });
        return found;
    }
}
"@

$phoneIp = "192.168.1.14"
Write-Host "Scanning $phoneIp for active Wireless Debugging port..."
$port = [AdbPortScanner]::FindOpenPort($phoneIp, 30000, 50000)

if ($port -gt 0) {
    Write-Host ">>> FOUND OPEN PORT ON PHONE: $port <<<"
    & adb connect "${phoneIp}:${port}"
    Start-Sleep -Seconds 1
    & adb -s "${phoneIp}:${port}" install -r -d "c:\xampp\htdocs\shreegiriraj\ShreeGirirajERP.apk"
} else {
    Write-Host "No open ADB port found on 30000-50000. Testing 5555..."
    $p5555 = [AdbPortScanner]::FindOpenPort($phoneIp, 5555, 5555)
    if ($p5555 -gt 0) {
        & adb connect "${phoneIp}:5555"
        & adb -s "${phoneIp}:5555" install -r -d "c:\xampp\htdocs\shreegiriraj\ShreeGirirajERP.apk"
    }
}

& adb devices -l
