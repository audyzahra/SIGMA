import 'package:flutter/foundation.dart';

/// Resolves the Laravel API base once for every feature.
/// API_BASE_URL may be either the server root or the full /api prefix.
class ApiConfig {
  ApiConfig({String? baseUrl}) : _configuredBaseUrl = baseUrl ?? _environmentUrl;

  static const _environmentUrl = String.fromEnvironment('API_BASE_URL');
  final String _configuredBaseUrl;

  String get baseUrl {
    final configured = _configuredBaseUrl.trim();
    final value = configured.isNotEmpty ? configured : _platformDefault;
    final uri = Uri.parse(value);
    if (!uri.hasScheme || uri.host.isEmpty) {
      throw ArgumentError.value(value, 'API_BASE_URL', 'Must be an absolute URL.');
    }
    final segments = uri.pathSegments.where((part) => part.isNotEmpty).toList();
    while (segments.length > 1 && segments.last == 'api' && segments[segments.length - 2] == 'api') {
      segments.removeLast();
    }
    if (segments.isEmpty || segments.last != 'api') segments.add('api');
    return uri.replace(pathSegments: segments, query: null, fragment: null).toString().replaceFirst(RegExp(r'/$'), '');
  }

  String get _platformDefault {
    if (kIsWeb) return 'http://127.0.0.1:8000';
    if (defaultTargetPlatform == TargetPlatform.android) return 'http://10.0.2.2:8000';
    return 'http://127.0.0.1:8000';
  }
}
