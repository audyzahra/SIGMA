import 'package:flutter/material.dart';

import '../state/officer_store.dart';
import '../widgets/officer_page.dart';
import '../widgets/officer_widgets.dart';

class OfficerDataPage extends StatelessWidget {
  const OfficerDataPage({super.key, required this.store, required this.name});

  final OfficerStore store;
  final String name;

  @override
  Widget build(BuildContext context) {
    final profile = store.profile;

    return OfficerPage(
      title: 'Data Saya',
      onRefresh: store.load,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SigmaCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Center(
                  child: CircleAvatar(
                    radius: 36,
                    child: Icon(Icons.person_outline, size: 38),
                  ),
                ),
                const SizedBox(height: 18),
                _DataItem(
                  icon: Icons.person_outline,
                  label: 'Nama',
                  value: profile?.name ?? name,
                ),
                _DataItem(
                  icon: Icons.email_outlined,
                  label: 'Email',
                  value: profile?.email.isNotEmpty == true
                      ? profile!.email
                      : 'Belum tersedia',
                ),
                _DataItem(
                  icon: Icons.badge_outlined,
                  label: 'Peran',
                  value: profile?.role ?? 'Petugas',
                ),
                _DataItem(
                  icon: Icons.groups_outlined,
                  label: 'Tim',
                  value: profile?.team ?? 'Belum tersedia',
                ),
                _DataItem(
                  icon: Icons.business_outlined,
                  label: 'Organisasi',
                  value: profile?.organization ?? 'Belum tersedia',
                ),
                _DataItem(
                  icon: Icons.circle_outlined,
                  label: 'Status',
                  value: profile?.online == true ? 'Online' : 'Offline',
                ),
                if (profile?.lastSeenAt != null)
                  _DataItem(
                    icon: Icons.access_time_outlined,
                    label: 'Terakhir aktif',
                    value: profile!.lastSeenAt.toString(),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _DataItem extends StatelessWidget {
  const _DataItem({
    required this.icon,
    required this.label,
    required this.value,
  });

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 20, color: sigmaNavy),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 12,
                    color: Colors.grey,
                    decoration: TextDecoration.none,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                    color: sigmaRed,
                    decoration: TextDecoration.none,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
