import 'package:flutter/material.dart';

import '../state/officer_store.dart';
import '../widgets/officer_page.dart';
import '../widgets/officer_widgets.dart';

class OfficerMapPage extends StatelessWidget {
  const OfficerMapPage({super.key, required this.store});

  final OfficerStore store;

  @override
  Widget build(BuildContext context) {
    if (store.tasks.isEmpty) {
      return OfficerPage(
        title: 'Peta',
        onRefresh: store.load,
        child: OfficerEmptyState(
          icon: Icons.map_outlined,
          title: 'Belum ada titik pemantauan',
          description: 'Belum tersedia hotspot, insiden, atau tugas yang dapat ditampilkan pada peta.',
          detail: 'Sumber data: tugas lapangan, insiden, dan hotspot. Data akan muncul otomatis ketika tersedia.',
          actionLabel: 'Muat ulang',
          onAction: store.load,
        ),
      );
    }

    return OfficerPage(
      title: 'Peta',
      onRefresh: store.load,
      child: Column(
        children: store.tasks.map((task) {
          final incident = task.incident;

          return SigmaCard(
            child: ListTile(
              leading: const Icon(Icons.location_on_outlined, color: sigmaRed),
              title: Text(incident.location),
              subtitle: Text('${incident.latitude}, ${incident.longitude}'),
            ),
          );
        }).toList(),
      ),
    );
  }
}