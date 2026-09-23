import 'package:flutter/material.dart';

import '../../data/datasources/citizen_remote_data_source.dart';
import '../../widgets/citizen_widgets.dart';

class ReportCreatePage extends StatefulWidget {
  const ReportCreatePage({super.key, required this.api});
  final CitizenApi api;
  @override
  State<ReportCreatePage> createState() => _ReportCreatePageState();
}

class _ReportCreatePageState extends State<ReportCreatePage> {
  final _form = GlobalKey<FormState>();
  final _lat = TextEditingController();
  final _lng = TextEditingController();
  final _description = TextEditingController();
  String _type = 'fire';
  bool _sending = false;
  @override
  void dispose() {
    _lat.dispose();
    _lng.dispose();
    _description.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _sending = true);
    try {
      final report = await widget.api.submit(
        type: _type,
        latitude: double.parse(_lat.text),
        longitude: double.parse(_lng.text),
        description: _description.text.trim(),
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('${report.number} berhasil dikirim.')),
        );
        Navigator.pop(context);
      }
    } catch (error) {
      if (mounted)
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(error.toString().replaceFirst('Exception: ', '')),
          ),
        );
    } finally {
      if (mounted) setState(() => _sending = false);
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
            const CitizenHeader(title: 'Kirim laporan', back: true),
            const SizedBox(height: 20),
            const Text(
              'Masukkan koordinat kejadian secara akurat. Unggahan foto belum didukung oleh API SIGMA saat ini.',
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField(
              value: _type,
              decoration: const InputDecoration(labelText: 'Jenis laporan'),
              items: const [
                DropdownMenuItem(value: 'fire', child: Text('Kebakaran')),
                DropdownMenuItem(value: 'smoke', child: Text('Asap')),
                DropdownMenuItem(
                  value: 'burning_activity',
                  child: Text('Aktivitas pembakaran'),
                ),
                DropdownMenuItem(value: 'other', child: Text('Lainnya')),
              ],
              onChanged: (value) => setState(() => _type = value!),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _lat,
              keyboardType: const TextInputType.numberWithOptions(
                decimal: true,
                signed: true,
              ),
              decoration: const InputDecoration(labelText: 'Latitude'),
              validator: (value) => double.tryParse(value ?? '') == null
                  ? 'Masukkan latitude yang valid.'
                  : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _lng,
              keyboardType: const TextInputType.numberWithOptions(
                decimal: true,
                signed: true,
              ),
              decoration: const InputDecoration(labelText: 'Longitude'),
              validator: (value) => double.tryParse(value ?? '') == null
                  ? 'Masukkan longitude yang valid.'
                  : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _description,
              maxLength: 5000,
              maxLines: 5,
              decoration: const InputDecoration(
                labelText: 'Keterangan',
                hintText: 'Jelaskan kondisi di lokasi',
              ),
            ),
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: _sending ? null : _submit,
              icon: _sending
                  ? const SizedBox.square(
                      dimension: 18,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Icon(Icons.send),
              label: Text(_sending ? 'Mengirim...' : 'Kirim laporan'),
            ),
          ],
        ),
      ),
    ),
  );
}
