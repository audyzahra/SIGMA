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
  int? _selectedRegionId;

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
    _selectedRegionId = profile.regionId;

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
        regionId: _selectedRegionId,
      );

      if (!mounted) return;

      setState(() {
        _isEditing = false;
        _profile = widget.api.profile();
      });

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Profil berhasil diperbarui')),
      );
    } catch (error) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error.toString().replaceFirst('Exception: ', '')),
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
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 16),
                TextFormField(
                  initialValue: profile.email,
                  enabled: false,
                  decoration: InputDecoration(
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
                  readOnly: true,
                  onTap: _isEditing ? () => _selectRegion(profile) : null,
                  decoration: InputDecoration(
                    labelText: 'Wilayah administratif',
                    prefixIcon: Icon(Icons.location_on_outlined),
                    suffixIcon: Icon(Icons.search),
                    helperText: profile.region == null
                        ? 'Wilayah belum dipilih. Pilih provinsi, kabupaten/kota, dan kecamatan agar peringatan wilayah dapat diterima.'
                        : 'Wilayah ini menentukan peringatan dini yang Anda terima.',
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

  Future<void> _selectRegion(CitizenProfile profile) async {
    final region = await showDialog<CitizenRegion>(
      context: context,
      builder: (_) => _RegionPickerDialog(
        api: widget.api,
        hierarchy: profile.regionHierarchy,
      ),
    );
    if (region == null || !mounted) return;
    setState(() {
      _selectedRegionId = region.id;
      _regionController.text = region.name;
    });
  }

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

class _RegionPickerDialog extends StatefulWidget {
  const _RegionPickerDialog({required this.api, this.hierarchy});

  final CitizenApi api;
  final Map<String, dynamic>? hierarchy;

  @override
  State<_RegionPickerDialog> createState() => _RegionPickerDialogState();
}

class _RegionPickerDialogState extends State<_RegionPickerDialog> {
  List<CitizenRegion> _provinces = const [];
  List<CitizenRegion> _regencies = const [];
  List<CitizenRegion> _districts = const [];
  int? _provinceId;
  int? _regencyId;
  int? _districtId;
  bool _loading = true;
  String? _error;
  int _regionRequest = 0;

  @override
  void initState() {
    super.initState();
    _loadInitial();
  }

  Future<void> _loadInitial() async {
    final request = ++_regionRequest;
    try {
      final provinces = await widget.api.regions(level: 'province');
      final hierarchy = widget.hierarchy;
      List<CitizenRegion> regencies = const [], districts = const [];
      int? provinceId, regencyId, districtId;
      if (hierarchy != null) {
        provinceId = (hierarchy['province_id'] as num?)?.toInt();
        regencyId = (hierarchy['regency_id'] as num?)?.toInt();
        districtId = (hierarchy['district_id'] as num?)?.toInt();
        if (provinceId != null)
          regencies = await widget.api.regions(
            parentId: provinceId,
            level: 'regency',
          );
        if (regencyId != null)
          districts = await widget.api.regions(
            parentId: regencyId,
            level: 'district',
          );
      }
      if (provinceId != null &&
          !provinces.any((region) => region.id == provinceId)) {
        provinceId = null;
        regencyId = null;
        districtId = null;
        regencies = const [];
        districts = const [];
      }
      if (regencyId != null &&
          !regencies.any((region) => region.id == regencyId)) {
        regencyId = null;
        districtId = null;
        districts = const [];
      }
      if (districtId != null &&
          !districts.any((region) => region.id == districtId)) {
        districtId = null;
      }
      if (!mounted || request != _regionRequest) return;
      setState(() {
        _provinces = provinces;
        _regencies = regencies;
        _districts = districts;
        _provinceId = provinceId;
        _regencyId = regencyId;
        _districtId = districtId;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _error = 'Daftar wilayah gagal dimuat. Periksa koneksi lalu coba lagi.';
      });
    }
  }

  Future<void> _selectProvince(int? id) async {
    final request = ++_regionRequest;
    setState(() {
      _provinceId = id;
      _regencyId = null;
      _districtId = null;
      _regencies = const [];
      _districts = const [];
      _loading = true;
      _error = null;
    });
    if (id == null) {
      setState(() => _loading = false);
      return;
    }
    try {
      final items = await widget.api.regions(parentId: id, level: 'regency');
      if (mounted && request == _regionRequest)
        setState(() {
          _regencies = items;
          _loading = false;
        });
    } catch (_) {
      if (mounted && request == _regionRequest)
        setState(() {
          _loading = false;
          _error = 'Kabupaten/kota gagal dimuat. Coba lagi.';
        });
    }
  }

  Future<void> _selectRegency(int? id) async {
    final request = ++_regionRequest;
    setState(() {
      _regencyId = id;
      _districtId = null;
      _districts = const [];
      _loading = true;
      _error = null;
    });
    if (id == null) {
      setState(() => _loading = false);
      return;
    }
    try {
      final items = await widget.api.regions(parentId: id, level: 'district');
      if (mounted && request == _regionRequest)
        setState(() {
          _districts = items;
          _loading = false;
        });
    } catch (_) {
      if (mounted && request == _regionRequest)
        setState(() {
          _loading = false;
          _error = 'Kecamatan gagal dimuat. Coba lagi.';
        });
    }
  }

  void _retry() {
    if (_regencyId != null) {
      _selectRegency(_regencyId);
    } else if (_provinceId != null) {
      _selectProvince(_provinceId);
    } else {
      _loadInitial();
    }
  }

  @override
  Widget build(BuildContext context) => AlertDialog(
    title: const Text('Pilih wilayah'),
    content: SizedBox(
      width: 420,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (_loading) const LinearProgressIndicator(),
          if (_error != null)
            Row(
              children: [
                Expanded(
                  child: Text(
                    _error!,
                    style: const TextStyle(color: Colors.red),
                  ),
                ),
                TextButton(
                  onPressed: _loading ? null : _retry,
                  child: const Text('Coba lagi'),
                ),
              ],
            ),
          DropdownButtonFormField<int>(
            key: ValueKey('province-$_provinceId'),
            initialValue: _provinceId,
            isExpanded: true,
            decoration: const InputDecoration(labelText: 'Provinsi'),
            items: _provinces
                .map(
                  (r) => DropdownMenuItem(
                    value: r.id,
                    child: Text(r.name, overflow: TextOverflow.ellipsis),
                  ),
                )
                .toList(),
            onChanged: _loading ? null : _selectProvince,
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<int>(
            key: ValueKey('regency-$_provinceId-$_regencyId'),
            initialValue: _regencyId,
            isExpanded: true,
            decoration: const InputDecoration(labelText: 'Kabupaten/kota'),
            items: _regencies
                .map(
                  (r) => DropdownMenuItem(
                    value: r.id,
                    child: Text(r.name, overflow: TextOverflow.ellipsis),
                  ),
                )
                .toList(),
            onChanged: _loading || _provinceId == null ? null : _selectRegency,
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<int>(
            key: ValueKey('district-$_regencyId-$_districtId'),
            initialValue: _districtId,
            isExpanded: true,
            decoration: const InputDecoration(labelText: 'Kecamatan'),
            items: _districts
                .map(
                  (r) => DropdownMenuItem(
                    value: r.id,
                    child: Text(r.name, overflow: TextOverflow.ellipsis),
                  ),
                )
                .toList(),
            onChanged: _loading || _regencyId == null
                ? null
                : (id) => setState(() => _districtId = id),
          ),
          const SizedBox(height: 8),
          const Text(
            'Peringatan dini diterima berdasarkan kecamatan pada profil Anda.',
            style: TextStyle(fontSize: 12),
          ),
        ],
      ),
    ),
    actionsAlignment: MainAxisAlignment.spaceBetween,
    buttonPadding: const EdgeInsets.symmetric(horizontal: 16),
    actionsOverflowButtonSpacing: 8,
    actions: [
      TextButton(
        onPressed: () => Navigator.pop(context),
        child: const Text('Batal'),
      ),
      FilledButton(
        onPressed: _districtId == null
            ? null
            : () => Navigator.pop(
                context,
                _districts.firstWhere((r) => r.id == _districtId),
              ),
        child: const Text('Pilih'),
      ),
    ],
  );
}
