import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';

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
    _map = widget.api.map();
  }

  Future<void> _refreshMap() async {
    final request = widget.api.map();
    setState(() => _map = request);

    try {
      await request;
    } catch (_) {}
  }

  List<MapItem> _parseItems(Map<String, dynamic> data) {
    final items = <MapItem>[];

    void addItems(String key, MapItem Function(Map<String, dynamic>) parser) {
      final value = data[key];
      if (value is! List) return;

      for (final entry in value) {
        if (entry is! Map) continue;

        try {
          final item = parser(Map<String, dynamic>.from(entry));

          if (item.latitude.isFinite &&
              item.longitude.isFinite &&
              item.latitude >= -90 &&
              item.latitude <= 90 &&
              item.longitude >= -180 &&
              item.longitude <= 180) {
            items.add(item);
          }
        } catch (error) {
          debugPrint('Gagal memproses $key: $error');
        }
      }
    }

    addItems('hotspots', MapItem.hotspot);
    addItems('incidents', MapItem.incident);
    addItems('reports', MapItem.report);

    return items;
  }

  bool _isHotspot(MapItem item) => item.id.startsWith('hotspot');

  bool _isReport(MapItem item) => item.id.startsWith('report');

  Color _itemColor(MapItem item) {
    if (_isHotspot(item)) return SigmaColors.primary;
    if (_isReport(item)) return SigmaColors.info;
    return SigmaColors.secondary;
  }

  IconData _itemIcon(MapItem item) {
    if (_isHotspot(item)) {
      return Icons.local_fire_department;
    }
    if (_isReport(item)) {
      return Icons.assignment;
    }
    return Icons.warning_amber_rounded;
  }

  Widget _buildMap(List<MapItem> items) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(12),
      child: FlutterMap(
        options: const MapOptions(
          initialCenter: LatLng(-2.5, 118),
          initialZoom: 5,
          minZoom: 3,
          maxZoom: 18,
        ),
        children: [
          TileLayer(
            urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            userAgentPackageName: 'com.sigma.apk_sigma',
          ),
          MarkerLayer(
            markers: items.map((item) {
              return Marker(
                point: LatLng(item.latitude, item.longitude),
                width: 34,
                height: 40,
                child: Tooltip(
                  message:
                      '${item.label}\n'
                      '${item.latitude.toStringAsFixed(5)}, '
                      '${item.longitude.toStringAsFixed(5)}',
                  child: Icon(
                    _itemIcon(item),
                    color: _itemColor(item),
                    size: 29,
                  ),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _legendItem(IconData icon, Color color, String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, color: color, size: 16),
        const SizedBox(width: 4),
        Text(label, style: const TextStyle(fontSize: 12)),
      ],
    );
  }

  Widget _buildLegend() {
    return Wrap(
      spacing: 12,
      runSpacing: 6,
      children: [
        _legendItem(
          Icons.local_fire_department,
          SigmaColors.primary,
          'Hotspot',
        ),
        _legendItem(
          Icons.warning_amber_rounded,
          SigmaColors.secondary,
          'Insiden',
        ),
        _legendItem(Icons.assignment, SigmaColors.info, 'Laporan'),
      ],
    );
  }

  Widget _buildMonitoringList(List<MapItem> items) {
    if (items.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(12),
          child: Text(
            'Belum ada data pemantauan.',
            textAlign: TextAlign.center,
          ),
        ),
      );
    }

    return ListView.separated(
      padding: EdgeInsets.zero,
      physics: const AlwaysScrollableScrollPhysics(),
      itemCount: items.length,
      separatorBuilder: (_, _) => const Divider(height: 1),
      itemBuilder: (context, index) {
        final item = items[index];

        return ListTile(
          dense: true,
          visualDensity: const VisualDensity(vertical: -2),
          contentPadding: EdgeInsets.zero,
          leading: Icon(_itemIcon(item), color: _itemColor(item)),
          title: Text(item.label),
          subtitle: Text(
            '${item.latitude.toStringAsFixed(5)}, '
            '${item.longitude.toStringAsFixed(5)}',
          ),
          trailing: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 100),
            child: StatusBadge(
              label: statusLabel(item.state),
              color: statusColor(item.state),
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: FutureBuilder<Map<String, dynamic>>(
        future: _map,
        builder: (context, snapshot) {
          if (snapshot.connectionState != ConnectionState.done) {
            return const PageLoading();
          }

          if (snapshot.hasError || !snapshot.hasData) {
            return PageError(onRetry: _refreshMap);
          }

          final items = _parseItems(snapshot.data!);

          return Column(
            children: [
              const Padding(
                padding: EdgeInsets.fromLTRB(20, 8, 20, 8),
                child: CitizenHeader(title: 'Peta pantau'),
              ),

              Expanded(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(14, 4, 14, 8),
                  child: CitizenSurface(
                    padding: const EdgeInsets.all(12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Peta pemantauan',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        const SizedBox(height: 8),

                        // Tinggi peta tetap agar tidak memenuhi kartu.
                        SizedBox(
                          height: 200,
                          width: double.infinity,
                          child: _buildMap(items),
                        ),

                        const SizedBox(height: 8),
                        _buildLegend(),
                        const SizedBox(height: 12),

                        const Text(
                          'Data pemantauan',
                          style: TextStyle(
                            fontSize: 17,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'Hotspot, insiden, dan laporan masyarakat SIGMA.',
                          style: TextStyle(fontSize: 12),
                        ),
                        const SizedBox(height: 6),

                        // Daftar mendapat sisa ruang yang tersedia.
                        Expanded(child: _buildMonitoringList(items)),
                      ],
                    ),
                  ),
                ),
              ),

              Padding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
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
      ),
    );
  }
}
