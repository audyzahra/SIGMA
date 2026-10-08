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
              padding: const EdgeInsets.fromLTRB(
                20,
                16,
                20,
                32,
              ),
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
    final photoUrl = report.photoUrl;

    if (photoUrl == null || photoUrl.isEmpty) {
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
      return CitizenSurface(
        child: SizedBox(
          height: 180,
          child: Center(
            child: CircularProgressIndicator(),
          ),
        ),
      );
    }

    return CitizenSurface(
      padding: EdgeInsets.zero,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(
              16,
              16,
              16,
              12,
            ),
            child: _sectionTitle(
              Icons.photo_camera_outlined,
              'Bukti foto',
            ),
          ),
          GestureDetector(
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => _ReportPhotoViewer(
                    imageUrl: photoUrl,
                    token: _photoToken,
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
                        color: Colors.black.withValues(
                          alpha: 0.65,
                        ),
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
            padding: const EdgeInsets.fromLTRB(
              16,
              16,
              16,
              12,
            ),
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
                    userAgentPackageName:
                        'com.sigma.apk_sigma',
                  ),
                  MarkerLayer(
                    markers: [
                      Marker(
                        point: point,
                        width: 52,
                        height: 52,
                        child: const Icon(
                          Icons.location_on,
                          size: 48,
                          color: Color(0xFFC82828),
                        ),
                      ),
                    ],
                  ),
                  RichAttributionWidget(
                    attributions: [
                      TextSourceAttribution(
                        'OpenStreetMap contributors',
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: const Color(0xFFE2E8F0),
                ),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(
                    Icons.my_location_outlined,
                    size: 20,
                    color: Color(0xFFC82828),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Koordinat lokasi',
                          style: TextStyle(
                            fontSize: 12,
                            color: Color(0xFF64748B),
                          ),
                        ),
                        const SizedBox(height: 5),
                        Text(
                          '${report.latitude.toStringAsFixed(6)}, '
                          '${report.longitude.toStringAsFixed(6)}',
                          style: const TextStyle(
                            fontSize: 14,
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
          ),
        ],
      ),
    );
  }

  Widget _buildTimeline(CitizenReport report) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Perkembangan laporan',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w800,
            color: Color(0xFF172033),
          ),
        ),
        const SizedBox(height: 14),
        if (report.history.isEmpty)
          CitizenSurface(
            child: const EmptyState(
              message: 'Belum ada pembaruan laporan.',
            ),
          )
        else
          ...List.generate(
            report.history.length,
            (index) {
              final entry = report.history[index];
              final isLast =
                  index == report.history.length - 1;

              return _TimelineItem(
                entry: entry,
                isLast: isLast,
              );
            },
          ),
      ],
    );
  }

  Widget _buildCurrentStatus(CitizenReport report) {
    final color = statusColor(report.status);

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: color.withValues(alpha: 0.20),
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.14),
              shape: BoxShape.circle,
            ),
            child: Icon(
              _statusIcon(report.status),
              color: color,
              size: 21,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Status saat ini',
                  style: TextStyle(
                    fontSize: 12,
                    color: Color(0xFF64748B),
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  statusLabel(report.status),
                  style: TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                    color: color,
                  ),
                ),
                const SizedBox(height: 5),
                Text(
                  _statusDescription(report.status),
                  style: const TextStyle(
                    fontSize: 13,
                    height: 1.45,
                    color: Color(0xFF475569),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _sectionTitle(
    IconData icon,
    String title,
  ) {
    return Row(
      children: [
        Icon(
          icon,
          size: 20,
          color: const Color(0xFFC82828),
        ),
        const SizedBox(width: 8),
        Text(
          title,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w800,
            color: Color(0xFF172033),
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
        Container(
          width: 38,
          height: 38,
          decoration: BoxDecoration(
            color: const Color(0xFFFFF1F1),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(
            icon,
            size: 19,
            color: const Color(0xFFC82828),
          ),
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
                  fontWeight: FontWeight.w700,
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
    final local = date.toLocal();

    return '${_twoDigits(local.day)} '
        '${_monthName(local.month)} '
        '${local.year} • '
        '${_twoDigits(local.hour)}:'
        '${_twoDigits(local.minute)} WIB';
  }

  String _formatDateOnly(DateTime date) {
    final local = date.toLocal();

    return '${_twoDigits(local.day)} '
        '${_monthName(local.month)} '
        '${local.year}';
  }

  String _formatTime(DateTime date) {
    final local = date.toLocal();

    return '${_twoDigits(local.hour)}:'
        '${_twoDigits(local.minute)} WIB';
  }

  String _twoDigits(int value) {
    return value.toString().padLeft(2, '0');
  }

  String _monthName(int month) {
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

    return months[month - 1];
  }

  IconData _statusIcon(String status) {
    switch (status) {
      case 'verified':
        return Icons.verified_outlined;
      case 'rejected':
        return Icons.cancel_outlined;
      case 'process':
        return Icons.sync_outlined;
      case 'completed':
        return Icons.task_alt_outlined;
      default:
        return Icons.schedule_outlined;
    }
  }

  String _statusDescription(String status) {
    switch (status) {
      case 'verified':
        return 'Laporan telah diverifikasi oleh Pemerintah SIGMA.';
      case 'rejected':
        return 'Laporan tidak dapat diteruskan berdasarkan hasil verifikasi.';
      case 'process':
        return 'Laporan sedang dalam proses penanganan.';
      case 'completed':
        return 'Penanganan laporan telah diselesaikan.';
      default:
        return 'Laporan telah diterima oleh sistem SIGMA dan menunggu proses berikutnya.';
    }
  }
}

class _TimelineItem extends StatelessWidget {
  const _TimelineItem({
    required this.entry,
    required this.isLast,
  });

  final ReportHistory entry;
  final bool isLast;

  @override
  Widget build(BuildContext context) {
    final color = statusColor(entry.status);

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 34,
            child: Column(
              children: [
                Container(
                  width: 30,
                  height: 30,
                  decoration: BoxDecoration(
                    color: color.withValues(
                      alpha: 0.12,
                    ),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    _timelineIcon(entry.status),
                    size: 17,
                    color: color,
                  ),
                ),
                if (!isLast)
                  Expanded(
                    child: Container(
                      width: 2,
                      margin: const EdgeInsets.symmetric(
                        vertical: 4,
                      ),
                      color: const Color(0xFFE2E8F0),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Container(
              margin: const EdgeInsets.only(
                bottom: 14,
              ),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius:
                    BorderRadius.circular(14),
                border: Border.all(
                  color: const Color(0xFFE2E8F0),
                ),
              ),
              child: Column(
                crossAxisAlignment:
                    CrossAxisAlignment.start,
                children: [
                  Row(
                    crossAxisAlignment:
                        CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Text(
                          statusLabel(entry.status),
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF172033),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        _formatTimelineDate(entry.at),
                        style: const TextStyle(
                          fontSize: 10,
                          color: Color(0xFF94A3B8),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    entry.description.isEmpty
                        ? 'Status diperbarui.'
                        : entry.description,
                    style: const TextStyle(
                      fontSize: 12,
                      height: 1.45,
                      color: Color(0xFF64748B),
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

  IconData _timelineIcon(String status) {
    switch (status) {
      case 'verified':
        return Icons.verified;
      case 'rejected':
        return Icons.close;
      case 'process':
        return Icons.sync;
      case 'completed':
        return Icons.check;
      default:
        return Icons.check;
    }
  }

  String _formatTimelineDate(DateTime date) {
    final local = date.toLocal();

    return '${_twoDigits(local.day)} '
        '${_monthName(local.month)} '
        '${local.year}\n'
        '${_twoDigits(local.hour)}:'
        '${_twoDigits(local.minute)} WIB';
  }

  String _twoDigits(int value) {
    return value.toString().padLeft(2, '0');
  }

  String _monthName(int month) {
    const months = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'Mei',
      'Jun',
      'Jul',
      'Agu',
      'Sep',
      'Okt',
      'Nov',
      'Des',
    ];

    return months[month - 1];
  }
}

class _ReportPhotoViewer extends StatelessWidget {
  const _ReportPhotoViewer({
    required this.imageUrl,
    required this.token,
  });

  final String imageUrl;
  final String? token;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: const Text('Bukti foto'),
      ),
      body: Center(
        child: InteractiveViewer(
          minScale: 0.8,
          maxScale: 4,
          child: Image.network(
            imageUrl,
            headers: token != null
                ? {
                    'Authorization': 'Bearer $token',
                    'Accept': 'application/json',
                  }
                : null,
            fit: BoxFit.contain,
            errorBuilder: (context, error, stackTrace) {
              return const Column(
                mainAxisAlignment:
                    MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.broken_image_outlined,
                    size: 56,
                    color: Colors.white54,
                  ),
                  SizedBox(height: 12),
                  Text(
                    'Foto tidak dapat dimuat',
                    style: TextStyle(
                      color: Colors.white70,
                    ),
                  ),
                ],
              );
            },
          ),
        ),
      ),
    );
  }
}