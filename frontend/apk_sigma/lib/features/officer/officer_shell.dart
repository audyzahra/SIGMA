import 'package:flutter/material.dart';

import 'domain/entities/officer_entities.dart';
import 'state/officer_store.dart';
import 'widgets/officer_widgets.dart';

class OfficerShell extends StatefulWidget {
  const OfficerShell({super.key, required this.name});

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
                ? const Center(child: CircularProgressIndicator())
                : store.errorMessage != null
                ? _LoadError(message: store.errorMessage!, onRetry: store.load)
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
      OfficerMapPage(store: store),
      OfficerTasksPage(store: store),
      OfficerReportsPage(store: store),
      OfficerProfilePage(store: store, name: widget.name),
    ];
  }
}

class OfficerPage extends StatelessWidget {
  const OfficerPage({super.key, required this.title, required this.child});

  final String title;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: () {
        final shell = context.findAncestorStateOfType<_OfficerShellState>();

        return shell?.store.load() ?? Future<void>.value();
      },
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 14, 16, 28),
        children: [
          SigmaHeader(title: title),
          const SizedBox(height: 18),
          child,
        ],
      ),
    );
  }
}

class OfficerDashboard extends StatelessWidget {
  const OfficerDashboard({
    super.key,
    required this.store,
    required this.name,
    required this.onTasks,
  });

  final OfficerStore store;
  final String name;
  final VoidCallback onTasks;

  @override
  Widget build(BuildContext context) {
    final activeTasks = store.tasks
        .where((task) => task.status != TaskStatus.completed)
        .length;

    final incidentIds = store.tasks.map((task) => task.incident.id).toSet();

    return OfficerPage(
      title: 'Beranda',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Halo, $name',
            style: const TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w800,
              color: sigmaNavy,
            ),
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: Metric(
                  label: 'Tugas aktif',
                  value: '$activeTasks',
                  icon: Icons.assignment_outlined,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Metric(
                  label: 'Insiden',
                  value: '${incidentIds.length}',
                  icon: Icons.warning_amber_outlined,
                  color: sigmaOrange,
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          const OfficerSectionHeader(title: 'Tugas prioritas'),
          const SizedBox(height: 8),
          if (store.tasks.isEmpty) ...[
            const OfficerEmptyState(
              icon: Icons.assignment_late_outlined,
              title: 'Belum ada penugasan aktif',
              description:
                  'Belum ada tugas yang ditugaskan kepada tim Anda saat ini.',
            ),
            const OfficerSectionHeader(title: 'Status operasional'),
            const SizedBox(height: 8),
            const OfficerInfoCard(
              icon: Icons.check_circle_outline,
              text: 'Sistem online. Data tugas akan diperbarui ketika pemerintah mengirimkan penugasan.',
              color: Color(0xFFE2F6ED),
            ),
            const SizedBox(height: 14),
            const OfficerSectionHeader(title: 'Tetap siaga'),
            const SizedBox(height: 8),
            const OfficerInfoCard(
              icon: Icons.notifications_active_outlined,
              text: 'Periksa notifikasi secara berkala untuk menerima penugasan baru.',
            ),
          ] else
            TaskTile(task: store.tasks.first, onTap: onTasks),
        ],
      ),
    );
  }
}

class OfficerTasksPage extends StatefulWidget {
  const OfficerTasksPage({super.key, required this.store});

  final OfficerStore store;

  @override
  State<OfficerTasksPage> createState() => _OfficerTasksPageState();
}

class _OfficerTasksPageState extends State<OfficerTasksPage> {
  int _filter = 0;

