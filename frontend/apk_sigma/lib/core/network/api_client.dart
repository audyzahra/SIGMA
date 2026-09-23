import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

import '../constants/api_config.dart';

class ApiClient {
  ApiClient({ApiConfig? config, http.Client? client})
    : _config = config ?? ApiConfig(),
      _client = client ?? http.Client();

  final ApiConfig _config;
  final http.Client _client;

  Uri uri(String path) {
    final normalizedPath = path.startsWith('/') ? path.substring(1) : path;
    return Uri.parse('${_config.baseUrl}/$normalizedPath');
  }

  Future<http.Response> send(
    String method,
    String path, {
    Map<String, String> headers = const {},
    Object? body,
  }) async {
    final requestUri = uri(path);
    final request = http.Request(method, requestUri)
      ..headers.addAll(headers);
    if (body != null) {
      request.headers['Content-Type'] = 'application/json';
      request.body = jsonEncode(body);
    }
    if (kDebugMode) {
      debugPrint('API $method $requestUri');
      if (body != null) debugPrint('API request body: ${jsonEncode(body)}');
    }
    try {
      final response = await http.Response.fromStream(await _client.send(request));
      if (kDebugMode) {
        debugPrint('API $method $requestUri -> HTTP ${response.statusCode}');
        debugPrint('API response: ${response.body}');
      }
      return response;
    } catch (error, stackTrace) {
      if (kDebugMode) {
        debugPrint('API $method $requestUri failed: $error');
        debugPrintStack(stackTrace: stackTrace);
      }
      rethrow;
    }
  }

  Future<http.StreamedResponse> sendStream(
    String method,
    String path, {
    Map<String, String> headers = const {},
  }) async {
    final requestUri = uri(path);
    final request = http.Request(method, requestUri)..headers.addAll(headers);
    if (kDebugMode) debugPrint('API $method $requestUri');
    final response = await _client.send(request);
    if (kDebugMode) debugPrint('API response status: ${response.statusCode}');
    return response;
  }
}
