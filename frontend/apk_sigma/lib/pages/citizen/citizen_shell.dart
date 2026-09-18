import 'package:flutter/material.dart';

import '../../data/citizen_dummy_data.dart';
import '../../models/citizen_models.dart';
import '../auth/auth_widgets.dart';

const _green = Color(0xFF087C43),
    _orange = Color(0xFFF77F00),
    _blue = Color(0xFF2563EB);

class CitizenShell extends StatefulWidget {
  const CitizenShell({super.key, required this.name});
  final String name;
  @override
  State<CitizenShell> createState() => _CitizenShellState();
}

class _CitizenShellState extends State<CitizenShell> {
  int index = 0;
  void report() => Navigator.push(
    context,
    MaterialPageRoute(builder: (_) => const SubmitReportPage()),
  );
  @override
  Widget build(BuildContext c) {
    final pages = [
      CitizenHome(
        name: widget.name,
        onMap: () => setState(() => index = 2),
        onReport: report,
      ),
      const ReportsPage(),
      CitizenMap(onReport: report),
      const CitizenProfilePage(),
    ];
    return Scaffold(
      body: SafeArea(child: pages[index]),
      bottomNavigationBar: NavigationBar(
        selectedIndex: index,
        onDestinationSelected: (v) => setState(() => index = v),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home),
            label: 'Beranda',
          ),
          NavigationDestination(
            icon: Icon(Icons.assignment_outlined),
            selectedIcon: Icon(Icons.assignment),
            label: 'Laporan',
          ),
          NavigationDestination(
            icon: Icon(Icons.map_outlined),
            selectedIcon: Icon(Icons.map),
            label: 'Peta',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person),
            label: 'Profil',
          ),
        ],
      ),
    );
  }
}

class CitizenHeader extends StatelessWidget {
  const CitizenHeader({super.key, required this.title, this.back = false});
  final String title;
  final bool back;
  @override
  Widget build(BuildContext c) => Row(
    children: [
      if (back)
        IconButton(
          onPressed: () => Navigator.pop(c),
          icon: const Icon(Icons.arrow_back),
        )
      else
        ClipRRect(
          borderRadius: BorderRadius.circular(10),
          child: Image.asset(
            'assets/images/logo.jpeg',
            width: 38,
            height: 38,
            fit: BoxFit.cover,
          ),
        ),
      const SizedBox(width: 10),
      Expanded(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              back ? title : 'SIGMA WARGA',
              style: TextStyle(
                fontSize: back ? 20 : 12,
                fontWeight: FontWeight.w900,
                color: back ? sigmaNavy : sigmaRed,
              ),
            ),
            if (!back)
              Text(
                title,
                style: const TextStyle(
                  fontSize: 21,
                  fontWeight: FontWeight.w800,
                  color: sigmaNavy,
                ),
              ),
          ],
        ),
      ),
      if (!back) ...[
        IconButton(
          onPressed: () => showSnack(c, 'Tidak ada notifikasi baru'),
          icon: const Badge(
            smallSize: 7,
            child: Icon(Icons.notifications_none),
          ),
        ),
        const CircleAvatar(
          backgroundColor: sigmaRed,
          foregroundColor: Colors.white,
          child: Icon(Icons.person_outline),
        ),
      ],
    ],
  );
}

