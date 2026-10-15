import 'package:flutter/material.dart';

import '../state/officer_store.dart';
import '../domain/entities/officer_entities.dart';
import '../widgets/officer_page.dart';
import '../widgets/officer_widgets.dart';

class OfficerSettingsPage extends StatelessWidget {
  const OfficerSettingsPage({super.key, required this.store});

  final OfficerStore store;

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: store,
      builder: (context, _) {
        final isOnline = store.connectivity == ConnectivityStatus.online;

        return OfficerPage(
          title: 'Pengaturan',
          onRefresh: store.load,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const OfficerSectionHeader(title: 'Konektivitas'),
              const SizedBox(height: 8),
              SigmaCard(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 6,
                ),
                child: Material(
                  color: Colors.transparent,
                  child: SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    title: const Text(
                      'Status koneksi',
                      style: TextStyle(fontWeight: FontWeight.w700),
                    ),
                    subtitle: Text(
                      isOnline
                          ? 'Aplikasi menggunakan status online.'
                          : 'Aplikasi sedang dalam status offline.',
                    ),
                    value: isOnline,
                    activeColor: sigmaGreen,
                    onChanged: (_) {
                      store.toggleConnectivity();
                    },
                  ),
                ),
              ),
              const SizedBox(height: 18),
              const OfficerSectionHeader(title: 'Data'),
              const SizedBox(height: 8),
              SigmaCard(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 4,
                ),
                child: Material(
                  color: Colors.transparent,
                  child: ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: const Icon(
                      Icons.refresh_outlined,
                      color: sigmaNavy,
                    ),
                    title: const Text(
                      'Muat ulang data',
                      style: TextStyle(fontWeight: FontWeight.w700),
                    ),
                    subtitle: const Text(
                      'Ambil data tugas, laporan, dan profil terbaru.',
                    ),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: store.loading
                        ? null
                        : () async {
                            await store.load();

                            if (!context.mounted) return;

                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text(
                                  'Data petugas berhasil diperbarui.',
                                ),
                              ),
                            );
                          },
                  ),
                ),
              ),
              const SizedBox(height: 18),
              const OfficerInfoCard(
                icon: Icons.info_outline,
                text: 'Pengaturan konektivitas di sini digunakan untuk mengatur status operasional aplikasi petugas. Data dapat dimuat ulang kapan saja untuk mendapatkan informasi terbaru.',
              ),
            ],
          ),
        );
      },
    );
  }
}
