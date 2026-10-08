import 'package:flutter/material.dart';

import '../../../../core/theme/sigma_theme.dart';
import '../../data/models/citizen_models.dart';
import '../../../auth/presentation/pages/role_selection_page.dart';
import '../../../auth/data/datasources/auth_api.dart';
import '../../../auth/data/datasources/auth_storage.dart';
import '../../data/datasources/citizen_remote_data_source.dart';
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

  final _phoneController = TextEditingController();
  final _regionController = TextEditingController();

  bool _isEditing = false;
  bool _isSaving = false;
  String _currentName = '';

  @override
  void dispose() {
    _phoneController.dispose();
    _regionController.dispose();
    super.dispose();
  }

  void _startEditing(CitizenProfile profile) {
  _currentName = profile.name;
  _phoneController.text = profile.phone ?? '';
  _regionController.text = profile.region ?? '';

  setState(() {
    _isEditing = true;
  });
}

  void _cancelEditing() {
    setState(() {
      _isEditing = false;
    });
  }

  Future<void> _saveProfile() async {
    if (_isSaving) return;

    setState(() {
      _isSaving = true;
    });

    try {
      await widget.api.updateProfile(
  name: _currentName,
  phone: _phoneController.text.trim(),
  region: _regionController.text.trim(),
);

      if (!mounted) return;

      setState(() {
        _isEditing = false;
        _profile = widget.api.profile();
      });

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Profil berhasil diperbarui'),
        ),
      );
    } catch (error) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            error.toString().replaceFirst('Exception: ', ''),
          ),
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isSaving = false;
        });
      }
    }
  }
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
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Data profil',
                style: TextStyle(
                  fontSize: 17,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 16),
              TextFormField(
                initialValue: profile.email,
                enabled: false,
                decoration: const InputDecoration(
                  labelText: 'Email',
                  prefixIcon: Icon(Icons.email_outlined),
                  helperText: 'Email tidak dapat diubah',
                ),
              ),
              const SizedBox(height: 14),
              TextFormField(
                controller: _phoneController,
                enabled: _isEditing,
                keyboardType: TextInputType.phone,
                decoration: const InputDecoration(
                  labelText: 'Nomor Telepon',
                  prefixIcon: Icon(Icons.phone_outlined),
                ),
              ),
              const SizedBox(height: 14),
              TextFormField(
                controller: _regionController,
                enabled: _isEditing,
                decoration: const InputDecoration(
                  labelText: 'Alamat',
                  prefixIcon: Icon(Icons.location_on_outlined),
                ),
              ),
              const SizedBox(height: 20),
              if (!_isEditing)
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () => _startEditing(profile),
                    icon: const Icon(Icons.edit_outlined),
                    label: const Text('Ubah data'),
                  ),
                )
              else
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: _isSaving ? null : _cancelEditing,
                        child: const Text('Batal'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: _isSaving ? null : _saveProfile,
                        child: _isSaving
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                ),
                              )
                            : const Text('Simpan'),
                      ),
                    ),
                  ],
                ),
            ],
          ),
        ),
                    CitizenSurface(
            child: ValueListenableBuilder<ThemeMode>(
              valueListenable: SigmaThemeController.mode,
              builder: (context, mode, _) {
                return Row(
                  children: [
                    Expanded(
                      child: Text(
                        'Tema',
                        style: TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.w700,
                          color: Theme.of(context).colorScheme.onSurface,
                        ),
                      ),
                    ),
                    IconButton(
                      tooltip: 'Tema terang',
                      onPressed: () {
                        SigmaThemeController.setMode(ThemeMode.light);
                      },
                      icon: Icon(
                        Icons.light_mode_outlined,
                        color: mode == ThemeMode.light
                            ? SigmaColors.primary
                            : Theme.of(context).colorScheme.onSurfaceVariant,
                      ),
                    ),
                    IconButton(
                      tooltip: 'Tema gelap',
                      onPressed: () {
                        SigmaThemeController.setMode(ThemeMode.dark);
                      },
                      icon: Icon(
                        Icons.dark_mode_outlined,
                        color: mode == ThemeMode.dark
                            ? SigmaColors.primary
                            : Theme.of(context).colorScheme.onSurfaceVariant,
                      ),
                    ),
                  ],
                );
              },
            ),
          ),
          CitizenSurface(
            child: ListTile(
              leading: const Icon(
                Icons.logout,
                color: SigmaColors.primary,
              ),
              title: const Text(
                'Keluar',
                style: TextStyle(
                  color: SigmaColors.primary,
                ),
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
