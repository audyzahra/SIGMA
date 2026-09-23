enum TaskStatus {
  assigned,
  accepted,
  enRoute,
  arrived,
  handling,
  extinguishing,
  cooling,
  completed,
  cancelled,
}

enum SyncStatus { pending, syncing, synced, failed }

enum ConnectivityStatus { online, offline }

class Incident {
  const Incident({
    required this.id,
    required this.incidentCode,
    required this.title,
    required this.location,
    required this.latitude,
    required this.longitude,
    required this.riskIndex,
    required this.priority,
    required this.distanceKm,
    required this.estimatedMinutes,
    required this.wind,
    required this.waterSource,
  });
  final String id, incidentCode, title, location, priority, wind, waterSource;
  final double latitude, longitude, distanceKm;
  final int riskIndex, estimatedMinutes;
}

class OfficerTask {
  const OfficerTask({
    required this.id,
    required this.incident,
    required this.status,
    required this.area,
  });
  final String id, area;
  final Incident incident;
  final TaskStatus status;
  OfficerTask copyWith({TaskStatus? status}) => OfficerTask(
    id: id,
    incident: incident,
    status: status ?? this.status,
    area: area,
  );
}

class FieldReport {
  const FieldReport({
    required this.id,
    required this.title,
    required this.createdAt,
    required this.status,
    required this.type,
    this.description = '',
    this.size = '1,8 MB',
  });
  final String id, title, createdAt, description, size, type;
  final SyncStatus status;
}

class OfficerProfile {
  const OfficerProfile({
    required this.name,
    required this.email,
    required this.role,
    required this.team,
    required this.organization,
    required this.online,
    this.lastSeenAt,
  });

  final String name;
  final String email;
  final String role;
  final String? team;
  final String? organization;
  final bool online;
  final DateTime? lastSeenAt;
}