class CitizenHome extends StatelessWidget {
  const CitizenHome({
    super.key,
    required this.name,
    required this.onMap,
    required this.onReport,
  });
  final String name;
  final VoidCallback onMap, onReport;
  @override
  Widget build(BuildContext c) => ListView(
    padding: const EdgeInsets.fromLTRB(20, 14, 20, 24),
    children: [
      const CitizenHeader(title: 'Beranda'),
      const SizedBox(height: 16),
      CitizenCard(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    'Halo,\n$name',
                    style: const TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
                const StatusChip(text: 'Warga Terverifikasi', color: _green),
              ],
            ),
            const SizedBox(height: 8),
            const Text(
              'Indramayu, Jawa Barat',
              style: TextStyle(fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 12),
            const Row(
              children: [
                Expanded(
                  child: MiniInfo(
                    icon: Icons.wb_sunny_outlined,
                    label: 'Cuaca',
                    value: 'Cerah Berawan',
                  ),
                ),
                SizedBox(width: 8),
                Expanded(
                  child: MiniInfo(
                    icon: Icons.thermostat_outlined,
                    label: 'Suhu',
                    value: '32°C',
                  ),
                ),
                SizedBox(width: 8),
                Expanded(
                  child: MiniInfo(
                    icon: Icons.air_outlined,
                    label: 'ISPU',
                    value: 'Sedang (78)',
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
      const SizedBox(height: 8),
      const RiskCard(),
      const SizedBox(height: 14),
      const SectionTitle(
        title: 'Pantauan Radar Sekitar',
        trailing: 'Radius 5 km',
      ),
      const SizedBox(height: 8),
      RadarMap(onTap: onMap),
      const SizedBox(height: 16),
      EmergencyButton(
        icon: Icons.local_fire_department_outlined,
        title: 'Laporkan Kebakaran',
        subtitle: 'Kirim foto dan lokasi ke petugas',
        onPressed: onReport,
      ),
      const SizedBox(height: 10),
      Row(
        children: [
          Expanded(
            child: ActionTile(
              icon: Icons.mic_none,
              title: 'Bicara Darurat',
              subtitle: 'Laporan suara instan',
              color: _orange,
              onTap: () => Navigator.push(
                c,
                MaterialPageRoute(builder: (_) => const EmergencyVoicePage()),
              ),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: ActionTile(
              icon: Icons.phone_in_talk_outlined,
              title: 'SOS 112',
              subtitle: 'Bebas pulsa 24 jam',
              color: sigmaRed,
              onTap: () => showSosDialog(c),
            ),
          ),
        ],
      ),
      const SizedBox(height: 18),
      CitizenCard(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Tips Aman Hari Ini',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 10),
            ...CitizenDummyData.safetyTips.map(
              (t) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: MiniInfo(
                  icon: Icons.check_circle_outline,
                  label: t.$1,
                  value: t.$2,
                ),
              ),
            ),
          ],
        ),
      ),
    ],
  );
}

class RiskCard extends StatelessWidget {
  const RiskCard({super.key});
  @override
  Widget build(BuildContext c) => CitizenCard(
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Row(
          children: [
            Icon(Icons.local_fire_department_outlined, color: sigmaRed),
            SizedBox(width: 7),
            Expanded(
              child: Text(
                'Risiko Kebakaran Saat Ini',
                style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16),
              ),
            ),
            StatusChip(text: 'TINGGI', color: sigmaRed),
          ],
        ),
        const SizedBox(height: 10),
        const Text(
          'Area sekitar Anda perlu waspada terhadap potensi sebaran asap dan titik api.',
        ),
        const SizedBox(height: 12),
        const LinearProgressIndicator(
          value: .72,
          minHeight: 9,
          color: sigmaRed,
          borderRadius: BorderRadius.all(Radius.circular(8)),
        ),
        const SizedBox(height: 12),
        const MiniInfo(
          icon: Icons.location_searching,
          label: 'Titik panas terdeteksi',
          value: '3,2 km ke arah timur laut',
        ),
      ],
    ),
  );
}

class ReportsPage extends StatefulWidget {
  const ReportsPage({super.key});
  @override
  State<ReportsPage> createState() => _ReportsPageState();
}

class _ReportsPageState extends State<ReportsPage> {
  String filter = 'Semua';
  @override
  Widget build(BuildContext c) {
    final reports = CitizenDummyData.reports.where(
      (r) => filter == 'Semua' || r.status == filter,
    );
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        const CitizenHeader(title: 'Laporan'),
        const SizedBox(height: 18),
        const Row(
          children: [
            Expanded(
              child: Text(
                'Laporan Saya',
                style: TextStyle(fontSize: 23, fontWeight: FontWeight.w800),
              ),
            ),
            StatusChip(text: 'Total: 4 Laporan', color: _blue),
          ],
        ),
        const SizedBox(height: 14),
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: ['Semua', 'Menunggu', 'Valid', 'Ditangani', 'Selesai']
                .map(
                  (x) => Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: ChoiceChip(
                      label: Text(x),
                      selected: filter == x,
                      onSelected: (_) => setState(() => filter = x),
                    ),
                  ),
                )
                .toList(),
          ),
        ),
        const SizedBox(height: 14),
        ...reports.map((r) => ReportCard(report: r)),
      ],
    );
  }
}

