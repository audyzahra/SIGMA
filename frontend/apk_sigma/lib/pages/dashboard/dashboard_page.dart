import 'package:flutter/material.dart';

import '../auth/auth_widgets.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key, required this.role, required this.name});

  final String role;
  final String name;

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  var _selectedIndex = 0;

  bool get _isOfficer => widget.role == 'officer';

  @override
  Widget build(BuildContext context) {
    final items = _isOfficer
        ? const [
            NavigationDestination(
              icon: Icon(Icons.home_outlined),
              label: 'Beranda',
            ),
            NavigationDestination(
              icon: Icon(Icons.assignment_outlined),
              label: 'Laporan',
            ),
            NavigationDestination(
              icon: Icon(Icons.map_outlined),
              label: 'Peta',
            ),
            NavigationDestination(
              icon: Icon(Icons.person_outline),
              label: 'Profil',
            ),
          ]
        : const [
            NavigationDestination(
              icon: Icon(Icons.home_outlined),
              label: 'Beranda',
            ),
            NavigationDestination(
              icon: Icon(Icons.map_outlined),
              label: 'Peta',
            ),
            NavigationDestination(
              icon: Icon(Icons.description_outlined),
              label: 'Laporan',
            ),
            NavigationDestination(
              icon: Icon(Icons.person_outline),
              label: 'Profil',
            ),
          ];

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: Image.asset(
                'assets/images/logo.jpeg',
                width: 32,
                height: 32,
              ),
            ),
            const SizedBox(width: 10),
            const Text('SIGMA'),
          ],
        ),
        backgroundColor: Colors.white,
        foregroundColor: sigmaNavy,
        elevation: 0,
      ),
      body: SafeArea(
        child: _selectedIndex == 0
            ? _isOfficer
                  ? _OfficerOverview(name: widget.name)
                  : _CitizenOverview(name: widget.name)
            : _PlaceholderPage(label: items[_selectedIndex].label),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedIndex,
        onDestinationSelected: (index) =>
            setState(() => _selectedIndex = index),
        destinations: items,
      ),
    );
  }
}

class _CitizenOverview extends StatelessWidget {
  const _CitizenOverview({required this.name});
  final String name;

  @override
  Widget build(BuildContext context) => ListView(
    padding: const EdgeInsets.all(20),
    children: [
      Text(
        'Halo, $name',
        style: const TextStyle(
          fontSize: 24,
          fontWeight: FontWeight.w800,
          color: sigmaNavy,
        ),
      ),
      const SizedBox(height: 4),
      const Text('Pantau dan laporkan kondisi karhutla di wilayah Anda.'),
      const SizedBox(height: 20),
      const _StatusBanner(text: 'Sistem operasional', color: Color(0xFF16A34A)),
      const SizedBox(height: 20),
      const _MenuCard(
        icon: Icons.local_fire_department_outlined,
        title: 'Laporkan Kebakaran',
        subtitle: 'Buat laporan kejadian',
        color: sigmaRed,
      ),
      const _MenuCard(
        icon: Icons.location_on_outlined,
        title: 'Peta Risiko',
        subtitle: 'Lihat kondisi wilayah',
        color: Color(0xFFF97316),
      ),
      const _MenuCard(
        icon: Icons.article_outlined,
        title: 'Riwayat Laporan',
        subtitle: 'Pantau laporan Anda',
        color: Color(0xFF2563EB),
      ),
      const _MenuCard(
        icon: Icons.notifications_none,
        title: 'Notifikasi',
        subtitle: 'Informasi terbaru',
        color: Color(0xFF7C3AED),
      ),
    ],
  );
}

class _OfficerOverview extends StatelessWidget {
  const _OfficerOverview({required this.name});
  final String name;

  @override
  Widget build(BuildContext context) => ListView(
    padding: const EdgeInsets.all(20),
    children: [
      Text(
        'Halo, $name',
        style: const TextStyle(
          fontSize: 24,
          fontWeight: FontWeight.w800,
          color: sigmaNavy,
        ),
      ),
      const SizedBox(height: 4),
      const Text('Ringkasan penanganan karhutla hari ini.'),
      const SizedBox(height: 20),
      const Row(
        children: [
          Expanded(
            child: _StatCard(
              label: 'Laporan Baru',
              value: '12',
              color: sigmaRed,
            ),
          ),
          SizedBox(width: 10),
          Expanded(
            child: _StatCard(
              label: 'Ditangani',
              value: '7',
              color: Color(0xFFF97316),
            ),
          ),
          SizedBox(width: 10),
          Expanded(
            child: _StatCard(
              label: 'Selesai',
              value: '28',
              color: Color(0xFF16A34A),
            ),
          ),
        ],
      ),
      const SizedBox(height: 20),
      const _MenuCard(
        icon: Icons.assignment_outlined,
        title: 'Laporan Masuk',
        subtitle: 'Tinjau laporan prioritas',
        color: sigmaRed,
      ),
      const _MenuCard(
        icon: Icons.health_and_safety_outlined,
        title: 'Penanganan',
        subtitle: 'Kelola status penanganan',
        color: Color(0xFFF97316),
      ),
      const _MenuCard(
        icon: Icons.map_outlined,
        title: 'Peta Wilayah',
        subtitle: 'Pantau area tugas',
        color: Color(0xFF2563EB),
      ),
    ],
  );
}

class _StatusBanner extends StatelessWidget {
  const _StatusBanner({required this.text, required this.color});
  final String text;
  final Color color;
  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.all(14),
    decoration: BoxDecoration(
      color: color.withValues(alpha: .1),
      borderRadius: BorderRadius.circular(14),
    ),
    child: Row(
      children: [
        Icon(Icons.circle, color: color, size: 12),
        const SizedBox(width: 8),
        Text(
          text,
          style: TextStyle(color: color, fontWeight: FontWeight.w700),
        ),
      ],
    ),
  );
}

class _MenuCard extends StatelessWidget {
  const _MenuCard({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.color,
  });
  final IconData icon;
  final String title;
  final String subtitle;
  final Color color;
  @override
  Widget build(BuildContext context) => Card(
    child: ListTile(
      contentPadding: const EdgeInsets.all(14),
      leading: CircleAvatar(
        backgroundColor: color.withValues(alpha: .12),
        foregroundColor: color,
        child: Icon(icon),
      ),
      title: Text(title, style: const TextStyle(fontWeight: FontWeight.w700)),
      subtitle: Text(subtitle),
      trailing: const Icon(Icons.chevron_right),
    ),
  );
}

class _StatCard extends StatelessWidget {
  const _StatCard({
    required this.label,
    required this.value,
    required this.color,
  });
  final String label;
  final String value;
  final Color color;
  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.all(12),
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(14),
      border: Border.all(color: color.withValues(alpha: .3)),
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          value,
          style: TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.w800,
            color: color,
          ),
        ),
        const SizedBox(height: 4),
        Text(label, style: const TextStyle(fontSize: 11)),
      ],
    ),
  );
}

class _PlaceholderPage extends StatelessWidget {
  const _PlaceholderPage({required this.label});
  final String label;
  @override
  Widget build(BuildContext context) => Center(
    child: Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(Icons.construction_outlined, size: 48, color: sigmaRed),
        const SizedBox(height: 12),
        Text(
          label,
          style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
        ),
        const SizedBox(height: 4),
        const Text('Menu ini siap dikembangkan.'),
      ],
    ),
  );
}