  @override
  Widget build(BuildContext context) {
    final tasks = widget.store.tasks.where((task) {
      if (_filter == 1) {
        return task.status != TaskStatus.completed &&
            task.status != TaskStatus.cancelled;
      }
      return _filter != 2 || task.status == TaskStatus.completed;
    }).toList();

    return OfficerPage(
      title: 'Tugas',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SegmentedButton<int>(
            segments: const [
              ButtonSegment(value: 0, label: Text('Semua')),
              ButtonSegment(value: 1, label: Text('Aktif')),
              ButtonSegment(value: 2, label: Text('Selesai')),
            ],
            selected: {_filter},
            onSelectionChanged: (value) =>
                setState(() => _filter = value.first),
          ),
          if (tasks.isEmpty)
            OfficerEmptyState(
              icon: Icons.assignment_outlined,
              title: _filter == 0
                  ? 'Tidak ada tugas aktif'
                  : 'Belum ada tugas pada filter ini',
              description: 'Tugas baru akan muncul setelah pemerintah mengirimkan penugasan melalui sistem SIGMA.',
              detail: 'Gunakan tombol muat ulang untuk mengambil data tugas terbaru.',
              actionLabel: 'Muat ulang',
              onAction: widget.store.load,
            )
          else
            ...tasks.map((task) {
              return TaskTile(
                task: task,
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) =>
                          TaskDetailPage(store: widget.store, task: task),
                    ),
                  );
                },
              );
            }),
        ],
      ),
    );
  }
}

class TaskTile extends StatelessWidget {
  const TaskTile({super.key, required this.task, required this.onTap});

  final OfficerTask task;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final incident = task.incident;
    final status = task.status;

    return SigmaCard(
      child: ListTile(
        onTap: onTap,
        contentPadding: EdgeInsets.zero,
        leading: const Icon(
          Icons.local_fire_department_outlined,
          color: sigmaRed,
        ),
        title: Text(incident.location),
        subtitle: Text([statusLabel(status), incident.priority].join(' · ')),
        trailing: const Icon(Icons.chevron_right),
      ),
    );
  }
}

class TaskDetailPage extends StatefulWidget {
  const TaskDetailPage({super.key, required this.store, required this.task});

  final OfficerStore store;
  final OfficerTask task;

  @override
  State<TaskDetailPage> createState() => _TaskDetailPageState();
}

class _TaskDetailPageState extends State<TaskDetailPage> {
  bool _updating = false;

  OfficerTask get currentTask {
    return widget.store.tasks.firstWhere(
      (item) => item.id == widget.task.id,
      orElse: () => widget.task,
    );
  }

