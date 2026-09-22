import '../../domain/entities/officer_entities.dart';
import '../../domain/repositories/officer_repository.dart';

class MockOfficerRepository implements OfficerRepository {
  final Incident incident = const Incident(id: '1', incidentCode: 'KH-0102', title: 'Kebakaran Lahan Gambut', location: 'Desa Sukamaju, Sektor Barat 04', latitude: -0.2841, longitude: 101.4298, riskIndex: 87, priority: 'PRIORITAS TINGGI', distanceKm: 2.5, estimatedMinutes: 8, wind: '14 km/j B', waterSource: 'Kanal 800 m');
  late List<OfficerTask> _tasks = [OfficerTask(id: 'T-102', incident: incident, status: TaskStatus.assigned, area: 'Lahan Gambut Kering'), OfficerTask(id: 'T-103', incident: const Incident(id: '2', incidentCode: 'KH-0103', title: 'Kebakaran Hutan', location: 'Kec. Sungai Raya, Blok C', latitude: -0.281, longitude: 101.425, riskIndex: 64, priority: 'PRIORITAS SEDANG', distanceKm: 5.4, estimatedMinutes: 18, wind: '12 km/j T', waterSource: 'Kanal 1,5 km'), status: TaskStatus.enRoute, area: 'Hutan Produksi')];
  final List<FieldReport> _reports = [const FieldReport(id: 'RPT-2026-981', title: 'Foto kondisi api', createdAt: '14 Mar 2026, 10:20 WIB', status: SyncStatus.synced, type: 'Foto'), const FieldReport(id: 'RPT-2026-980', title: 'Video penanganan', createdAt: '14 Mar 2026, 10:05 WIB', status: SyncStatus.pending, type: 'Video', size: '14.2 MB'), const FieldReport(id: 'RPT-2026-979', title: 'Catatan lapangan', createdAt: '14 Mar 2026, 09:50 WIB', status: SyncStatus.synced, type: 'Catatan', description: 'Angin kencang arah timur laut, sekat bakar kanal C-12 siap menampung suplai air TRC.')];
  @override Future<List<OfficerTask>> getTasks() async => List.of(_tasks);
  @override Future<List<FieldReport>> getReports() async => List.of(_reports);
  @override Future<void> updateTaskStatus(String id, TaskStatus status) async { _tasks = _tasks.map((x) => x.id == id ? x.copyWith(status: status) : x).toList(); }
  @override Future<void> createReport(FieldReport report) async => _reports.insert(0, report);
  @override Future<void> sync() async { await Future<void>.delayed(const Duration(milliseconds: 650)); for (var i=0;i<_reports.length;i++) { if (_reports[i].status == SyncStatus.pending) _reports[i] = FieldReport(id:_reports[i].id,title:_reports[i].title,createdAt:_reports[i].createdAt,status:SyncStatus.synced,type:_reports[i].type,description:_reports[i].description,size:_reports[i].size); } }
}
