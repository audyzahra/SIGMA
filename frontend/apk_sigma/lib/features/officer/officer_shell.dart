import 'package:flutter/material.dart';

import 'dashboard/officer_dashboard.dart';
import 'map/officer_map_page.dart';
import 'profile/officer_profile_page.dart';
import 'reports/officer_reports_page.dart';
import 'state/officer_store.dart';
import 'tasks/officer_tasks_page.dart';
import 'widgets/officer_widgets.dart';

class OfficerShell extends StatefulWidget {
  const OfficerShell({
    super.key,
    required this.name,
  });

  final String name;

  @override
  State<OfficerShell> createState() => _OfficerShellState();
}

class _OfficerShellState extends State<OfficerShell> {
  final OfficerStore store = OfficerStore();

  int tab = 0;

  @override
  void initState() {
    super.initState();
    store.load();
  }

  @override
  void dispose() {
    store.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: store,
      builder: (context, _) {
        return Scaffold(
          body: SafeArea(
            child: store.loading && !store.loaded
                ? const Center(
                    child: CircularProgressIndicator(),
                  )
                : store.errorMessage != null
                    ? _LoadError(
                        message: store.errorMessage!,
                        onRetry: store.load,
                      )
                    : _pages()[tab],
          ),
          bottomNavigationBar: NavigationBar(
            selectedIndex: tab,
            onDestinationSelected: (value) {
              setState(() {
                tab = value;
              });
            },
            destinations: const [
              NavigationDestination(
                icon: Icon(Icons.home_outlined),
                selectedIcon: Icon(Icons.home),
                label: 'Beranda',
              ),
              NavigationDestination(
                icon: Icon(Icons.map_outlined),
                selectedIcon: Icon(Icons.map),
                label: 'Peta',
              ),
              NavigationDestination(
                icon: Icon(Icons.assignment_outlined),
                selectedIcon: Icon(Icons.assignment),
                label: 'Tugas',
              ),
              NavigationDestination(
                icon: Icon(Icons.description_outlined),
                selectedIcon: Icon(Icons.description),
                label: 'Laporan',
              ),
              NavigationDestination(
                icon: Icon(Icons.person_outline),
                selectedIcon: Icon(Icons.person),
                label: 'Profil',
              ),
            ],
          ),
        );
      },
    );
  }

  List<Widget> _pages() {
    return [
      OfficerDashboard(
        store: store,
        name: widget.name,
        onTasks: () {
          setState(() {
            tab = 2;
          });
        },
      ),
      OfficerMapPage(
        store: store,
      ),
      OfficerTasksPage(
        store: store,
      ),
      OfficerReportsPage(
        store: store,
      ),
      OfficerProfilePage(
        store: store,
        name: widget.name,
      ),
    ];
  }
}

class _LoadError extends StatelessWidget {
  const _LoadError({
    required this.message,
    required this.onRetry,
  });

  final String message;
  final Future<void> Function() onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: SigmaCard(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.cloud_off_outlined,
                size: 42,
                color: sigmaOrange,
              ),
              const SizedBox(height: 12),
              Text(
                message,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              SigmaButton(
                label: 'Coba lagi',
                onPressed: onRetry,
              ),
            ],
          ),
        ),
      ),
    );
  }
}