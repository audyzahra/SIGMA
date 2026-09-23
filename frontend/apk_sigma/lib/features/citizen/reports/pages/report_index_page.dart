import 'package:flutter/material.dart';

import '../../data/models/citizen_models.dart';
import '../../data/datasources/citizen_remote_data_source.dart';
import '../../widgets/citizen_widgets.dart';
import '../widgets/report_card.dart';
import 'report_detail_page.dart';

class ReportIndexPage extends StatefulWidget {
  const ReportIndexPage({super.key, required this.api});
  final CitizenApi api;
  @override
  State<ReportIndexPage> createState() => _ReportIndexPageState();
}

class _ReportIndexPageState extends State<ReportIndexPage> {
  String _filter = 'all';
  late Future<Map<String, dynamic>> _reports = widget.api.reports();
  @override
  Widget build(BuildContext context) => FutureBuilder<Map<String, dynamic>>(
    future: _reports,
    builder: (context, snapshot) {
      if (snapshot.connectionState != ConnectionState.done)
        return const PageLoading();
      if (snapshot.hasError)
        return PageError(
          onRetry: () => setState(() => _reports = widget.api.reports()),
        );
      final reports = (snapshot.data!['reports'] as List<dynamic>)
          .map((item) => CitizenReport.fromJson(item as Map<String, dynamic>))
          .where((report) => _filter == 'all' || report.status == _filter)
          .toList();
      return ListView(
        padding: const EdgeInsets.all(20),
        children: [
          const CitizenHeader(title: 'Laporan'),
          const SizedBox(height: 16),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children:
                  [
                        'all',
                        'submitted',
                        'verified',
                        'process',
                        'completed',
                        'rejected',
                      ]
                      .map(
                        (item) => Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: ChoiceChip(
                            label: Text(
                              item == 'all' ? 'Semua' : statusLabel(item),
                            ),
                            selected: _filter == item,
                            onSelected: (_) => setState(() => _filter = item),
                          ),
                        ),
                      )
                      .toList(),
            ),
          ),
          const SizedBox(height: 14),
          if (reports.isEmpty)
            const EmptyState(message: 'Belum ada laporan pada status ini.')
          else
            ...reports.map(
              (report) => ReportCard(
                report: report,
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) =>
                        ReportDetailPage(api: widget.api, id: report.id),
                  ),
                ),
              ),
            ),
        ],
      );
    },
  );
}
