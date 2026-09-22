import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

class AuthApi {
  AuthApi({String? baseUrl})
      : baseUrl =
            baseUrl ??
            const String.fromEnvironment(
              'API_BASE_URL',
              defaultValue: '',
            );

  final String baseUrl;

  String get apiBaseUrl {
    if (baseUrl.isNotEmpty) {
      return baseUrl;
    }

    // Flutter Web / Chrome
    if (kIsWeb) {
      return 'http://127.0.0.1:8000/api';
    }

    // Android Emulator
    if (defaultTargetPlatform == TargetPlatform.android) {
      return 'http://10.0.2.2:8000/api';
    }

    // Windows / Desktop
    return 'http://127.0.0.1:8000/api';
  }

  Future<Map<String, dynamic>> _post(
    String path,
    Map<String, dynamic> body, {
    String? token,
  }) async {
    final response = await http.post(
      Uri.parse('$apiBaseUrl$path'),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      },
      body: jsonEncode(body),
    );

    Map<String, dynamic> data;

    try {
      data = jsonDecode(response.body) as Map<String, dynamic>;
    } catch (_) {
      throw Exception(
        'Response server tidak valid '
        '(HTTP ${response.statusCode}).',
      );
    }

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

  Future<void> verifyEmail(
    String email,
    String code,
  ) async {
    await _post('/verify-email', {
      'email': email,
      'otp_code': code,
    });
  }

  Future<void> resendCode(String email) async {
    await _post('/resend-code', {
      'email': email,
    });
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
    await _post(
      '/logout',
      {},
      token: token,
    );
  }
}