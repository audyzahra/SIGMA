import 'dart:async';

import 'package:flutter/foundation.dart';

import '../data/models/citizen_models.dart';
import '../data/datasources/citizen_remote_data_source.dart';

/// Shared notification state. It always reads the Laravel database; no local
/// notification list or hard-coded badge count is maintained.
class CitizenNotificationStore extends ChangeNotifier {
  CitizenNotificationStore(this._api);
  final CitizenApi _api;
  List<CitizenNotification> items = const [];
  bool loading = true;
  bool loadedOnce = false;
  String? error;
  bool _disposed = false;
  bool _requestInFlight = false;
  bool _reloadRequested = false;
  StreamSubscription<void>? _events;
  int get unreadCount => items.where((item) => !item.isRead).length;

  Future<void> load() async {
    if (_requestInFlight) {
      _reloadRequested = true;
      return;
    }
    _requestInFlight = true;
    loading = true;
    notifyListeners();
    try {
      items = await _api.notifications();
      error = null;
    } catch (_) {
      error = 'Notifikasi tidak dapat dimuat. Periksa koneksi lalu coba lagi.';
    } finally {
      _requestInFlight = false;
      loading = false;
      loadedOnce = true;
      if (!_disposed) {
        notifyListeners();
        if (_reloadRequested) {
          _reloadRequested = false;
          unawaited(load());
        }
      }
    }
  }

  void startRealtime() {
    _events ??= _api.notificationEvents().listen((_) {
      if (!_disposed) unawaited(load());
    });
  }

  Future<void> markRead(CitizenNotification item) async {
    if (item.isRead) return;
    try {
      await _api.markNotificationRead(item.id);
    } catch (_) {
      error = 'Status baca gagal disimpan. Notifikasi tetap dapat dibuka.';
      if (!_disposed) notifyListeners();
    }
    await load();
  }

  Future<void> markAllRead() async {
    if (unreadCount == 0) return;
    try {
      await _api.markAllNotificationsRead();
    } catch (_) {
      error = 'Status baca gagal diperbarui. Coba lagi.';
      if (!_disposed) notifyListeners();
      return;
    }
    await load();
  }

  @override
  void dispose() {
    _disposed = true;
    unawaited(_events?.cancel() ?? Future<void>.value());
    super.dispose();
  }
}
