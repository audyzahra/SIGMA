import 'package:flutter/material.dart';

import '../../../../core/theme/sigma_theme.dart';
import '../../data/models/citizen_models.dart';
import '../../data/datasources/citizen_remote_data_source.dart';
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
  late Future<Map<String, dynamic>> _map;

  @override
  void initState() {
    super.initState();
    _map = _loadMap();
  }

  Future<Map<String, dynamic>> _loadMap() {
    // Memuat data aktual melalui API Laravel.
    return widget.api.map();
  }

  void _refreshMap() {
    setState(() {
      _map = _loadMap();
    });
  }

  List<MapItem> _parseItems(Map<String, dynamic> data) {
    final items = <MapItem>[];

    void addItems(String key, MapItem Function(Map<String, dynamic>) parse) {
      final value = data[key];

      if (value is! List) return;

      for (final item in value) {
        if (item is! Map) continue;

        try {
          items.add(
            parse(Map<String, dynamic>.from(item)),
          );
        } catch (error) {
          debugPrint('Gagal memproses data $key: $error');
        }
      }
    }

    addItems('hotspots', MapItem.hotspot);
    addItems('incidents', MapItem.incident);
    addItems('reports', MapItem.report);

    return items;
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<Map<String, dynamic>>(
      future: _map,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const PageLoading();
        }

        if (snapshot.hasError || !snapshot.hasData) {
          return PageError(onRetry: _refreshMap);
        }

        final data = snapshot.data!;
        final items = _parseItems(data);

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
                          message:
                              'Belum ada data geografis dari database.',
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
                              'Titik hotspot, insiden, dan laporan '
                              'dari data SIGMA.',
                            ),
                            const SizedBox(height: 14),
                            Expanded(
                              child: RefreshIndicator(
                                onRefresh: () async {
                                  _refreshMap();
                                  await _map;
                                },
                                child: ListView.separated(
                                  physics:
                                      const AlwaysScrollableScrollPhysics(),
                                  itemCount: items.length,
                                  separatorBuilder: (_, _) =>
                                      const Divider(),
                                  itemBuilder: (_, index) {
                                    final item = items[index];
                                    final hotspot =
                                        item.id.startsWith('hotspot');
                                    final report =
                                        item.id.startsWith('report');

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
                                        '${item.latitude.toStringAsFixed(5)}, '
                                        '${item.longitude.toStringAsFixed(5)}',
                                      ),
                                      trailing: StatusBadge(
                                        label: statusLabel(item.state),
                                        color: statusColor(item.state),
                                      ),
                                    );
                                  },
                                ),
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
}