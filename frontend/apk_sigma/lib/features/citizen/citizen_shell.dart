import 'package:flutter/material.dart';

import 'data/datasources/citizen_remote_data_source.dart';
import 'dashboard/pages/citizen_dashboard_page.dart';
import 'map/pages/citizen_map_page.dart';
import 'notifications/citizen_notification_scope.dart';
import 'notifications/citizen_notification_store.dart';
import 'profile/pages/profile_page.dart';
import 'reports/pages/report_create_page.dart';
import 'reports/pages/report_index_page.dart';

class CitizenShell extends StatefulWidget {
  const CitizenShell({super.key, required this.name});
  final String name;
  @override
  State<CitizenShell> createState() => _CitizenShellState();
}

class _CitizenShellState extends State<CitizenShell> {
  final _api = CitizenApi();
  int _index = 0;
  late final CitizenNotificationStore _notifications =
      CitizenNotificationStore(_api)..load()..startRealtime();

  @override
  void dispose() {
    _notifications.dispose();
    super.dispose();
  }

  Future<void> _createReport() async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => ReportCreatePage(api: _api)),
    );
    if (mounted) setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final pages = [
      CitizenDashboardPage(
        api: _api,
        onCreateReport: _createReport,
        onOpenMap: () => setState(() => _index = 2),
      ),
      ReportIndexPage(api: _api),
      CitizenMapPage(api: _api, onCreateReport: _createReport),
      ProfilePage(api: _api),
    ];
    return CitizenNotificationScope(
      store: _notifications,
      api: _api,
      child: Scaffold(
        body: SafeArea(child: pages[_index]),
        bottomNavigationBar: NavigationBarTheme(
          data: NavigationBarThemeData(
            height: 70,
            indicatorColor: const Color(0x1FC82828),
            labelTextStyle: WidgetStateProperty.resolveWith(
              (states) => TextStyle(
                fontSize: 12,
                fontWeight: states.contains(WidgetState.selected)
                    ? FontWeight.w800
                    : FontWeight.w600,
              ),
            ),
          ),
          child: NavigationBar(
            selectedIndex: _index,
            onDestinationSelected: (value) => setState(() => _index = value),
            destinations: const [
              NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home), label: 'Beranda'),
              NavigationDestination(icon: Icon(Icons.assignment_outlined), selectedIcon: Icon(Icons.assignment), label: 'Laporan'),
              NavigationDestination(icon: Icon(Icons.map_outlined), selectedIcon: Icon(Icons.map), label: 'Peta'),
              NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person), label: 'Profil'),
            ],
          ),
        ),
      ),
    );
  }
}
