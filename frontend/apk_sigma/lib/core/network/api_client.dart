/// Backend gateway. Configure API_BASE_URL with --dart-define when Laravel API is enabled.
class ApiClient { const ApiClient({this.baseUrl = const String.fromEnvironment('API_BASE_URL')}); final String baseUrl; }
