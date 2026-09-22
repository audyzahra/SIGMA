import 'package:flutter/material.dart';

import '../../../../core/theme/sigma_theme.dart';
import '../../../../models/citizen_models.dart';
import '../../../../services/citizen_api.dart';
import '../../widgets/citizen_widgets.dart';

class CitizenDashboardPage extends StatefulWidget {
  const CitizenDashboardPage({
    super.key,
    required this.api,
    required this.onCreateReport,
    required this.onOpenMap,
  });
  final CitizenApi api;
  final VoidCallback onCreateReport, onOpenMap;
  @override
  State<CitizenDashboardPage> createState() => _CitizenDashboardPageState();
}

class _CitizenDashboardPageState extends State<CitizenDashboardPage> {
  late Future<CitizenDashboard> _dashboard = widget.api.dashboard();
  @override
  Widget build(BuildContext context) => FutureBuilder<CitizenDashboard>(
    future: _dashboard,
    builder: (context, snapshot) {
      if (snapshot.connectionState != ConnectionState.done)
        return const PageLoading();
      if (snapshot.hasError)
        return PageError(
          message: snapshot.error is CitizenApiException
              ? (snapshot.error! as CitizenApiException).userMessage
              : null,
          onRetry: () => setState(() => _dashboard = widget.api.dashboard()),
        );
      final dashboard = snapshot.data!;
      final profile = dashboard.profile;
      final stats = dashboard.statistics;
      final risk = dashboard.risk;
      return ListView(
        padding: const EdgeInsets.fromLTRB(20, 14, 20, 28),
        children: [
          const CitizenHeader(title: 'Beranda'),
          const SizedBox(height: 18),
          CitizenSurface(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        'Halo, ${profile.name}',
                        style: const TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                    StatusBadge(
                      label: profile.verified
                          ? 'Terverifikasi'
                          : 'Belum diverifikasi',
                      color: profile.verified
                          ? SigmaColors.success
                          : SigmaColors.secondary,
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  profile.region ?? 'Wilayah belum tersedia',
                  style: const TextStyle(color: SigmaColors.muted),
                ),
              ],
            ),
          ),
          Row(
            children: [
              _metric('${stats.total}', 'Total laporan'),
              _metric('${stats.process}', 'Ditangani'),
              _metric('${stats.completed}', 'Selesai'),
            ],
          ),
          const SizedBox(height: 16),
          const Text(
            'Risiko wilayah',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 8),
          CitizenSurface(
            child: risk == null
                ? const Text('Data risiko belum tersedia.')
                : Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(
                            Icons.local_fire_department_outlined,
                            color: SigmaColors.primary,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              risk.region ?? 'Wilayah',
                              style: const TextStyle(
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                          ),
                          StatusBadge(
                            label: riskLabel(risk.level),
                            color: statusColor(risk.level),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      LinearProgressIndicator(
                        value: (risk.score / 100).clamp(0.0, 1.0).toDouble(),
                        color: statusColor(risk.level),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Skor risiko ${risk.score.toStringAsFixed(0)}',
                        style: const TextStyle(color: SigmaColors.muted),
                      ),
                    ],
                  ),
          ),
          FilledButton.icon(
            onPressed: widget.onCreateReport,
            icon: const Icon(Icons.add_location_alt_outlined),
            label: const Text('Laporkan kejadian'),
            style: FilledButton.styleFrom(padding: const EdgeInsets.all(16)),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: widget.onOpenMap,
                  icon: const Icon(Icons.map_outlined),
                  label: const Text('Pantau peta'),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () =>
                      _showSos(context, dashboard.emergencyNumber),
                  icon: const Icon(Icons.phone_in_talk_outlined),
                  label: Text('SOS ${dashboard.emergencyNumber}'),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          const Text(
            'Kejadian terbaru',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 8),
          if (dashboard.incidents.isEmpty)
            const CitizenSurface(
              child: Text('Belum ada kejadian aktif yang dipublikasikan.'),
            ),
          ...dashboard.incidents.map((incident) {
            return CitizenSurface(
              child: ListTile(
                contentPadding: EdgeInsets.zero,
                leading: const Icon(
                  Icons.warning_amber_rounded,
                  color: SigmaColors.secondary,
                ),
                title: Text(
                  incident.locationDescription ?? 'Lokasi belum tersedia',
                ),
                subtitle: Text(statusLabel(incident.fireStatus)),
                trailing: StatusBadge(
                  label: riskLabel(incident.severityLevel),
                  color: statusColor(incident.severityLevel),
                ),
              ),
            );
          }),
          const SizedBox(height: 8),
          const Text(
            'Laporan terbaru',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 8),
          if (dashboard.latestReports.isEmpty)
            const CitizenSurface(child: Text('Belum ada laporan yang dikirim.')),
          ...dashboard.latestReports.map(
            (report) => CitizenSurface(
              child: ListTile(
                contentPadding: EdgeInsets.zero,
                leading: const Icon(Icons.assignment_outlined),
                title: Text(report.number),
                subtitle: Text(reportTypeLabel(report.type)),
                trailing: StatusBadge(
                  label: statusLabel(report.status),
                  color: statusColor(report.status),
                ),
              ),
            ),
          ),
        ],
      );
    },
  );
  Widget _metric(String value, String label) => Expanded(
    child: Container(
      margin: const EdgeInsets.only(right: 7),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            value,
            style: const TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w900,
              color: SigmaColors.primary,
            ),
          ),
          Text(
            label,
            style: const TextStyle(fontSize: 11, color: SigmaColors.muted),
          ),
        ],
      ),
    ),
  );
  void _showSos(BuildContext context, String number) => showDialog(
    context: context,
    builder: (_) => AlertDialog(
      title: Text('Hubungi SOS $number?'),
      content: const Text(
        'Pastikan kondisi Anda aman sebelum menghubungi layanan darurat.',
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('Batal'),
        ),
        FilledButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('Mengerti'),
        ),
      ],
    ),
  );
}
