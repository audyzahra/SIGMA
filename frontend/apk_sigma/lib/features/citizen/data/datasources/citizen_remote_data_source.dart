import 'dart:convert';

import 'package:flutter/foundation.dart';

import '../models/citizen_models.dart';
import '../../../auth/data/datasources/auth_storage.dart';
import '../../../../core/network/api_client.dart';

class CitizenApiException implements Exception {
  const CitizenApiException(this.statusCode, this.userMessage);
  final int statusCode;
  final String userMessage;
  @override
  String toString() => userMessage;
}

class CitizenApi {
  CitizenApi({AuthStorage? storage})
    : _storage = storage ?? AuthStorage(),
      _client = ApiClient();
  final AuthStorage _storage;
  final ApiClient _client;

  Future<Map<String, dynamic>> _request(
    String method,
    String path, {
    Map<String, dynamic>? body,
  }) async {
    final token = await _storage.getToken();
    if (token == null)
      throw Exception('Sesi tidak ditemukan. Silakan masuk kembali.');
    final uri = _client.uri('/citizen$path');
    final headers = {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    };
    try {
      final response = await _client.send(method, '/citizen$path', headers: headers, body: body);
      if (kDebugMode) {
        final body = response.body.length > 2000
            ? '${response.body.substring(0, 2000)}…'
            : response.body;
        debugPrint('Citizen API $method $uri → HTTP ${response.statusCode}');
        debugPrint('Citizen API response: $body');
      }
      Map<String, dynamic>? json;
      try {
        final decoded = jsonDecode(response.body);
        json = decoded is Map<String, dynamic> ? decoded : null;
      } on FormatException {
        // Laravel may return an HTML error page before its JSON handler starts.
      }
      if (response.statusCode >= 400) {
        final message = json?['message'] as String?;
        throw CitizenApiException(
          response.statusCode,
          response.statusCode == 401 || response.statusCode == 403
              ? 'Sesi masuk tidak valid atau tidak memiliki akses. Silakan masuk kembali.'
              : message ?? 'Layanan belum dapat memuat data. Coba lagi.',
        );
      }
      if (json == null) {
        throw const CitizenApiException(
          0,
          'Respons layanan tidak valid. Coba lagi.',
        );
      }
      return json;
    } catch (error, stackTrace) {
      if (kDebugMode) {
        debugPrint('Citizen API failed: $method $uri — $error');
        debugPrintStack(stackTrace: stackTrace);
      }
      if (error is CitizenApiException) rethrow;
      throw const CitizenApiException(
        0,
        'Koneksi ke layanan SIGMA gagal. Periksa jaringan lalu coba lagi.',
      );
    }
  }

  Future<CitizenDashboard> dashboard() async =>
      CitizenDashboard.fromJson(await _request('GET', '/dashboard'));
  Future<Map<String, dynamic>> reports() => _request('GET', '/reports');
  Future<CitizenReport> report(String id) async => CitizenReport.fromJson(
    (await _request('GET', '/reports/$id'))['report'] as Map<String, dynamic>,
  );
  Future<CitizenReport> submit({
    required String type,
    required double latitude,
    required double longitude,
    required String description,
  }) async => CitizenReport.fromJson(
    (await _request(
          'POST',
          '/reports',
          body: {
            'report_type': type,
            'latitude': latitude,
            'longitude': longitude,
            'description': description,
          },
        ))['report']
        as Map<String, dynamic>,
  );
  Future<Map<String, dynamic>> map() => _request('GET', '/map');
  Future<CitizenProfile> profile() async => CitizenProfile.fromJson(
    (await _request('GET', '/profile'))['profile'] as Map<String, dynamic>,
  );
  Future<CitizenProfile> updateName(String name) async =>
      CitizenProfile.fromJson(
        (await _request('PATCH', '/profile', body: {'name': name}))['profile']
            as Map<String, dynamic>,
      );
  Future<List<CitizenNotification>> notifications() async {
    final data = await _request('GET', '/notifications');
    return (data['notifications'] as List<dynamic>)
        .map((item) => CitizenNotification.fromJson(item as Map<String, dynamic>))
        .toList();
  }

  Future<int> unreadNotificationCount() async =>
      ((await _request('GET', '/notifications/unread-count'))['unread_count']
              as num)
          .toInt();

  Future<void> markNotificationRead(String id) async =>
      _request('PATCH', '/notifications/$id/read');
  Future<void> markAllNotificationsRead() async =>
      _request('PATCH', '/notifications/read-all');

  /// Long-lived authenticated SSE connection for foreground notification events.
  /// A reconnect happens only when the server closes its stream, not as a UI poll.
  Stream<void> notificationEvents() async* {
    while (true) {
      final token = await _storage.getToken();
      if (token == null) return;
      try {
        final response = await _client.sendStream(
          'GET',
          '/citizen/notifications/stream',
          headers: {
            'Accept': 'text/event-stream',
            'Authorization': 'Bearer $token',
          },
        );
        if (response.statusCode >= 400) return;
        await for (final line in response.stream
            .transform(utf8.decoder)
            .transform(const LineSplitter())) {
          if (line == 'event: notification') yield null;
        }
      } catch (_) {
        // The next stream attempt is made when the transport disconnects.
      }
    }
  }
}
