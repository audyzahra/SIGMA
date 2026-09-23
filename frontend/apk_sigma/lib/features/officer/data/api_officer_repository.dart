import 'dart:convert';

import '../domain/entities/officer_entities.dart';
import '../domain/repositories/officer_repository.dart';
import '../../auth/data/datasources/auth_storage.dart';
import '../../../core/network/api_client.dart';

class ApiOfficerRepository implements OfficerRepository {
  ApiOfficerRepository({AuthStorage? storage})
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
    if (token == null) throw Exception('Sesi petugas tidak ditemukan.');
    try {
      final response = await _client.send(
        method,
        '/officer$path',
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: body,
      );
      final decoded = jsonDecode(response.body);
      final json = decoded is Map<String, dynamic>
          ? decoded
          : <String, dynamic>{};
      if (response.statusCode >= 400) {
        throw Exception(
          json['message']?.toString() ?? 'Data petugas tidak dapat dimuat.',
        );
      }
      if (json.isEmpty) throw Exception('Respons layanan petugas tidak valid.');
      return json;
    } on FormatException {
      throw Exception('Respons layanan petugas tidak valid.');
    }
  }

  OfficerTask _task(Map<String, dynamic> json) {
    final incident = json['incident'];
    if (incident is! Map<String, dynamic>) {
      throw Exception('Data insiden untuk tugas tidak tersedia.');
    }
    final latitude = incident['latitude'];
    final longitude = incident['longitude'];
    if (latitude is! num || longitude is! num) {
      throw Exception('Koordinat insiden tidak tersedia.');
    }
    final location =
        incident['location_description']?.toString() ?? 'Lokasi belum tersedia';
    return OfficerTask(
      id: json['id'].toString(),
      area: location,
      status: _status(json['status']?.toString()),
      incident: Incident(
        id: incident['id'].toString(),
        incidentCode: 'INS-${incident['id']}',
        title: location,
        location: location,
        latitude: latitude.toDouble(),
        longitude: longitude.toDouble(),
        riskIndex: 0,
        priority: json['priority_level']?.toString() ?? 'normal',
        distanceKm: 0,
        estimatedMinutes: 0,
        wind: '',
        waterSource: '',
      ),
    );
  }

  TaskStatus _status(String? value) =>
      {
        'assigned': TaskStatus.assigned,
        'accepted': TaskStatus.accepted,
        'traveling': TaskStatus.enRoute,
        'arrived': TaskStatus.arrived,
        'handling': TaskStatus.handling,
        'completed': TaskStatus.completed,
        'cancelled': TaskStatus.cancelled,
      }[value] ??
      TaskStatus.assigned;
  @override
  Future<List<OfficerTask>> getTasks() async {
    final tasks = (await _request('GET', '/tasks'))['tasks'];
    if (tasks is! List) return const [];
    return tasks.whereType<Map<String, dynamic>>().map(_task).toList();
  }

  @override
  Future<List<FieldReport>> getReports() async {
    final reports = (await _request('GET', '/reports'))['reports'];
    if (reports is! List) return const [];
    return reports
        .whereType<Map<String, dynamic>>()
        .map(
          (report) => FieldReport(
            id: report['id'].toString(),
            title: report['incident'] is Map<String, dynamic>
                ? ((report['incident']
                              as Map<String, dynamic>)['location_description']
                          ?.toString() ??
                      'Laporan lapangan')
                : 'Laporan lapangan',
            description: report['description']?.toString() ?? '',
            createdAt: report['verified_at']?.toString() ?? '',
            status: SyncStatus.synced,
            type: 'Verifikasi lapangan',
          ),
        )
        .toList();
  }

  @override
  Future<OfficerProfile> getProfile() async {
    final profile = (await _request('GET', '/profile'))['profile'];
    if (profile is! Map<String, dynamic>)
      throw Exception('Profil petugas tidak tersedia.');
    final teams = profile['teams'];
    final team =
        teams is List && teams.isNotEmpty && teams.first is Map<String, dynamic>
        ? teams.first as Map<String, dynamic>
        : null;
    return OfficerProfile(
      name: profile['name']?.toString() ?? 'Belum tersedia',
      email: profile['email']?.toString() ?? '',
      role: profile['roles'] is List && (profile['roles'] as List).isNotEmpty
          ? (profile['roles'] as List).first.toString()
          : 'Petugas',
      team: team?['name']?.toString(),
      organization: team?['organization']?.toString(),
      online: team?['online_status']?.toString() == 'online',
      lastSeenAt: DateTime.tryParse(team?['last_seen_at']?.toString() ?? ''),
    );
  }

  @override
  Future<void> createReport(String taskId, String description) async {
    await _request(
      'POST',
      '/tasks/$taskId/reports',
      body: {'description': description},
    );
  }

  @override
  Future<void> updateTaskStatus(String taskId, TaskStatus status) async =>
      _request(
        'PATCH',
        '/tasks/$taskId/status',
        body: {
          'status':
              {
                'assigned': 'assigned',
                'enRoute': 'traveling',
                'arrived': 'arrived',
                'handling': 'handling',
                'completed': 'completed',
              }[status.name] ??
              'assigned',
        },
      );
  @override
  Future<void> acceptTask(String taskId) async =>
      _request('PATCH', '/tasks/$taskId/accept');
}