  Future<void> _updateStatus(TaskStatus status) async {
    if (_updating) return;

    setState(() {
      _updating = true;
    });

    await widget.store.setTaskStatus(widget.task.id, status);

    if (!mounted) return;

    setState(() {
      _updating = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    // final task = currentTask;
    // final incident = task.incident;
    // final status = task.status;

    return AnimatedBuilder(
      animation: widget.store,
      builder: (context, _) {
        final updatedTask = currentTask;
        final updatedStatus = updatedTask.status;

        return Scaffold(
          body: SafeArea(
            child: OfficerPage(
              title: 'Detail Tugas',
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  /*
                  |--------------------------------------------------------------------------
                  | INFORMASI TUGAS
                  |--------------------------------------------------------------------------
                  */

                  SigmaCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          updatedTask.incident.location,
                          style: const TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 18,
                          ),
                        ),

                        const SizedBox(height: 4),

                        Text(
                          '${updatedTask.incident.latitude}, '
                          '${updatedTask.incident.longitude}',
                        ),

                        const SizedBox(height: 8),

                        SigmaChip(
                          text: statusLabel(updatedStatus),
                          color: statusColor(updatedStatus),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  /*
                  |--------------------------------------------------------------------------
                  | PROGRESS TINDAKAN
                  |--------------------------------------------------------------------------
                  */
                  const Text(
                    'Proses Penanganan',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w800,
                      color: sigmaNavy,
                    ),
                  ),

                  const SizedBox(height: 10),

                  _TaskProgressActions(
                    status: updatedStatus,
                    loading: _updating,
                    onStatusSelected: _updateStatus,
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}

class _TaskProgressActions extends StatelessWidget {
  const _TaskProgressActions({
    required this.status,
    required this.loading,
    required this.onStatusSelected,
  });

  final TaskStatus status;
  final bool loading;
  final Future<void> Function(TaskStatus status) onStatusSelected;

  @override
  Widget build(BuildContext context) {
    final steps = <TaskStatus>[
      TaskStatus.accepted,
      TaskStatus.enRoute,
      TaskStatus.arrived,
      TaskStatus.handling,
      TaskStatus.completed,
    ];

    final currentIndex = _currentIndex(status);

    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: steps.asMap().entries.map((entry) {
        final index = entry.key;
        final step = entry.value;

        /*
        |--------------------------------------------------------------------------
        | BELUM SAMPAI
        |--------------------------------------------------------------------------
        */

        if (index > currentIndex + 1) {
          return _TaskStepButton(
            label: actionLabel(step),
            icon: Icons.lock_outline,
            enabled: false,
            completed: false,
            loading: false,
            onPressed: null,
          );
        }

        /*
        |--------------------------------------------------------------------------
        | SUDAH SELESAI
        |--------------------------------------------------------------------------
        */

        if (index <= currentIndex) {
          return _TaskStepButton(
            label: actionLabel(step),
            icon: Icons.check,
            enabled: false,
            completed: true,
            loading: false,
            onPressed: null,
          );
        }

        /*
        |--------------------------------------------------------------------------
        | AKSI BERIKUTNYA
        |--------------------------------------------------------------------------
        */

        return _TaskStepButton(
          label: actionLabel(step),
          icon: _actionIcon(step),
          enabled: !loading,
          completed: false,
          loading: loading,
          onPressed: () {
            onStatusSelected(step);
          },
        );
      }).toList(),
    );
  }

  int _currentIndex(TaskStatus status) {
    return switch (status) {
      TaskStatus.assigned => -1,
      TaskStatus.accepted => 0,
      TaskStatus.enRoute => 1,
      TaskStatus.arrived => 2,
      TaskStatus.handling => 3,
      TaskStatus.completed => 4,
      _ => -1,
    };
  }

  IconData _actionIcon(TaskStatus status) {
    return switch (status) {
      TaskStatus.accepted => Icons.check_circle_outline,
      TaskStatus.enRoute => Icons.directions_car_outlined,
      TaskStatus.arrived => Icons.location_on_outlined,
      TaskStatus.handling => Icons.local_fire_department_outlined,
      TaskStatus.completed => Icons.done_all,
      _ => Icons.arrow_forward,
    };
  }
}

class _TaskStepButton extends StatelessWidget {
  const _TaskStepButton({
    required this.label,
    required this.icon,
    required this.enabled,
    required this.completed,
    required this.loading,
    required this.onPressed,
  });

  final String label;
  final IconData icon;
  final bool enabled;
  final bool completed;
  final bool loading;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    /*
    |--------------------------------------------------------------------------
    | SUDAH SELESAI
    |--------------------------------------------------------------------------
    */

    if (completed) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
        decoration: BoxDecoration(
          color: const Color(0xFFE8F5E9),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: const Color(0xFFA5D6A7)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.check_circle, size: 17, color: sigmaGreen),
            const SizedBox(width: 7),
            Text(
              label,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: sigmaGreen,
              ),
            ),
          ],
        ),
      );
    }

    /*
    |--------------------------------------------------------------------------
    | BELUM AKTIF
    |--------------------------------------------------------------------------
    */

    if (!enabled) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
        decoration: BoxDecoration(
          color: const Color(0xFFF1F3F5),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 17, color: Colors.grey.shade500),
            const SizedBox(width: 7),
            Text(
              label,
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: Colors.grey.shade500,
              ),
            ),
          ],
        ),
      );
    }

    /*
    |--------------------------------------------------------------------------
    | AKSI BERIKUTNYA
    |--------------------------------------------------------------------------
    */

    return ElevatedButton.icon(
      onPressed: loading ? null : onPressed,
      icon: loading
          ? const SizedBox(
              width: 15,
              height: 15,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                color: Colors.white,
              ),
            )
          : Icon(icon, size: 17),
      label: Text(loading ? 'Memproses...' : label),
      style: ElevatedButton.styleFrom(
        backgroundColor: sigmaRed,
        foregroundColor: Colors.white,
        disabledBackgroundColor: sigmaRed.withValues(alpha: 0.6),
        disabledForegroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 11),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        elevation: 0,
      ),
    );
  }
}

