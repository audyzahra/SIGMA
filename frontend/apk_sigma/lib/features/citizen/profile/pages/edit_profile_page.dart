import 'package:flutter/material.dart';

import '../../../../models/citizen_models.dart';
import '../../../../services/citizen_api.dart';
import '../../widgets/citizen_widgets.dart';

class EditProfilePage extends StatefulWidget {
  const EditProfilePage({super.key, required this.api, required this.profile});
  final CitizenApi api;
  final CitizenProfile profile;
  @override
  State<EditProfilePage> createState() => _EditProfilePageState();
}

class _EditProfilePageState extends State<EditProfilePage> {
  late final TextEditingController _name = TextEditingController(
    text: widget.profile.name,
  );
  final _form = GlobalKey<FormState>();
  bool _saving = false;
  @override
  void dispose() {
    _name.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _saving = true);
    try {
      await widget.api.updateName(_name.text.trim());
      if (mounted) Navigator.pop(context, true);
    } catch (error) {
      if (mounted)
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(error.toString().replaceFirst('Exception: ', '')),
          ),
        );
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    body: SafeArea(
      child: Form(
        key: _form,
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            const CitizenHeader(title: 'Ubah profil', back: true),
            const SizedBox(height: 20),
            TextFormField(
              controller: _name,
              decoration: const InputDecoration(labelText: 'Nama'),
              validator: (value) =>
                  (value ?? '').trim().isEmpty ? 'Nama wajib diisi.' : null,
            ),
            const SizedBox(height: 16),
            FilledButton(
              onPressed: _saving ? null : _save,
              child: Text(_saving ? 'Menyimpan...' : 'Simpan perubahan'),
            ),
          ],
        ),
      ),
    ),
  );
}
