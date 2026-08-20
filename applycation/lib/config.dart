// ─── API Configuration ───────────────────────────────────────────────────────
// Android emulator  → 10.0.2.2:8000
// Physical device   → your PC's LAN IP e.g. 192.168.1.5:8000
const String kBaseUrl = 'http://10.0.2.2:8000/api';

const Map<String, String> kHeaders = {
  'Content-Type': 'application/json',
  'Accept':       'application/json',
};
