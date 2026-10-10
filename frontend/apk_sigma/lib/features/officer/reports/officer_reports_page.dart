import 'package:flutter/material.dart';

import '../domain/entities/officer_entities.dart';
import '../state/officer_store.dart';
import '../widgets/officer_page.dart';
import '../widgets/officer_widgets.dart';
import 'create_officer_report_page.dart';

class OfficerReportsPage extends StatelessWidget {
  const OfficerReportsPage({
    super.key,
    required this.store,
  });

  final OfficerStore store;

  @override
  Widget build(BuildContext context) {
    return OfficerPage(
      title: 'Laporan',
      onRefresh: store.load,
      child: store.reports.isEmpty
          ? OfficerEmptyState(
              icon: Icons.description_outlined,
              title: 'Belum ada laporan lapangan',
              description:
                  'Laporan kondisi lapangan yang Anda kirim akan muncul di halaman ini.',
              detail:
                  'Pastikan laporan berisi lokasi, kondisi lapangan, dan bukti pendukung yang relevan.',
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
                    builder: (_) => CreateOfficerReportPage(
                      store: store,
                      task: task,
                    ),
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