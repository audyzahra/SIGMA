import 'dart:async';

import 'package:flutter/material.dart';

import '../../../../core/theme/sigma_theme.dart';
import '../../data/datasources/citizen_remote_data_source.dart';
import '../../data/models/citizen_models.dart';
import '../../widgets/citizen_widgets.dart';

class EarlyWarningListPage extends StatefulWidget {
  const EarlyWarningListPage({super.key, required this.api});

  final CitizenApi api;

  @override
  State<EarlyWarningListPage> createState() => _EarlyWarningListPageState();
}

class _EarlyWarningListPageState extends State<EarlyWarningListPage> {
  List<CitizenEarlyWarning> _warnings = const [];
  bool _loading = true;
  bool _refreshing = false;
  String? _error;
  Timer? _timer;
  DateTime? _updatedAt;

  @override
  void initState() {
    super.initState();
    _refresh();
    _timer = Timer.periodic(const Duration(seconds: 30), (_) => _refresh());
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _refresh() async {
    if (_refreshing) return;
    _refreshing = true;
    try {
      final warnings = await widget.api.earlyWarnings();
      if (!mounted) return;
      setState(() {
        _warnings = warnings;
        _updatedAt = DateTime.now();
        _error = null;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _warnings = const [];
        _error = 'Peringatan gagal diperbarui. Periksa koneksi internet.';
        _loading = false;
      });
    } finally {
      _refreshing = false;
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Peringatan dini')),
    body: RefreshIndicator(
      onRefresh: _refresh,
      child: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          if (_updatedAt != null)
            Text(
              'Diperbarui ${_updatedAt!.toLocal().toString().substring(0, 16)} · pembaruan otomatis 30 detik',
              style: const TextStyle(color: SigmaColors.muted, fontSize: 12),
            ),
          if (_error != null)
            CitizenSurface(
              child: Row(children: [
                const Icon(Icons.wifi_off, color: SigmaColors.secondary),
                const SizedBox(width: 10),
                Expanded(child: Text(_error!)),
                IconButton(onPressed: _refresh, icon: const Icon(Icons.refresh)),
              ]),
            ),
          if (_loading)
            const Padding(padding: EdgeInsets.all(32), child: Center(child: CircularProgressIndicator())),
          if (!_loading && _warnings.isEmpty && _error == null)
            const CitizenSurface(child: Text('Tidak ada peringatan aktif untuk wilayah profil Anda. Jika wilayah belum dipilih, atur di Profil.')),
          for (final warning in _warnings)
            Padding(
              padding: const EdgeInsets.only(top: 12),
              child: CitizenSurface(
                child: ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: Icon(Icons.campaign_outlined, color: _levelColor(warning.level)),
                  title: Row(children: [
                    Expanded(child: Text(warning.area, style: const TextStyle(fontWeight: FontWeight.w700))),
                    StatusBadge(label: warning.level.toUpperCase(), color: _levelColor(warning.level)),
                  ]),
                  subtitle: Text('${warning.reason}\nTerbit ${warning.issuedAt.toLocal()}'),
                  isThreeLine: true,
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => Navigator.push<void>(context, MaterialPageRoute(
                    builder: (_) => EarlyWarningDetailPage(api: widget.api, warningId: warning.id),
                  )),
                ),
              ),
            ),
        ],
      ),
    ),
  );
}

class EarlyWarningDetailPage extends StatefulWidget {
  const EarlyWarningDetailPage({super.key, required this.api, required this.warningId});

  final CitizenApi api;
  final int warningId;

  @override
  State<EarlyWarningDetailPage> createState() => _EarlyWarningDetailPageState();
}

class _EarlyWarningDetailPageState extends State<EarlyWarningDetailPage> {
  CitizenEarlyWarning? _warning;
  bool _loading = true;
  String? _error;
  Timer? _timer;
  bool _refreshing = false;

  @override
  void initState() {
    super.initState();
    _refresh();
    _timer = Timer.periodic(const Duration(seconds: 30), (_) => _refresh());
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _refresh() async {
    if (_refreshing) return;
    _refreshing = true;
    try {
      final warning = await widget.api.earlyWarning(widget.warningId);
      if (!mounted) return;
      setState(() {
        _warning = warning;
        _error = null;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _warning = null;
        _error = 'Detail peringatan tidak dapat dimuat. Periksa koneksi atau buka daftar terbaru.';
        _loading = false;
      });
    } finally {
      _refreshing = false;
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Detail peringatan')),
    body: _loading
        ? const PageLoading()
        : _warning == null
            ? PageError(message: _error, onRetry: _refresh)
            : ListView(padding: const EdgeInsets.all(20), children: [
                CitizenSurface(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    Expanded(child: Text(_warning!.area, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800))),
                    StatusBadge(label: _warning!.level.toUpperCase(), color: _levelColor(_warning!.level)),
                  ]),
                  const SizedBox(height: 8),
                  Text(_warning!.status == 'active' ? 'Status: Aktif' : 'Status: ${_warning!.status == 'expired' ? 'Kedaluwarsa' : 'Ditutup'}'),
                  if (_warning!.region != null) Text('Wilayah: ${_warning!.region}'),
                  Text('Diterbitkan ${_warning!.issuedAt.toLocal()}'),
                  if (_warning!.expiresAt != null) Text('Berlaku sampai ${_warning!.expiresAt!.toLocal()}'),
                  const Divider(height: 24),
                  const Text('Alasan', style: TextStyle(fontWeight: FontWeight.w700)),
                  Text(_warning!.reason),
                  const SizedBox(height: 16),
                  const Text('Instruksi keselamatan', style: TextStyle(fontWeight: FontWeight.w700)),
                  Text(_warning!.instructions),
                  const SizedBox(height: 12),
                  const Text('Status diperiksa otomatis setiap 30 detik.', style: TextStyle(color: SigmaColors.muted, fontSize: 12)),
                ])),
              ]),
  );
}

Color _levelColor(String level) => switch (level) {
  'critical' => SigmaColors.primary,
  'warning' => SigmaColors.secondary,
  'watch' => Colors.orange,
  _ => SigmaColors.success,
};
