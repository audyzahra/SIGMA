import 'package:flutter/material.dart';

import '../../../../core/theme/sigma_theme.dart';
import '../../../../models/citizen_models.dart';
import '../../../../services/citizen_api.dart';
import '../../widgets/citizen_widgets.dart';

class CitizenMapPage extends StatefulWidget {
  const CitizenMapPage({
    super.key,
    required this.api,
    required this.onCreateReport,
  });
  final CitizenApi api;
  final VoidCallback onCreateReport;
  @override
  State<CitizenMapPage> createState() => _CitizenMapPageState();
}

class _CitizenMapPageState extends State<CitizenMapPage> {
  late Future<Map<String, dynamic>> _map = widget.api.map();
  @override
  Widget build(BuildContext context) => FutureBuilder<Map<String, dynamic>>(
    future: _map,
    builder: (context, snapshot) {
      if (snapshot.connectionState != ConnectionState.done)
        return const PageLoading();
      if (snapshot.hasError)
        return PageError(
          onRetry: () => setState(() => _map = widget.api.map()),
        );
      final data = snapshot.data!;
      final items = <MapItem>[
        ...(data['hotspots'] as List<dynamic>).map(
          (item) => MapItem.hotspot(item as Map<String, dynamic>),
        ),
        ...(data['incidents'] as List<dynamic>).map(
          (item) => MapItem.incident(item as Map<String, dynamic>),
        ),
        ...(data['reports'] as List<dynamic>).map(
          (item) => MapItem.report(item as Map<String, dynamic>),
        ),
      ];
      return Column(
        children: [
          const Padding(
            padding: EdgeInsets.fromLTRB(20, 14, 20, 10),
            child: CitizenHeader(title: 'Peta pantau'),
          ),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: CitizenSurface(
                child: items.isEmpty
                    ? const EmptyState(
                        message: 'Belum ada data geografis untuk ditampilkan.',
                      )
                    : Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Data pemantauan',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'Titik dinamis dari hotspot, insiden, dan laporan Anda.',
                          ),
                          const SizedBox(height: 14),
                          Expanded(
                            child: ListView.separated(
                              itemCount: items.length,
                              separatorBuilder: (_, _) => const Divider(),
                              itemBuilder: (_, index) {
                                final item = items[index];
                                final hotspot = item.id.startsWith('hotspot');
                                final report = item.id.startsWith('report');
                                return ListTile(
                                  contentPadding: EdgeInsets.zero,
                                  leading: Icon(
                                    hotspot
                                        ? Icons.local_fire_department
                                        : report
                                        ? Icons.assignment
                                        : Icons.warning_amber_rounded,
                                    color: hotspot
                                        ? SigmaColors.primary
                                        : report
                                        ? SigmaColors.info
                                        : SigmaColors.secondary,
                                  ),
                                  title: Text(item.label),
                                  subtitle: Text(
                                    '${item.latitude.toStringAsFixed(5)}, ${item.longitude.toStringAsFixed(5)}',
                                  ),
                                  trailing: StatusBadge(
                                    label: statusLabel(item.state),
                                    color: statusColor(item.state),
                                  ),
                                );
                              },
                            ),
                          ),
                        ],
                      ),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
            child: SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: widget.onCreateReport,
                icon: const Icon(Icons.add_location_alt_outlined),
                label: const Text('Buat laporan'),
              ),
            ),
          ),
        ],
      );
    },
  );
}