class OfficerMapPage extends StatelessWidget {
  const OfficerMapPage({super.key, required this.store});

  final OfficerStore store;

  @override
  Widget build(BuildContext context) {
    if (store.tasks.isEmpty) {
      return OfficerPage(
        title: 'Peta',
        child: OfficerEmptyState(
          icon: Icons.map_outlined,
          title: 'Belum ada titik pemantauan',
          description: 'Belum tersedia hotspot, insiden, atau tugas yang dapat ditampilkan pada peta.',
          detail: 'Sumber data: tugas lapangan, insiden, dan hotspot. Data akan muncul otomatis ketika tersedia.',
          actionLabel: 'Muat ulang',
          onAction: store.load,
        ),
      );
    }

    return OfficerPage(
      title: 'Peta',
      child: Column(
        children: store.tasks.map((task) {
          final incident = task.incident;

          return SigmaCard(
            child: ListTile(
              leading: const Icon(Icons.location_on_outlined, color: sigmaRed),
              title: Text(incident.location),
              subtitle: Text('${incident.latitude}, ${incident.longitude}'),
            ),
          );
        }).toList(),
      ),
    );
  }
}

class OfficerReportsPage extends StatelessWidget {
  const OfficerReportsPage({super.key, required this.store});

  final OfficerStore store;

  @override
  Widget build(BuildContext context) {
    return OfficerPage(
      title: 'Laporan',
      child: store.reports.isEmpty
          ? OfficerEmptyState(
              icon: Icons.description_outlined,
              title: 'Belum ada laporan lapangan',
              description: 'Laporan kondisi lapangan yang Anda kirim akan muncul di halaman ini.',
              detail: 'Pastikan laporan berisi lokasi, kondisi lapangan, dan bukti pendukung yang relevan.',
              actionLabel: 'Buat laporan',
              onAction: () {
                final taskIndex = store.tasks.indexWhere(
                  (item) =>
                      item.status != TaskStatus.completed &&
                      item.status != TaskStatus.cancelled,
                );
                if (taskIndex < 0) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text(
                        'Laporan dapat dibuat setelah Anda menerima tugas aktif.',
                      ),
                    ),
                  );
                  return;
                }
                final task = store.tasks[taskIndex];
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) =>
                        CreateOfficerReportPage(store: store, task: task),
                  ),
                );
              },
            )
          : Column(
              children: store.reports.map((report) {
                return SigmaCard(
                  child: ListTile(
                    contentPadding: EdgeInsets.zero,
                    title: Text(report.title),
                    subtitle: Text(report.createdAt.toString()),
                  ),
                );
              }).toList(),
            ),
    );
  }
}

class CreateOfficerReportPage extends StatefulWidget {
  const CreateOfficerReportPage({
    super.key,
    required this.store,
    required this.task,
  });

  final OfficerStore store;
  final OfficerTask task;

  @override
  State<CreateOfficerReportPage> createState() =>
      _CreateOfficerReportPageState();
}

class _CreateOfficerReportPageState extends State<CreateOfficerReportPage> {
  final _description = TextEditingController();
  bool _submitting = false;

  @override
  void dispose() {
    _description.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final description = _description.text.trim();
    if (description.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Jelaskan kondisi lapangan terlebih dahulu.'),
        ),
      );
      return;
    }
    setState(() => _submitting = true);
    final sent = await widget.store.createReport(widget.task.id, description);
    if (!mounted) return;
    setState(() => _submitting = false);
    if (sent) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Laporan lapangan berhasil dikirim.')),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Laporan belum dapat dikirim. Coba lagi.'),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    body: SafeArea(
      child: OfficerPage(
        title: 'Buat Laporan',
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            OfficerInfoCard(
              icon: Icons.location_on_outlined,
              text: widget.task.incident.location,
            ),
            const SizedBox(height: 16),
            const Text(
              'Kondisi lapangan',
              style: TextStyle(fontWeight: FontWeight.w800, color: sigmaNavy),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _description,
              minLines: 5,
              maxLines: 8,
              maxLength: 5000,
              decoration: const InputDecoration(
                hintText: 'Tuliskan kondisi, tindakan, dan bukti pendukung yang relevan.',
              ),
            ),
            const SizedBox(height: 12),
            SigmaButton(
              label: _submitting ? 'Mengirim...' : 'Kirim Laporan',
              icon: Icons.send_outlined,
              onPressed: _submitting ? () {} : _submit,
            ),
          ],
        ),
      ),
    ),
  );
}