class ReportCard extends StatelessWidget {
  const ReportCard({super.key, required this.report});
  final CitizenReport report;
  @override
  Widget build(BuildContext c) => Padding(
    padding: const EdgeInsets.only(bottom: 13),
    child: CitizenCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  '#${report.id}',
                  style: const TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 12,
                  ),
                ),
              ),
              StatusChip(
                text: report.status,
                color: report.status == 'Ditangani' ? sigmaRed : _green,
              ),
            ],
          ),
          Text(report.time, style: const TextStyle(fontSize: 11)),
          const SizedBox(height: 8),
          Text(
            report.title,
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
          ),
          Text(report.location),
          const SizedBox(height: 12),
          LinearProgressIndicator(
            value: report.progress / 4,
            minHeight: 7,
            color: sigmaRed,
          ),
          TextButton(
            onPressed: () => showModalBottomSheet(
              context: c,
              builder: (_) => Padding(
                padding: const EdgeInsets.all(24),
                child: Text(
                  'Status ${report.title}\n\nPetugas sedang melakukan pembaruan penanganan.',
                ),
              ),
            ),
            child: const Text('Lihat Detail Perkembangan'),
          ),
        ],
      ),
    ),
  );
}

class SubmitReportPage extends StatefulWidget {
  const SubmitReportPage({super.key});
  @override
  State<SubmitReportPage> createState() => _SubmitReportPageState();
}

