import 'package:flutter/material.dart';

import '../domain/entities/officer_entities.dart';
import '../state/officer_store.dart';
import '../widgets/officer_widgets.dart';
import '../widgets/officer_page.dart';
import '../tasks/widgets/task_tile.dart';

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
      onRefresh: store.load,
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
              text:
                  'Sistem online. Data tugas akan diperbarui ketika pemerintah mengirimkan penugasan.',
              color: Color(0xFFE2F6ED),
            ),
            const SizedBox(height: 14),
            const OfficerSectionHeader(title: 'Tetap siaga'),
            const SizedBox(height: 8),
            const OfficerInfoCard(
              icon: Icons.notifications_active_outlined,
              text:
                  'Periksa notifikasi secara berkala untuk menerima penugasan baru.',
            ),
          ] else
            TaskTile(
              task: store.tasks.first,
              onTap: onTasks,
            ),
        ],
      ),
    );
  }
}