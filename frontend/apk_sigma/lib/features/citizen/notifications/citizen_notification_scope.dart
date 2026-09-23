import 'package:flutter/material.dart';

import '../data/datasources/citizen_remote_data_source.dart';
import 'citizen_notification_store.dart';

class CitizenNotificationScope extends InheritedNotifier<CitizenNotificationStore> {
  const CitizenNotificationScope({
    super.key,
    required CitizenNotificationStore store,
    required this.api,
    required super.child,
  }) : super(notifier: store);
  final CitizenApi api;
  static CitizenNotificationScope? maybeOf(BuildContext context) =>
      context.dependOnInheritedWidgetOfExactType<CitizenNotificationScope>();
}
