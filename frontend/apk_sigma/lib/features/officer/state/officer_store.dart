import 'package:flutter/foundation.dart';

import '../data/api_officer_repository.dart';
import '../domain/entities/officer_entities.dart';
import '../domain/repositories/officer_repository.dart';

class OfficerStore extends ChangeNotifier {
  OfficerStore({OfficerRepository? repository})
    : _repository = repository ?? ApiOfficerRepository();
  final OfficerRepository _repository;
  List<OfficerTask> tasks = [];
  List<FieldReport> reports = [];
  OfficerProfile? profile;
  ConnectivityStatus connectivity = ConnectivityStatus.online;
  bool loaded = false;
  bool loading = false;
  String? errorMessage;

  Future<void> load() async {
    loading = true;
    errorMessage = null;
    notifyListeners();
    try {
      tasks = await _repository.getTasks();
      reports = await _repository.getReports();
      try {
        profile = await _repository.getProfile();
      } catch (_) {
        profile = null;
      }
      loaded = true;
    } catch (_) {
      tasks = [];
      reports = [];
      profile = null;
      errorMessage =
          'Data petugas belum dapat dimuat. Periksa koneksi lalu coba lagi.';
    } finally {
      loading = false;
      notifyListeners();
    }
  }

  OfficerTask? get activeTask => tasks.isEmpty ? null : tasks.first;
  Future<void> setTaskStatus(String taskId, TaskStatus status) async {
    try {
      if (status == TaskStatus.accepted) {
        await _repository.acceptTask(taskId);
      } else {
        await _repository.updateTaskStatus(taskId, status);
      }
      await load();
    } catch (_) {
      errorMessage = 'Status tugas belum dapat diperbarui. Coba lagi.';
      notifyListeners();
    }
  }

  Future<bool> createReport(String taskId, String description) async {
    try {
      await _repository.createReport(taskId, description);
      await load();
      return errorMessage == null;
    } catch (_) {
      errorMessage = 'Laporan lapangan belum dapat dikirim. Coba lagi.';
      notifyListeners();
      return false;
    }
  }

  void toggleConnectivity() {
    connectivity = connectivity == ConnectivityStatus.online
        ? ConnectivityStatus.offline
        : ConnectivityStatus.online;
    notifyListeners();
  }
}
