class ApiConfig {
  // Change this IP to your PC's local IP when testing on physical device
  // For Android emulator use: 10.0.2.2
  // For physical device use your PC IP e.g.: 192.168.1.100
  static const String baseUrl = 'http://10.0.2.2:8000/api';

  static const Map<String, String> headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };
}