class _SubmitReportPageState extends State<SubmitReportPage> {
  bool photo = false;
  final tags = <String>{};
  @override
  Widget build(BuildContext c) => Scaffold(
    body: SafeArea(
      child: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          const CitizenHeader(title: 'Kirim Laporan', back: true),
          const SizedBox(height: 16),
          const Text(
            'Laporan Cepat Karhutla',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 12),
          CitizenCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  '1  Foto Asap / Api',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 12),
                Container(
                  height: 130,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: const Color(0xFFE5EEFF),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Center(
                    child: Text(
                      photo
                          ? 'Foto dummy berhasil dipilih'
                          : 'Ambil Foto Langsung',
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: FilledButton.icon(
                        onPressed: () => setState(() => photo = true),
                        icon: const Icon(Icons.camera_alt_outlined),
                        label: const Text('Buka Kamera'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => setState(() => photo = true),
                        icon: const Icon(Icons.photo_library_outlined),
                        label: const Text('Pilih Galeri'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          CitizenCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Expanded(
                      child: Text(
                        '2  Titik Lokasi Kejadian',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                    StatusChip(text: 'GPS Akurat', color: _green),
                  ],
                ),
                const SizedBox(height: 10),
                const SizedBox(height: 150, child: RadarMap()),
                const MiniInfo(
                  icon: Icons.location_on_outlined,
                  label: 'Jl. Raya Cikedung Lor',
                  value: 'Indramayu, Jawa Barat • -6.48291, 108.20144',
                ),
              ],
            ),
          ),
          CitizenCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  '3  Kondisi Lapangan',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
                ),
                Wrap(
                  spacing: 7,
                  runSpacing: 7,
                  children:
                      [
                            'Api Membesar',
                            'Asap Tebal',
                            'Lahan Gambut',
                            'Dekat Permukiman',
                            'Bakaran Sampah',
                          ]
                          .map(
                            (t) => FilterChip(
                              label: Text(t),
                              selected: tags.contains(t),
                              onSelected: (v) => setState(
                                () => v ? tags.add(t) : tags.remove(t),
                              ),
                            ),
                          )
                          .toList(),
                ),
                const TextField(
                  maxLength: 200,
                  maxLines: 3,
                  decoration: InputDecoration(
                    hintText: 'Tuliskan ciri api atau arah angin...',
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          EmergencyButton(
            icon: Icons.send,
            title: 'Kirim Laporan Sekarang',
            subtitle: 'Diteruskan ke petugas terdekat',
            onPressed: () => showDialog(
              context: c,
              builder: (_) => AlertDialog(
                title: const Text('Kirim laporan?'),
                content: const Text(
                  'Laporan akan diteruskan ke petugas terdekat.',
                ),
                actions: [
                  TextButton(
                    onPressed: () => Navigator.pop(c),
                    child: const Text('Batal'),
                  ),
                  FilledButton(
                    onPressed: () {
                      Navigator.pop(c);
                      showSnack(c, 'Laporan berhasil dikirim: REP-2026-XXXX');
                    },
                    child: const Text('Kirim'),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    ),
  );
}

class CitizenMap extends StatelessWidget {
  const CitizenMap({super.key, required this.onReport});
  final VoidCallback onReport;
  @override
  Widget build(BuildContext c) => Column(
    children: [
      const Padding(
        padding: EdgeInsets.fromLTRB(20, 14, 20, 10),
        child: CitizenHeader(title: 'Peta Pantau'),
      ),
      Expanded(
        child: Stack(
          children: [
            const Positioned.fill(child: RadarMap()),
            Positioned(
              right: 16,
              top: 16,
              child: Column(
                children: [
                  FloatingActionButton.small(
                    heroTag: 'loc',
                    onPressed: () => showSnack(c, 'Lokasi Anda diperbarui'),
                    child: const Icon(Icons.my_location),
                  ),
                  const SizedBox(height: 8),
                  FloatingActionButton.small(
                    heroTag: 'layer',
                    onPressed: () => showSnack(c, 'Layer peta diperbarui'),
                    child: const Icon(Icons.layers_outlined),
                  ),
                ],
              ),
            ),
            Positioned(
              left: 18,
              right: 18,
              bottom: 18,
              child: CitizenCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'WASPADA RINGAN',
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        color: _orange,
                      ),
                    ),
                    const Text('1 Titik Api (3.2 km Barat Laut)'),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        Expanded(
                          child: FilledButton.icon(
                            onPressed: onReport,
                            icon: const Icon(
                              Icons.local_fire_department_outlined,
                            ),
                            label: const Text('Laporkan Api'),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: () => showSosDialog(c),
                            icon: const Icon(Icons.phone_outlined),
                            label: const Text('Posko 112'),
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
      ),
    ],
  );
}

class CitizenProfilePage extends StatelessWidget {
  const CitizenProfilePage({super.key});
  @override
  Widget build(BuildContext c) => ListView(
    padding: const EdgeInsets.all(20),
    children: [
      const CitizenHeader(title: 'Profil'),
      const SizedBox(height: 18),
      const Text(
        'Profil Saya',
        style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800),
      ),
      CitizenCard(
        child: Column(
          children: [
            const CircleAvatar(
              radius: 38,
              backgroundColor: Color(0xFFE4ECFC),
              child: Icon(Icons.person, size: 42, color: sigmaNavy),
            ),
            const SizedBox(height: 10),
            const StatusChip(text: 'Warga Terverifikasi', color: _green),
            Text(
              CitizenDummyData.profile.name,
              style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800),
            ),
            Text(CitizenDummyData.profile.email),
            Text(CitizenDummyData.profile.location),
          ],
        ),
      ),
      const SizedBox(height: 12),
      ...['Edit Profil', 'Bantuan', 'Tentang SIGMA'].map(
        (x) => CitizenCard(
          child: ListTile(
            title: Text(x),
            leading: const Icon(Icons.chevron_right, color: sigmaRed),
            onTap: () => showSnack(c, '$x siap dibuka'),
          ),
        ),
      ),
      CitizenCard(
        child: ListTile(
          leading: const Icon(Icons.logout, color: sigmaRed),
          title: const Text(
            'Keluar',
            style: TextStyle(color: sigmaRed, fontWeight: FontWeight.w800),
          ),
          onTap: () => showDialog(
            context: c,
            builder: (_) => AlertDialog(
              title: const Text('Keluar?'),
              content: const Text('Keluar dari sesi saat ini?'),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(c),
                  child: const Text('Batal'),
                ),
                FilledButton(
                  onPressed: () {
                    Navigator.pop(c);
                    showSnack(c, 'Sesi ditutup');
                  },
                  child: const Text('Keluar'),
                ),
              ],
            ),
          ),
        ),
      ),
    ],
  );
}

class EmergencyVoicePage extends StatefulWidget {
  const EmergencyVoicePage({super.key});
  @override
  State<EmergencyVoicePage> createState() => _EmergencyVoicePageState();
}

class _EmergencyVoicePageState extends State<EmergencyVoicePage> {
  bool recording = false;
  @override
  Widget build(BuildContext c) => Scaffold(
    body: SafeArea(
      child: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          const CitizenHeader(title: 'Bicara Darurat', back: true),
          const SizedBox(height: 18),
          const Text('Suara Anda akan diteruskan bersama lokasi GPS.'),
          const SizedBox(height: 25),
          Center(
            child: GestureDetector(
              onTap: () => setState(() => recording = !recording),
              child: CircleAvatar(
                radius: 95,
                backgroundColor: sigmaRed,
                child: const Icon(Icons.mic, color: Colors.white, size: 62),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Center(
            child: Text(
              recording
                  ? 'Sedang merekam... 00:07'
                  : 'Tekan tombol untuk mulai merekam',
            ),
          ),
          const SizedBox(height: 16),
          const CitizenCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Transkripsi AI Realtime',
                  style: TextStyle(fontWeight: FontWeight.w800),
                ),
                SizedBox(height: 8),
                Text('Saya melihat kepulan asap tebal dari arah kebun sawit.'),
                Wrap(
                  spacing: 6,
                  children: [
                    StatusChip(text: 'Asap Tebal', color: sigmaRed),
                    StatusChip(text: 'Lahan Gambut', color: _orange),
                  ],
                ),
                MiniInfo(
                  icon: Icons.location_on_outlined,
                  label: 'Indramayu, Jawa Barat',
                  value: 'GPS Akurat',
                ),
              ],
            ),
          ),
          EmergencyButton(
            icon: Icons.send,
            title: 'Kirim Laporan Suara',
            subtitle: 'Teruskan ke petugas terdekat',
            onPressed: () => showSnack(c, 'Laporan suara siap dikirim'),
          ),
        ],
      ),
    ),
  );
}

class RadarMap extends StatelessWidget {
  const RadarMap({super.key, this.onTap});
  final VoidCallback? onTap;
  @override
  Widget build(BuildContext c) => GestureDetector(
    onTap: onTap,
    child: Container(
      height: 240,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        gradient: const LinearGradient(
          colors: [Color(0xFFE5F0E4), Color(0xFFD8E9F0)],
        ),
      ),
      child: Stack(
        children: [
          Positioned.fill(child: CustomPaint(painter: _RadarPainter())),
          const Positioned(
            left: 30,
            top: 25,
            child: StatusChip(text: 'Titik Api • 3.2 km', color: sigmaRed),
          ),
          const Positioned(
            right: 25,
            bottom: 30,
            child: StatusChip(text: 'Posko Siaga', color: _green),
          ),
          const Align(
            alignment: Alignment.center,
            child: CircleAvatar(
              radius: 18,
              backgroundColor: sigmaRed,
              child: Icon(Icons.local_fire_department, color: Colors.white),
            ),
          ),
        ],
      ),
    ),
  );
}

class _RadarPainter extends CustomPainter {
  @override
  void paint(Canvas c, Size s) {
    final p = Paint()
      ..color = const Color(0x33D62828)
      ..style = PaintingStyle.fill;
    final center = Offset(s.width * .48, s.height * .48);
    c.drawCircle(center, 68, p);
    p
      ..color = const Color(0x88D62828)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2;
    c.drawCircle(center, 68, p);
    c.drawCircle(center, 100, p);
  }

  @override
  bool shouldRepaint(covariant _RadarPainter old) => false;
}

class CitizenCard extends StatelessWidget {
  const CitizenCard({super.key, required this.child});
  final Widget child;
  @override
  Widget build(BuildContext c) => Container(
    margin: const EdgeInsets.only(bottom: 8),
    padding: const EdgeInsets.all(16),
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      boxShadow: const [
        BoxShadow(
          color: Color(0x0B172033),
          blurRadius: 14,
          offset: Offset(0, 5),
        ),
      ],
    ),
    child: child,
  );
}

class StatusChip extends StatelessWidget {
  const StatusChip({super.key, required this.text, required this.color});
  final String text;
  final Color color;
  @override
  Widget build(BuildContext c) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
    decoration: BoxDecoration(
      color: color.withValues(alpha: .13),
      borderRadius: BorderRadius.circular(20),
    ),
    child: Text(
      text,
      style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.w800),
    ),
  );
}

