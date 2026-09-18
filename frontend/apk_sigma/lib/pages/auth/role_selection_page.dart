import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'auth_widgets.dart';
import 'login_page.dart';

class RoleSelectionPage extends StatelessWidget {
  const RoleSelectionPage({super.key});

  Future<void> _select(BuildContext context, String role) async {
    final preferences = await SharedPreferences.getInstance();
    await preferences.setString('selectedRole', role);
    if (!context.mounted) return;
    Navigator.of(context)
        .push(MaterialPageRoute(builder: (_) => LoginPage(role: role)));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(24, 36, 24, 24),
          child: Column(
            children: [
              const SigmaHeader(subtitle: 'Disaster Intelligence System'),
              const SizedBox(height: 12),
              const Text(
                'Pilih akses anda',
                style: TextStyle(
                  color: sigmaNavy,
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 28),
              _RoleCard(
                icon: Icons.person_outline,
                title: 'Masyarakat',
                description: 'Laporkan kejadian kebakaran dan dapatkan informasi wilayah',
                buttonLabel: 'Masuk sebagai Masyarakat',
                onPressed: () => _select(context, 'citizen'),
              ),
              const SizedBox(height: 16),
              _RoleCard(
                icon: Icons.shield_outlined,
                title: 'Petugas',
                description: 'Kelola laporan dan respon lapangan',
                buttonLabel: 'Masuk sebagai Petugas',
                onPressed: () => _select(context, 'officer'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _RoleCard extends StatelessWidget {
  const _RoleCard({
    required this.icon,
    required this.title,
    required this.description,
    required this.buttonLabel,
    required this.onPressed,
  });
  final IconData icon;
  final String title;
  final String description;
  final String buttonLabel;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) => Card(
    elevation: 3,
    shadowColor: Colors.black12,
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
    child: Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: sigmaRed, size: 34),
          const SizedBox(height: 12),
          Text(
            title,
            style: const TextStyle(
              color: sigmaNavy,
              fontSize: 20,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            description,
            style: const TextStyle(color: Colors.black54, height: 1.4),
          ),
          const SizedBox(height: 16),
          SigmaButton(label: buttonLabel, onPressed: onPressed),
        ],
      ),
    ),
  );
}
