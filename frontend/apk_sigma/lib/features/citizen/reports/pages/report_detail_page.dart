import 'package:flutter/material.dart';

import '../../../../models/citizen_models.dart';
import '../../../../services/citizen_api.dart';
import '../../widgets/citizen_widgets.dart';

class ReportDetailPage extends StatefulWidget {
  const ReportDetailPage({super.key, required this.api, required this.id});
  final CitizenApi api;
  final String id;
  @override
  State<ReportDetailPage> createState() => _ReportDetailPageState();
}

class _ReportDetailPageState extends State<ReportDetailPage> {
  late Future<CitizenReport> _report = widget.api.report(widget.id);
  @override
  Widget build(BuildContext context) => Scaffold(
    body: SafeArea(
      child: FutureBuilder<CitizenReport>(
        future: _report,
        builder: (context, snapshot) {
          if (snapshot.connectionState != ConnectionState.done)
            return const PageLoading();
          if (snapshot.hasError)
            return PageError(
              onRetry: () =>
                  setState(() => _report = widget.api.report(widget.id)),
            );
          final report = snapshot.data!;
          return ListView(
            padding: const EdgeInsets.all(20),
            children: [
              const CitizenHeader(title: 'Detail laporan', back: true),
              const SizedBox(height: 20),
              CitizenSurface(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      report.number,
                      style: const TextStyle(fontWeight: FontWeight.w800),
                    ),
                    const SizedBox(height: 8),
                    StatusBadge(
                      label: statusLabel(report.status),
                      color: statusColor(report.status),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      reportTypeLabel(report.type),
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      report.description.isEmpty
                          ? 'Tidak ada keterangan tambahan.'
                          : report.description,
                    ),
                    const SizedBox(height: 10),
                    Text('Koordinat: ${report.latitude}, ${report.longitude}'),
                  ],
                ),
              ),
              const Text(
                'Perkembangan',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
              ),
              const SizedBox(height: 8),
              if (report.history.isEmpty)
                const EmptyState(message: 'Belum ada pembaruan.')
              else
                ...report.history.map(
                  (entry) => ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(
                      Icons.check_circle,
                      color: statusColor(entry.status),
                    ),
                    title: Text(statusLabel(entry.status)),
                    subtitle: Text(
                      entry.description.isEmpty
                          ? 'Status diperbarui.'
                          : entry.description,
                    ),
                  ),
                ),
            ],
          );
        },
      ),
    ),
  );
}
