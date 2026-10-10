import 'package:flutter/material.dart';

import '../widgets/officer_page.dart';
import '../widgets/officer_widgets.dart';

class OfficerHelpPage extends StatelessWidget {
  const OfficerHelpPage({super.key});

  @override
  Widget build(BuildContext context) {
    return OfficerPage(
      title: 'Bantuan & SOP',
      onRefresh: () async {},
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Pusat Bantuan',
            style: TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w800,
              color: sigmaNavy,
              decoration: TextDecoration.none,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Panduan penggunaan aplikasi dan prosedur kerja petugas SIGMA.',
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w400,
              color: Colors.black54,
              height: 1.5,
              decoration: TextDecoration.none,
            ),
          ),
          const SizedBox(height: 20),

          _HelpSection(
            icon: Icons.assignment_outlined,
            title: 'Panduan Penggunaan',
            subtitle: 'Cara menggunakan fitur aplikasi petugas.',
            items: const [
              _HelpItem(
                title: 'Melihat penugasan',
                description:
                    'Buka menu Penugasan untuk melihat daftar tugas yang diberikan kepada Anda. Pilih salah satu tugas untuk melihat detail lokasi, informasi kejadian, dan instruksi penanganan.',
              ),
              _HelpItem(
                title: 'Memperbarui status penugasan',
                description:
                    'Buka detail penugasan, lalu gunakan tindakan status yang tersedia sesuai kondisi pekerjaan di lapangan. Pastikan status diperbarui sesuai keadaan sebenarnya.',
              ),
              _HelpItem(
                title: 'Membuat laporan',
                description:
                    'Gunakan menu Laporan untuk mencatat kondisi atau hasil penanganan. Isi informasi yang diperlukan dan pastikan laporan sesuai dengan keadaan di lapangan.',
              ),
              _HelpItem(
                title: 'Melihat peta',
                description:
                    'Gunakan menu Peta untuk membantu melihat lokasi dan informasi wilayah yang tersedia pada aplikasi.',
              ),
            ],
          ),
          const SizedBox(height: 16),

          _HelpSection(
            icon: Icons.local_fire_department_outlined,
            title: 'SOP Penanganan Kebakaran',
            subtitle: 'Panduan umum saat menerima dan menangani kejadian.',
            items: const [
              _HelpItem(
                title: '1. Menerima penugasan',
                description:
                    'Periksa detail kejadian, lokasi, tingkat risiko, dan instruksi yang diberikan sebelum menuju lokasi.',
              ),
              _HelpItem(
                title: '2. Persiapan dan perjalanan',
                description:
                    'Pastikan perlengkapan sesuai kebutuhan, koordinasikan keberangkatan dengan pihak terkait, dan utamakan keselamatan selama perjalanan.',
              ),
              _HelpItem(
                title: '3. Penilaian kondisi lapangan',
                description:
                    'Amati kondisi lokasi, potensi penyebaran api, asap, cuaca, serta keberadaan masyarakat yang membutuhkan bantuan. Jangan memasuki area berbahaya tanpa perlindungan dan kewenangan yang sesuai.',
              ),
              _HelpItem(
                title: '4. Penanganan dan koordinasi',
                description:
                    'Laksanakan tindakan sesuai pelatihan, kewenangan, dan SOP resmi instansi. Koordinasikan kebutuhan personel atau bantuan tambahan kepada pihak yang berwenang.',
              ),
              _HelpItem(
                title: '5. Pelaporan hasil',
                description:
                    'Perbarui status penugasan dan catat hasil penanganan, kondisi terakhir, serta informasi pendukung yang diperlukan melalui aplikasi.',
              ),
            ],
          ),
          const SizedBox(height: 16),

          _HelpSection(
            icon: Icons.help_outline,
            title: 'Pertanyaan Umum',
            subtitle: 'Bantuan untuk kendala penggunaan aplikasi.',
            items: const [
              _HelpItem(
                title: 'Penugasan tidak muncul',
                description:
                    'Periksa kembali halaman Penugasan dan koneksi internet. Jika penugasan tetap tidak tersedia, hubungi administrator atau koordinator petugas.',
              ),
              _HelpItem(
                title: 'Status penugasan gagal diperbarui',
                description:
                    'Periksa koneksi internet dan coba kembali. Pastikan Anda masih memiliki akses ke akun. Jika masalah berlanjut, laporkan kendala kepada administrator.',
              ),
              _HelpItem(
                title: 'Laporan gagal dikirim',
                description:
                    'Pastikan informasi wajib telah diisi dan koneksi internet tersedia. Periksa kembali status pengiriman sebelum mencoba mengirim laporan yang sama.',
              ),
              _HelpItem(
                title: 'Lupa kata sandi',
                description:
                    'Hubungi administrator untuk mendapatkan bantuan pemulihan akses akun sesuai prosedur yang berlaku.',
              ),
            ],
          ),
          const SizedBox(height: 16),

          const OfficerInfoCard(
            icon: Icons.info_outline,
            text:
                'Panduan ini merupakan panduan umum penggunaan aplikasi. Untuk tindakan operasional di lapangan, selalu ikuti SOP resmi instansi dan arahan komandan atau koordinator yang berwenang.',
          ),
        ],
      ),
    );
  }
}

class _HelpSection extends StatelessWidget {
  const _HelpSection({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.items,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final List<_HelpItem> items;

  @override
  Widget build(BuildContext context) {
    return SigmaCard(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: sigmaRed.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: sigmaRed, size: 23),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                        color: sigmaNavy,
                        decoration: TextDecoration.none,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      subtitle,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w400,
                        color: Colors.black54,
                        height: 1.4,
                        decoration: TextDecoration.none,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          const Divider(height: 1),
          const SizedBox(height: 4),
          ...items.map(
            (item) => _HelpTile(item: item),
          ),
        ],
      ),
    );
  }
}

class _HelpTile extends StatelessWidget {
  const _HelpTile({required this.item});

  final _HelpItem item;

  @override
  Widget build(BuildContext context) {
    return Theme(
      data: Theme.of(context).copyWith(
        dividerColor: Colors.transparent,
      ),
      child: ExpansionTile(
        tilePadding: EdgeInsets.zero,
        childrenPadding: const EdgeInsets.only(
          left: 4,
          right: 4,
          bottom: 14,
        ),
        shape: const Border(),
        collapsedShape: const Border(),
        iconColor: sigmaRed,
        collapsedIconColor: sigmaNavy,
        title: Text(
          item.title,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w700,
            color: sigmaNavy,
            decoration: TextDecoration.none,
          ),
        ),
        children: [
          Align(
            alignment: Alignment.centerLeft,
            child: Text(
              item.description,
              style: const TextStyle(
                fontSize: 12,
                height: 1.6,
                color: Colors.black87,
                decoration: TextDecoration.none,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _HelpItem {
  const _HelpItem({
    required this.title,
    required this.description,
  });

  final String title;
  final String description;
}