import 'package:flutter/material.dart';

import '../../../../core/theme/sigma_theme.dart';
import '../../../../models/citizen_models.dart';
import '../../../../pages/auth/role_selection_page.dart';
import '../../../../services/auth_api.dart';
import '../../../../services/auth_storage.dart';
import '../../../../services/citizen_api.dart';
import '../../widgets/citizen_widgets.dart';
import 'edit_profile_page.dart';

class ProfilePage extends StatefulWidget {
  const ProfilePage({super.key, required this.api});
  final CitizenApi api;
  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  late Future<CitizenProfile> _profile = widget.api.profile();
  @override
  Widget build(BuildContext context) => FutureBuilder<CitizenProfile>(
    future: _profile,
    builder: (context, snapshot) {
      if (snapshot.connectionState != ConnectionState.done)
        return const PageLoading();
      if (snapshot.hasError)
        return PageError(
          onRetry: () => setState(() => _profile = widget.api.profile()),
        );
      final profile = snapshot.data!;
      return ListView(
        padding: const EdgeInsets.all(20),
        children: [
          const CitizenHeader(title: 'Profil'),
          const SizedBox(height: 18),
          CitizenSurface(
            child: Column(
              children: [
                const CircleAvatar(
                  radius: 34,
                  backgroundColor: Color(0xFFFFE5E5),
                  child: Icon(
                    Icons.person,
                    size: 38,
                    color: SigmaColors.primary,
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  profile.name,
                  style: const TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                Text(profile.email),
                const SizedBox(height: 8),
                StatusBadge(
                  label: profile.verified
                      ? 'Email terverifikasi'
                      : 'Email belum diverifikasi',
                  color: profile.verified
                      ? SigmaColors.success
                      : SigmaColors.secondary,
                ),
                if (profile.region != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: Text(profile.region!),
                  ),
              ],
            ),
          ),
          CitizenSurface(
            child: ListTile(
              leading: const Icon(Icons.edit_outlined),
              title: const Text('Ubah nama'),
              onTap: () async {
                final changed = await Navigator.push<bool>(
                  context,
                  MaterialPageRoute(
                    builder: (_) =>
                        EditProfilePage(api: widget.api, profile: profile),
                  ),
                );
                if (changed == true && mounted)
                  setState(() => _profile = widget.api.profile());
              },
            ),
          ),
          CitizenSurface(
            child: const ListTile(
              leading: Icon(Icons.info_outline),
              title: Text('Data profil'),
              subtitle: Text(
                'Email, wilayah, dan nomor telepon tidak dapat diubah karena belum tersedia pada schema akun.',
              ),
            ),
          ),
          CitizenSurface(
            child: ListTile(
              leading: const Icon(Icons.logout, color: SigmaColors.primary),
              title: const Text(
                'Keluar',
                style: TextStyle(color: SigmaColors.primary),
              ),
              onTap: _logout,
            ),
          ),
        ],
      );
    },
  );
  Future<void> _logout() async {
    final token = await AuthStorage().getToken();
    if (token != null) {
      try {
        await AuthApi().logout(token);
      } catch (_) {}
    }
    await AuthStorage().clear();
    if (mounted)
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const RoleSelectionPage()),
        (_) => false,
      );
  }
}
