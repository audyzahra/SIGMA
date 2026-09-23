class CitizenProfile {
  const CitizenProfile({
    required this.name,
    required this.email,
    required this.verified,
    this.region,
  });
  factory CitizenProfile.fromJson(Map<String, dynamic> json) => CitizenProfile(
    name: json['name'] as String,
    email: json['email'] as String,
    verified: json['email_verified'] as bool? ?? false,
    region: json['region'] as String?,
  );
  final String name, email;
  final bool verified;
  final String? region;
}

class CitizenDashboard {
  const CitizenDashboard({
    required this.profile,
    required this.statistics,
    required this.latestReports,
    required this.incidents,
    required this.emergencyNumber,
    required this.unreadNotificationCount,
    this.risk,
  });

  factory CitizenDashboard.fromJson(Map<String, dynamic> json) {
    final emergency = json['emergency'] as Map<String, dynamic>? ?? const {};
    return CitizenDashboard(
      profile: CitizenProfile.fromJson(json['profile'] as Map<String, dynamic>),
      statistics: CitizenReportStatistics.fromJson(
        json['report_statistics'] as Map<String, dynamic>? ?? const {},
      ),
      latestReports: (json['latest_reports'] as List<dynamic>? ?? const [])
          .whereType<Map<String, dynamic>>()
          .map(CitizenReport.fromJson)
          .toList(),
      incidents: (json['incidents'] as List<dynamic>? ?? const [])
          .whereType<Map<String, dynamic>>()
          .map(CitizenIncident.fromJson)
          .toList(),
      risk: json['risk'] is Map<String, dynamic>
          ? CitizenRisk.fromJson(json['risk'] as Map<String, dynamic>)
          : null,
      emergencyNumber: emergency['number']?.toString() ?? '',
      unreadNotificationCount:
          (json['unread_notification_count'] as num?)?.toInt() ?? 0,
    );
  }

  final CitizenProfile profile;
  final CitizenReportStatistics statistics;
  final List<CitizenReport> latestReports;
  final List<CitizenIncident> incidents;
  final CitizenRisk? risk;
  final String emergencyNumber;
  final int unreadNotificationCount;
}

class CitizenReportStatistics {
  const CitizenReportStatistics({
    required this.total,
    required this.process,
    required this.completed,
  });
  factory CitizenReportStatistics.fromJson(Map<String, dynamic> json) =>
      CitizenReportStatistics(
        total: (json['total'] as num?)?.toInt() ?? 0,
        process: (json['process'] as num?)?.toInt() ?? 0,
        completed: (json['completed'] as num?)?.toInt() ?? 0,
      );
  final int total, process, completed;
}

class CitizenRisk {
  const CitizenRisk({required this.score, required this.level, this.region});
  factory CitizenRisk.fromJson(Map<String, dynamic> json) => CitizenRisk(
    score: double.tryParse(json['score']?.toString() ?? '') ?? 0,
    level: json['level']?.toString() ?? '',
    region: json['region'] as String?,
  );
  final double score;
  final String level;
  final String? region;
}

class CitizenIncident {
  const CitizenIncident({
    required this.locationDescription,
    required this.fireStatus,
    required this.severityLevel,
  });
  factory CitizenIncident.fromJson(Map<String, dynamic> json) =>
      CitizenIncident(
        locationDescription: json['location_description'] as String?,
        fireStatus: json['fire_status']?.toString() ?? '',
        severityLevel: json['severity_level']?.toString() ?? '',
      );
  final String? locationDescription;
  final String fireStatus, severityLevel;
}

class CitizenReport {
  const CitizenReport({
    required this.id,
    required this.number,
    required this.type,
    required this.status,
    required this.description,
    required this.latitude,
    required this.longitude,
    required this.createdAt,
    this.history = const [],
  });
  factory CitizenReport.fromJson(Map<String, dynamic> json) => CitizenReport(
    id: json['id'].toString(),
    number: json['number'] as String,
    type: json['report_type'] as String,
    status: json['status'] as String,
    description: json['description'] as String? ?? '',
    latitude: (json['latitude'] as num).toDouble(),
    longitude: (json['longitude'] as num).toDouble(),
    createdAt:
        DateTime.tryParse(json['created_at'] as String? ?? '') ??
        DateTime.now(),
    history: (json['history'] as List<dynamic>? ?? [])
        .map((item) => ReportHistory.fromJson(item as Map<String, dynamic>))
        .toList(),
  );
  final String id, number, type, status, description;
  final double latitude, longitude;
  final DateTime createdAt;
  final List<ReportHistory> history;
}

class ReportHistory {
  const ReportHistory({
    required this.status,
    required this.description,
    required this.at,
  });
  factory ReportHistory.fromJson(Map<String, dynamic> json) => ReportHistory(
    status: json['status'] as String,
    description: json['description'] as String? ?? '',
    at:
        DateTime.tryParse(json['updated_at'] as String? ?? '') ??
        DateTime.now(),
  );
  final String status, description;
  final DateTime at;
}

class CitizenNotification {
  const CitizenNotification({
    required this.id,
    required this.title,
    required this.message,
    required this.createdAt,
    this.readAt,
    this.reportId,
    this.status,
  });
  factory CitizenNotification.fromJson(Map<String, dynamic> json) =>
      CitizenNotification(
        id: json['id'].toString(),
        title: json['title'] as String? ?? 'Notifikasi SIGMA',
        message: json['message'] as String? ?? '',
        createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ??
            DateTime.now(),
        readAt: json['read_at'] == null
            ? null
            : DateTime.tryParse(json['read_at'] as String),
        reportId: json['report_id']?.toString(),
        status: json['status'] as String?,
      );
  final String id, title, message;
  final DateTime createdAt;
  final DateTime? readAt;
  final String? reportId, status;
  bool get isRead => readAt != null;
}

class MapItem {
  const MapItem({
    required this.id,
    required this.latitude,
    required this.longitude,
    required this.label,
    required this.state,
  });
  factory MapItem.hotspot(Map<String, dynamic> json) => MapItem(
    id: 'hotspot-${json['id']}',
    latitude: (json['latitude'] as num).toDouble(),
    longitude: (json['longitude'] as num).toDouble(),
    label: 'Titik panas',
    state: json['status'] as String,
  );
  factory MapItem.incident(Map<String, dynamic> json) => MapItem(
    id: 'incident-${json['id']}',
    latitude: (json['latitude'] as num).toDouble(),
    longitude: (json['longitude'] as num).toDouble(),
    label: json['location_description'] as String? ?? 'Kejadian',
    state: json['fire_status'] as String,
  );
  factory MapItem.report(Map<String, dynamic> json) => MapItem(
    id: 'report-${json['id']}',
    latitude: (json['latitude'] as num).toDouble(),
    longitude: (json['longitude'] as num).toDouble(),
    label: json['number'] as String,
    state: json['status'] as String,
  );
  final String id, label, state;
  final double latitude, longitude;
}
