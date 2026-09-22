import 'package:flutter/foundation.dart';

import '../../../models/citizen_models.dart';
import '../../../services/citizen_api.dart';

/// Shared notification state. It always reads the Laravel database; no local
/// notification list or hard-coded badge count is maintained.
class CitizenNotificationStore extends ChangeNotifier {
  CitizenNotificationStore(this._api);
  final CitizenApi _api;
  List<CitizenNotification> items = const [];
  bool loading = true;
  bool _disposed = false;
  int get unreadCount => items.where((item) => !item.isRead).length;

  Future<void> load() async {
    loading = true;
    notifyListeners();
    try {
      items = await _api.notifications();
    } finally {
      loading = false;
      notifyListeners();
    }
  }

  void startRealtime() {
    _api.notificationEvents().listen((_) {
      if (!_disposed) load();
    });
  }

  Future<void> markRead(CitizenNotification item) async {
    if (item.isRead) return;
    await _api.markNotificationRead(item.id);
    await load();
  }

  Future<void> markAllRead() async {
    if (unreadCount == 0) return;
    await _api.markAllNotificationsRead();
    await load();
  }

  @override
  void dispose() {
    _disposed = true;
    super.dispose();
  }
}