class MiniInfo extends StatelessWidget {
  const MiniInfo({
    super.key,
    required this.icon,
    required this.label,
    required this.value,
  });
  final IconData icon;
  final String label, value;
  @override
  Widget build(BuildContext c) => Container(
    padding: const EdgeInsets.all(9),
    decoration: BoxDecoration(
      color: const Color(0xFFEAF0FE),
      borderRadius: BorderRadius.circular(10),
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 16, color: sigmaRed),
        const SizedBox(height: 3),
        Text(label, style: const TextStyle(fontSize: 10)),
        Text(
          value,
          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
        ),
      ],
    ),
  );
}

class SectionTitle extends StatelessWidget {
  const SectionTitle({super.key, required this.title, required this.trailing});
  final String title, trailing;
  @override
  Widget build(BuildContext c) => Row(
    children: [
      Expanded(
        child: Text(
          title,
          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
        ),
      ),
      Text(
        trailing,
        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700),
      ),
    ],
  );
}

class EmergencyButton extends StatelessWidget {
  const EmergencyButton({
    super.key,
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onPressed,
  });
  final IconData icon;
  final String title, subtitle;
  final VoidCallback onPressed;
  @override
  Widget build(BuildContext c) => SizedBox(
    width: double.infinity,
    child: FilledButton(
      onPressed: onPressed,
      style: FilledButton.styleFrom(
        backgroundColor: sigmaRed,
        padding: const EdgeInsets.all(16),
      ),
      child: Row(
        children: [
          Icon(icon),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                Text(subtitle, style: const TextStyle(fontSize: 12)),
              ],
            ),
          ),
          const Icon(Icons.chevron_right),
        ],
      ),
    ),
  );
}

