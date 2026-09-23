import 'dart:convert';

import '../../../../core/constants/api_config.dart';
import '../../../../core/network/api_client.dart';

/// Transitional facade used by the existing feature APIs.
class AuthApi {
  AuthApi({String? baseUrl})
    : _config = ApiConfig(baseUrl: baseUrl),
      _client = ApiClient(config: ApiConfig(baseUrl: baseUrl));

  final ApiConfig _config;
  final ApiClient _client;
  String get apiBaseUrl => _config.baseUrl;

  Future<Map<String, dynamic>> post(String path, Map<String, dynamic> body, {String? token}) async {
    final response = await _client.send('POST', path, headers: {
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    }, body: body);
    final decoded = jsonDecode(response.body);
    if (decoded is! Map<String, dynamic>) {
      throw Exception('Respons server tidak valid (HTTP ${response.statusCode}).');
    }
    if (response.statusCode >= 400) {
      final errors = decoded['errors'];
      final first = errors is Map && errors.isNotEmpty ? errors.values.first : null;
      throw Exception(first is List && first.isNotEmpty ? first.first : decoded['message'] ?? 'Permintaan gagal.');
    }
    return decoded;
  }

  Future<void> logout(String token) async => post('/logout', {}, token: token);
}
