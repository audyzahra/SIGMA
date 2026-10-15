import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';

import '../../../auth/data/datasources/auth_storage.dart';
import '../../data/models/citizen_models.dart';
import '../../data/datasources/citizen_remote_data_source.dart';
import '../../widgets/citizen_widgets.dart';

class ReportDetailPage extends StatefulWidget {
  const ReportDetailPage({
    super.key,
    required this.api,
    required this.id,
  });

  final CitizenApi api;
  final String id;

  @override
  State<ReportDetailPage> createState() => _ReportDetailPageState();
}

class _ReportDetailPageState extends State<ReportDetailPage> {
  late Future<CitizenReport> _report = widget.api.report(widget.id);

  final AuthStorage _authStorage = AuthStorage();

  String? _photoToken;

  @override
  void initState() {
    super.initState();
    _loadPhotoToken();
  }

  Future<void> _loadPhotoToken() async {
    final token = await _authStorage.getToken();

    if (!mounted) {
      return;
    }

    setState(() {
      _photoToken = token;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF7F8FA),
      body: SafeArea(
        child: FutureBuilder<CitizenReport>(
          future: _report,
          builder: (context, snapshot) {
            if (snapshot.connectionState != ConnectionState.done) {
              return const PageLoading();
            }

            if (snapshot.hasError || snapshot.data == null) {
              return PageError(
                onRetry: () {
                  setState(() {
                    _report = widget.api.report(widget.id);
                  });
                },
              );
            }

            final report = snapshot.data!;

            return ListView(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
              children: [
                const CitizenHeader(
                  title: 'Detail laporan',
                  back: true,
                ),
                const SizedBox(height: 20),
                _buildHeader(report),
                const SizedBox(height: 16),
                _buildPhoto(report),
                const SizedBox(height: 16),
                _buildInformation(report),
                const SizedBox(height: 16),
                _buildDescription(report),
                const SizedBox(height: 16),
                _buildLocation(report),
                const SizedBox(height: 20),
                _buildTimeline(report),
                const SizedBox(height: 16),
                _buildCurrentStatus(report),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _buildHeader(CitizenReport report) {
    final status = report.status;

    return CitizenSurface(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      report.number,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF64748B),
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      reportTypeLabel(report.type),
                      style: const TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF172033),
                      ),
                    ),
                  ],
                ),
              ),
              StatusBadge(
                label: statusLabel(status),
                color: statusColor(status),
              ),
            ],
          ),
          const SizedBox(height: 18),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: const Color(0xFFE2E8F0),
              ),
            ),
            child: Row(
              children: [
                const Icon(
                  Icons.schedule_outlined,
                  size: 20,
                  color: Color(0xFF64748B),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Waktu laporan',
                        style: TextStyle(
                          fontSize: 12,
                          color: Color(0xFF64748B),
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        _formatDate(report.createdAt),
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF172033),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPhoto(CitizenReport report) {
    final photoUrl = report.displayPhotoUrl;
    final annotatedPhotoUrl = report.annotatedPhotoUrl;

    if (photoUrl == null || photoUrl.trim().isEmpty) {
      return CitizenSurface(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _sectionTitle(
              Icons.photo_camera_outlined,
              'Bukti foto',
            ),
            const SizedBox(height: 14),
            Container(
              width: double.infinity,
              height: 150,
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: const Color(0xFFE2E8F0),
                ),
              ),
              child: const Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.image_not_supported_outlined,
                    size: 40,
                    color: Color(0xFF94A3B8),
                  ),
                  SizedBox(height: 10),
                  Text(
                    'Tidak ada foto laporan',
                    style: TextStyle(
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF64748B),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    if (_photoToken == null) {
      return const CitizenSurface(
        child: SizedBox(
          height: 180,
          child: Center(
            child: CircularProgressIndicator(),
          ),
        ),
      );
    }

    final hasAnnotatedPhoto = annotatedPhotoUrl != null &&
        annotatedPhotoUrl.trim().isNotEmpty;

    return CitizenSurface(
      padding: EdgeInsets.zero,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _sectionTitle(
                  Icons.photo_camera_outlined,
                  'Bukti foto',
                ),
                const SizedBox(height: 6),
                Text(
                  hasAnnotatedPhoto
                      ? 'Foto hasil deteksi AI YOLO'
                      : 'Foto asli laporan',
                  style: const TextStyle(
                    fontSize: 12,
                    color: Color(0xFF64748B),
                  ),
                ),
              ],
            ),
          ),
          GestureDetector(
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => _ReportPhotoViewer(
                    imageUrl: photoUrl,
                    token: _photoToken!,
                  ),
                ),
              );
            },
            child: ClipRRect(
              borderRadius: const BorderRadius.vertical(
                bottom: Radius.circular(16),
              ),
              child: Stack(
                children: [
                  AspectRatio(
                    aspectRatio: 16 / 10,
                    child: Image.network(
                      photoUrl,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      headers: {
                        'Authorization': 'Bearer $_photoToken',
                        'Accept': 'application/json',
                      },
                      errorBuilder: (context, error, stackTrace) {
                        return Container(
                          color: const Color(0xFFF8FAFC),
                          alignment: Alignment.center,
                          child: const Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.broken_image_outlined,
                                size: 40,
                                color: Color(0xFF94A3B8),
                              ),
                              SizedBox(height: 8),
                              Text(
                                'Foto tidak dapat dimuat',
                                style: TextStyle(
                                  color: Color(0xFF64748B),
                                ),
                              ),
                            ],
                          ),
                        );
                      },
                      loadingBuilder: (context, child, progress) {
                        if (progress == null) {
                          return child;
                        }

                        return Container(
                          color: const Color(0xFFF8FAFC),
                          alignment: Alignment.center,
                          child: const CircularProgressIndicator(),
                        );
                      },
                    ),
                  ),
                  Positioned(
                    right: 12,
                    bottom: 12,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 11,
                        vertical: 8,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.65),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.zoom_in,
                            size: 16,
                            color: Colors.white,
                          ),
                          SizedBox(width: 6),
                          Text(
                            'Lihat foto',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInformation(CitizenReport report) {
    return CitizenSurface(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _sectionTitle(
            Icons.info_outline,
            'Informasi laporan',
          ),
          const SizedBox(height: 16),
          _informationRow(
            icon: Icons.local_fire_department_outlined,
            label: 'Jenis kejadian',
            value: reportTypeLabel(report.type),
          ),
          const Divider(height: 24),
          _informationRow(
            icon: Icons.calendar_today_outlined,
            label: 'Tanggal laporan',
            value: _formatDateOnly(report.createdAt),
          ),
          const Divider(height: 24),
          _informationRow(
            icon: Icons.access_time_outlined,
            label: 'Waktu laporan',
            value: _formatTime(report.createdAt),
          ),
          const Divider(height: 24),
          _informationRow(
            icon: Icons.tag_outlined,
            label: 'Nomor laporan',
            value: report.number,
          ),
        ],
      ),
    );
  }

  Widget _buildDescription(CitizenReport report) {
    final description = report.description.trim();

    return CitizenSurface(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _sectionTitle(
            Icons.notes_outlined,
            'Keterangan laporan',
          ),
          const SizedBox(height: 14),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: const Color(0xFFE2E8F0),
              ),
            ),
            child: Text(
              description.isEmpty
                  ? 'Tidak ada keterangan tambahan.'
                  : description,
              style: const TextStyle(
                fontSize: 14,
                height: 1.5,
                color: Color(0xFF475569),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLocation(CitizenReport report) {
    final point = LatLng(
      report.latitude,
      report.longitude,
    );

    return CitizenSurface(
      padding: EdgeInsets.zero,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
            child: _sectionTitle(
              Icons.location_on_outlined,
              'Lokasi kejadian',
            ),
          ),
          ClipRRect(
            borderRadius: const BorderRadius.vertical(
              bottom: Radius.circular(16),
            ),
            child: SizedBox(
              height: 260,
              child: FlutterMap(
                options: MapOptions(
                  initialCenter: point,
                  initialZoom: 15,
                  interactionOptions: const InteractionOptions(
                    flags: InteractiveFlag.all,
                  ),
                ),
                children: [
                  TileLayer(
                    urlTemplate:
                        'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    userAgentPackageName: 'com.sigma.apk_sigma',
                  ),
                  MarkerLayer(
                    markers: [
                      Marker(
                        point: point,
                        width: 48,
                        height: 48,
                        child: const Icon(
                          Icons.location_on,
                          color: Color(0xFFDC2626),
                          size: 42,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTimeline(CitizenReport report) {
    final history = report.history;

    return CitizenSurface(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _sectionTitle(
            Icons.timeline_outlined,
            'Riwayat laporan',
          ),
          const SizedBox(height: 18),
          if (history.isEmpty)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: const Color(0xFFE2E8F0),
                ),
              ),
              child: const Text(
                'Belum ada riwayat perubahan status laporan.',
                style: TextStyle(
                  fontSize: 13,
                  color: Color(0xFF64748B),
                ),
              ),
            )
          else
            ...List.generate(history.length, (index) {
              final item = history[index];
              final isLast = index == history.length - 1;

              return Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(
                    width: 28,
                    child: Column(
                      children: [
                        Container(
                          width: 12,
                          height: 12,
                          decoration: BoxDecoration(
                            color: statusColor(item.status),
                            shape: BoxShape.circle,
                          ),
                        ),
                        if (!isLast)
                          Container(
                            width: 2,
                            height: 72,
                            color: const Color(0xFFE2E8F0),
                          ),
                      ],
                    ),
                  ),
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.only(bottom: 18),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            statusLabel(item.status),
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF172033),
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _formatDate(item.at),
                            style: const TextStyle(
                              fontSize: 12,
                              color: Color(0xFF64748B),
                            ),
                          ),
                          if (item.description.trim().isNotEmpty) ...[
                            const SizedBox(height: 6),
                            Text(
                              item.description,
                              style: const TextStyle(
                                fontSize: 13,
                                height: 1.4,
                                color: Color(0xFF475569),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                ],
              );
            }),
        ],
      ),
    );
  }

  Widget _buildCurrentStatus(CitizenReport report) {
    return CitizenSurface(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _sectionTitle(
            Icons.fact_check_outlined,
            'Status terkini',
          ),
          const SizedBox(height: 14),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: statusColor(report.status).withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: statusColor(report.status).withValues(alpha: 0.25),
              ),
            ),
            child: Row(
              children: [
                Icon(
                  Icons.info_outline,
                  color: statusColor(report.status),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        statusLabel(report.status),
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: statusColor(report.status),
                        ),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'Status laporan mengikuti pembaruan penanganan oleh petugas.',
                        style: TextStyle(
                          fontSize: 12,
                          height: 1.4,
                          color: Color(0xFF475569),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _sectionTitle(IconData icon, String title) {
    return Row(
      children: [
        Container(
          width: 36,
          height: 36,
          decoration: BoxDecoration(
            color: const Color(0xFFFEF2F2),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(
            icon,
            size: 20,
            color: const Color(0xFFDC2626),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            title,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w800,
              color: Color(0xFF172033),
            ),
          ),
        ),
      ],
    );
  }

  Widget _informationRow({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(
          icon,
          size: 20,
          color: const Color(0xFF64748B),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(
                  fontSize: 12,
                  color: Color(0xFF64748B),
                ),
              ),
              const SizedBox(height: 4),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF172033),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  String _formatDate(DateTime date) {
    const months = [
      'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember',
    ];

    return '${date.day} ${months[date.month - 1]} ${date.year}, '
        '${_formatTime(date)}';
  }

  String _formatDateOnly(DateTime date) {
    const months = [
      'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember',
    ];

    return '${date.day} ${months[date.month - 1]} ${date.year}';
  }

  String _formatTime(DateTime date) {
    final hour = date.hour.toString().padLeft(2, '0');
    final minute = date.minute.toString().padLeft(2, '0');

    return '$hour:$minute WIB';
  }
}

class _ReportPhotoViewer extends StatelessWidget {
  const _ReportPhotoViewer({
    required this.imageUrl,
    required this.token,
  });

  final String imageUrl;
  final String token;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: const Text('Bukti foto laporan'),
      ),
      body: Center(
        child: InteractiveViewer(
          minScale: 0.5,
          maxScale: 5,
          child: Image.network(
            imageUrl,
            headers: {
              'Authorization': 'Bearer $token',
              'Accept': 'application/json',
            },
            fit: BoxFit.contain,
            errorBuilder: (context, error, stackTrace) {
              return const Padding(
                padding: EdgeInsets.all(24),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.broken_image_outlined,
                      color: Colors.white70,
                      size: 48,
                    ),
                    SizedBox(height: 12),
                    Text(
                      'Foto tidak dapat dimuat.',
                      style: TextStyle(
                        color: Colors.white70,
                      ),
                    ),
                  ],
                ),
              );
            },
            loadingBuilder: (context, child, progress) {
              if (progress == null) {
                return child;
              }

              return const Center(
                child: CircularProgressIndicator(
                  color: Colors.white,
                ),
              );
            },
          ),
        ),
      ),
    );
  }
}