class ActionTile extends StatelessWidget {
  const ActionTile({
    super.key,
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.color,
    required this.onTap,
  });
  final IconData icon;
  final String title, subtitle;
  final Color color;
  final VoidCallback onTap;
  @override
  Widget build(BuildContext c) => InkWell(
    onTap: onTap,
    child: Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color),
          const SizedBox(height: 8),
          Text(title, style: const TextStyle(fontWeight: FontWeight.w800)),
          Text(subtitle, style: const TextStyle(fontSize: 11)),
        ],
      ),
    ),
  );
}

void showSnack(BuildContext c, String text) => ScaffoldMessenger.of(c)
    .showSnackBar(
      SnackBar(content: Text(text), behavior: SnackBarBehavior.floating),
    );
void showSosDialog(BuildContext c) => showDialog(
  context: c,
  builder: (_) => AlertDialog(
    title: const Text('Hubungi SOS 112?'),
    content: const Text('Layanan darurat akan dihubungi.'),
    actions: [
      TextButton(onPressed: () => Navigator.pop(c), child: const Text('Batal')),
      FilledButton(
        onPressed: () {
          Navigator.pop(c);
          showSnack(c, 'Permintaan SOS diteruskan');
        },
        child: const Text('Hubungi'),
      ),
    ],
  ),
);
