import 'package:flutter/material.dart';

import '../../auth/data/datasources/auth_api.dart';
import '../../auth/data/datasources/auth_storage.dart';
import '../../auth/presentation/pages/role_selection_page.dart';
import '../state/officer_store.dart';
import '../widgets/officer_page.dart';
import '../widgets/officer_widgets.dart';
import 'officer_data_page.dart';
import 'officer_settings_page.dart';
import 'officer_security_page.dart';
import 'officer_help_page.dart';

class OfficerProfilePage extends StatelessWidget {
  const OfficerProfilePage({
    super.key,
    required this.store,
    required this.name,
  });

  final OfficerStore store;
  final String name;

  Future<void> _logout(BuildContext context) async {
    final storage = AuthStorage();
    final token = await storage.getToken();

    if (token != null) {
      try {
        await AuthApi().logout(token);
      } catch (_) {
        // Token lokal tetap dihapus jika request logout gagal.
      }
    }

    await storage.clear();

    if (!context.mounted) return;

    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(
        builder: (_) => const RoleSelectionPage(),
      ),
      (_) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    return OfficerPage(
      title: 'Profil',
      onRefresh: store.load,
      child: Column(
        children: [
          SigmaCard(
            child: Column(
              children: [
                const CircleAvatar(
                  radius: 32,
                  child: Icon(Icons.person_outline, size: 34),
                ),
                const SizedBox(height: 10),
                Text(
                  store.profile?.name ?? name,
                  style: const TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 20,
                  ),
                ),
                const SizedBox(height: 4),
                Text(store.profile?.role ?? 'Petugas'),
                const SizedBox(height: 10),
                OfficerInfoCard(
                  icon: Icons.groups_outlined,
                  text: store.profile?.team ?? 'Tim belum tersedia',
                ),
                const SizedBox(height: 8),
                OfficerInfoCard(
                  icon: Icons.business_outlined,
                  text: store.profile?.organization ??
                      'Organisasi belum tersedia',
                ),
                const SizedBox(height: 8),
                OfficerInfoCard(
                  icon: Icons.circle,
                  text: store.profile?.online == true
                      ? 'Status online'
                      : 'Status belum tersedia',
                  color: store.profile?.online == true
                      ? const Color(0xFFE2F6ED)
                      : sigmaBlue,
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),

          ...[
            'Data Saya',
            'Pengaturan',
            'Keamanan',
            'Bantuan & SOP',
          ].map(
            (label) => Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: SigmaCard(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 2,
                ),
                child: ListTile(
                  title: Text(label),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () {
                    Widget? page;

                    switch (label) {
                      case 'Data Saya':
                        page = OfficerDataPage(
                          store: store,
                          name: name,
                        );
                      case 'Pengaturan':
                        page = OfficerSettingsPage(store: store);
                      case 'Keamanan':
                        page = OfficerSecurityPage(store: store);
                      case 'Bantuan & SOP':
                        page = const OfficerHelpPage();
                    }

                    if (page != null) {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => page!,
                        ),
                      );
                    }
                  },
                ),
              ),
            ),
          ),

          const SizedBox(height: 8),

          SigmaCard(
            padding: const EdgeInsets.symmetric(
              horizontal: 14,
              vertical: 2,
            ),
            child: ListTile(
              leading: const Icon(
                Icons.logout,
                color: sigmaRed,
              ),
              title: const Text(
                'Keluar',
                style: TextStyle(
                  color: sigmaRed,
                  decoration: TextDecoration.none,
                ),
              ),
              onTap: () => _logout(context),
            ),
          ),
        ],
      ),
    );
  }
}