class OfficerProfilePage extends StatelessWidget {
  const OfficerProfilePage({
    super.key,
    required this.store,
    required this.name,
  });

  final OfficerStore store;
  final String name;

  @override
  Widget build(BuildContext context) {
    return OfficerPage(
      title: 'Profil',
      child: Column(
        children: [
          SigmaCard(
            child: Column(
              children: [
                const CircleAvatar(
                  radius: 32,
                  child: Icon(Icons.person_outline, size: 34),
                ),
                const SizedBox(height: 10),
                Text(
                  store.profile?.name ?? name,
                  style: const TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 20,
                  ),
                ),
                const SizedBox(height: 4),
                Text(store.profile?.role ?? 'Petugas'),
                const SizedBox(height: 10),
                OfficerInfoCard(
                  icon: Icons.groups_outlined,
                  text: store.profile?.team ?? 'Tim belum tersedia',
                ),
                const SizedBox(height: 8),
                OfficerInfoCard(
                  icon: Icons.business_outlined,
                  text:
                      store.profile?.organization ??
                      'Organisasi belum tersedia',
                ),
                const SizedBox(height: 8),
                OfficerInfoCard(
                  icon: Icons.circle,
                  text: store.profile?.online == true
                      ? 'Status online'
                      : 'Status belum tersedia',
                  color: store.profile?.online == true
                      ? const Color(0xFFE2F6ED)
                      : sigmaBlue,
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          ...[
            'Data Saya',
            'Organisasi',
            'Sinkronisasi Data',
            'Pengaturan',
            'Notifikasi',
            'Keamanan',
            'Bantuan & SOP',
          ].map(
            (label) => Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: SigmaCard(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 2,
                ),
                child: ListTile(
                  title: Text(label),
                  trailing: const Icon(Icons.chevron_right),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

String statusLabel(TaskStatus status) {
  return switch (status) {
    TaskStatus.assigned => 'Ditugaskan',
    TaskStatus.accepted => 'Diterima',
    TaskStatus.enRoute => 'Dalam perjalanan',
    TaskStatus.arrived => 'Tiba di lokasi',
    TaskStatus.handling => 'Penanganan',
    TaskStatus.extinguishing => 'Api padam',
    TaskStatus.cooling => 'Pendinginan',
    TaskStatus.completed => 'Selesai',
    TaskStatus.cancelled => 'Dibatalkan',
  };
}

TaskStatus? nextStatus(TaskStatus status) {
  return switch (status) {
    TaskStatus.assigned => TaskStatus.accepted,
    TaskStatus.accepted => TaskStatus.enRoute,
    TaskStatus.enRoute => TaskStatus.arrived,
    TaskStatus.arrived => TaskStatus.handling,
    TaskStatus.handling => TaskStatus.completed,
    _ => null,
  };
}

String actionLabel(TaskStatus status) {
  return statusLabel(status);
}

Color statusColor(TaskStatus status) {
  return switch (status) {
    TaskStatus.completed => sigmaGreen,
    TaskStatus.handling => sigmaRed,
    TaskStatus.arrived => sigmaBlue,
    TaskStatus.enRoute => sigmaOrange,
    TaskStatus.accepted => sigmaBlue,
    _ => sigmaOrange,
  };
}

class _LoadError extends StatelessWidget {
  const _LoadError({required this.message, required this.onRetry});

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
              Text(message, textAlign: TextAlign.center),
              const SizedBox(height: 16),
              SigmaButton(label: 'Coba lagi', onPressed: onRetry),
            ],
          ),
        ),
      ),
    );
  }
}
