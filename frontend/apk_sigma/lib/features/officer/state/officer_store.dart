import 'package:flutter/foundation.dart';

import '../data/api_officer_repository.dart';
import '../domain/entities/officer_entities.dart';
import '../domain/repositories/officer_repository.dart';
import '../tasks/task_status_helper.dart';

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

  final Set<String> _updatingTaskIds = {};

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

  // Ubah kata sandi akun petugas.
  Future<void> updatePassword({
    required String newPassword,
    required String passwordConfirmation,
  }) async {
    await _repository.updatePassword(
      newPassword: newPassword,
      passwordConfirmation: passwordConfirmation,
    );
  }

  
Future<void> setTaskStatus(
  String taskId,
  TaskStatus status,
) async {
  if (_updatingTaskIds.contains(taskId)) return;

  final index = tasks.indexWhere((task) => task.id == taskId);
  if (index == -1) {
    errorMessage = 'Tugas tidak ditemukan.';
    notifyListeners();
    return;
  }

  final previousTask = tasks[index];

  // Hanya izinkan tahapan berikutnya.
  if (nextStatus(previousTask.status) != status) return;

  _updatingTaskIds.add(taskId);
  errorMessage = null;
  notifyListeners();

  try {
    // Tunggu server memastikan perubahan status berhasil.
    if (status == TaskStatus.accepted) {
      await _repository.acceptTask(taskId);
    } else {
      await _repository.updateTaskStatus(taskId, status);
    }

    // Perbarui status lokal agar tombol berikutnya langsung terbuka.
    final updatedTasks = [...tasks];
    final latestIndex = updatedTasks.indexWhere(
      (task) => task.id == taskId,
    );

    if (latestIndex != -1) {
      updatedTasks[latestIndex] =
          updatedTasks[latestIndex].copyWith(status: status);
      tasks = updatedTasks;
    }
  } catch (_) {
    errorMessage =
        'Status tugas belum dapat diperbarui. Coba lagi.';
  } finally {
    _updatingTaskIds.remove(taskId);
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
