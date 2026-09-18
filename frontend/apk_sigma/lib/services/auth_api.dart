import 'dart:convert';

import 'package:http/http.dart' as http;

class AuthApi {
  AuthApi({String? baseUrl})
    : baseUrl =
          baseUrl ??
          const String.fromEnvironment(
            'API_BASE_URL',
            defaultValue: 'http://10.0.2.2:8000/api',
          );

  final String baseUrl;

  Future<Map<String, dynamic>> _post(
    String path,
    Map<String, dynamic> body, {
    String? token,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl$path'),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      },
      body: jsonEncode(body),
    );
    final data = jsonDecode(response.body) as Map<String, dynamic>;
    if (response.statusCode >= 400) {
      final errors = data['errors'] as Map<String, dynamic>?;
      final firstError = errors?.values.firstOrNull;
      throw Exception(
        firstError is List
            ? firstError.first
            : data['message'] ?? 'Permintaan gagal.',
      );
    }
    return data;
  }

  Future<void> register(
    String name,
    String email,
    String password,
    String confirmation,
    String role,
  ) async {
    await _post('/register', {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': confirmation,
      'role': role,
    });
  }

  Future<void> verifyEmail(String email, String code) async {
    await _post('/verify-email', {'email': email, 'otp_code': code});
  }

  Future<void> resendCode(String email) async {
    await _post('/resend-code', {'email': email});
  }

  Future<Map<String, dynamic>> login(
    String email,
    String password,
    String role,
  ) {
    return _post('/login', {
      'email': email,
      'password': password,
      'role': role,
    });
  }

  Future<void> logout(String token) async {
    await _post('/logout', {}, token: token);
  }
}
