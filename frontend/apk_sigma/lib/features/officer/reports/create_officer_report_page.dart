import 'package:flutter/material.dart';

import '../domain/entities/officer_entities.dart';
import '../state/officer_store.dart';
import '../widgets/officer_page.dart';
import '../widgets/officer_widgets.dart';

class CreateOfficerReportPage extends StatefulWidget {
  const CreateOfficerReportPage({
    super.key,
    required this.store,
    required this.task,
  });

  final OfficerStore store;
  final OfficerTask task;

  @override
  State<CreateOfficerReportPage> createState() =>
      _CreateOfficerReportPageState();
}

class _CreateOfficerReportPageState extends State<CreateOfficerReportPage> {
  final _description = TextEditingController();
  bool _submitting = false;

  @override
  void dispose() {
    _description.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final description = _description.text.trim();

    if (description.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Jelaskan kondisi lapangan terlebih dahulu.'),
        ),
      );
      return;
    }

    setState(() => _submitting = true);

    final sent = await widget.store.createReport(
      widget.task.id,
      description,
    );

    if (!mounted) return;

    setState(() => _submitting = false);

    if (sent) {
      Navigator.pop(context);

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Laporan lapangan berhasil dikirim.'),
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Laporan belum dapat dikirim. Coba lagi.'),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: OfficerPage(
          title: 'Buat Laporan',
          onRefresh: widget.store.load,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              OfficerInfoCard(
                icon: Icons.location_on_outlined,
                text: widget.task.incident.location,
              ),
              const SizedBox(height: 16),
              const Text(
                'Kondisi lapangan',
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                  color: sigmaNavy,
                ),
              ),
              const SizedBox(height: 8),
              TextField(
                controller: _description,
                minLines: 5,
                maxLines: 8,
                maxLength: 5000,
                decoration: const InputDecoration(
                  hintText:
                      'Tuliskan kondisi, tindakan, dan bukti pendukung yang relevan.',
                ),
              ),
              const SizedBox(height: 12),
              SigmaButton(
                label: _submitting ? 'Mengirim...' : 'Kirim Laporan',
                icon: Icons.send_outlined,
                onPressed: _submitting ? () {} : _submit,
              ),
            ],
          ),
        ),
      ),
    );
  }
}