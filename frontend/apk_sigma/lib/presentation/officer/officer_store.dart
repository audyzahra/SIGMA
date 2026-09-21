import 'package:flutter/foundation.dart';
import '../../data/repositories/mock_officer_repository.dart';
import '../../domain/entities/officer_entities.dart';
import '../../domain/repositories/officer_repository.dart';

class OfficerStore extends ChangeNotifier {
  OfficerStore({OfficerRepository? repository}) : _repository = repository ?? MockOfficerRepository();
  final OfficerRepository _repository;
  List<OfficerTask> tasks = [];
  List<FieldReport> reports = [];
  ConnectivityStatus connectivity = ConnectivityStatus.online;
  bool loaded = false;
  Future<void> load() async { tasks = await _repository.getTasks(); reports = await _repository.getReports(); loaded = true; notifyListeners(); }
  OfficerTask get activeTask => tasks.firstWhere(
        (task) => task.incident.incidentCode == 'KH-0102',
        orElse: () => tasks.first,
      );
  Future<void> setTaskStatus(TaskStatus status) async { await _repository.updateTaskStatus(activeTask.id, status); await load(); }
  Future<void> addReport(String title, {String type = 'Catatan'}) async {
    await _repository.createReport(FieldReport(
      id: 'RPT-${DateTime.now().millisecondsSinceEpoch}',
      title: title,
      createdAt: 'Baru saja',
      status: SyncStatus.pending,
      type: type,
    ));
    await load();
    if (connectivity == ConnectivityStatus.online) await sync();
  }
  Future<void> sync() async {
    if (connectivity == ConnectivityStatus.offline) return;
    await _repository.sync();
    await load();
  }
  void toggleConnectivity() { connectivity = connectivity == ConnectivityStatus.online ? ConnectivityStatus.offline : ConnectivityStatus.online; notifyListeners(); }
}
