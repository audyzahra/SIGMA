import 'package:flutter/material.dart';

import '../../domain/entities/officer_entities.dart';
import 'officer_store.dart';
import 'officer_widgets.dart';

class OfficerShell extends StatefulWidget {
  const OfficerShell({super.key, required this.name});
  final String name;
  @override
  State<OfficerShell> createState() => _OfficerShellState();
}

class _OfficerShellState extends State<OfficerShell> {
  final store = OfficerStore();
  int tab = 0;
  @override
  void initState() {
    super.initState();
    store.load();
  }

  @override
  void dispose() {
    store.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext c) => AnimatedBuilder(
    animation: store,
    builder: (c, child) {
      if (!store.loaded) {
        return const Scaffold(body: Center(child: CircularProgressIndicator()));
      }
      final pages = [
        OfficerHome(
          store: store,
          name: widget.name,
          go: (i) => setState(() => tab = i),
        ),
        OfficerMap(store: store),
        OfficerTasks(store: store),
        OfficerReports(store: store),
        OfficerProfile(store: store),
      ];
      return Scaffold(
        body: SafeArea(child: pages[tab]),
        bottomNavigationBar: NavigationBar(
          selectedIndex: tab,
          onDestinationSelected: (i) => setState(() => tab = i),
          indicatorColor: const Color(0x22C82828),
          destinations: const [
            NavigationDestination(
              icon: Icon(Icons.home_outlined),
              selectedIcon: Icon(Icons.home),
              label: 'Beranda',
            ),
            NavigationDestination(
              icon: Icon(Icons.map_outlined),
              selectedIcon: Icon(Icons.map),
              label: 'Peta',
            ),
            NavigationDestination(
              icon: Icon(Icons.assignment_outlined),
              selectedIcon: Icon(Icons.assignment),
              label: 'Tugas',
            ),
            NavigationDestination(
              icon: Icon(Icons.description_outlined),
              selectedIcon: Icon(Icons.description),
              label: 'Laporan',
            ),
            NavigationDestination(
              icon: Icon(Icons.person_outline),
              selectedIcon: Icon(Icons.person),
              label: 'Profil',
            ),
          ],
        ),
      );
    },
  );
}

class Page extends StatelessWidget {
  const Page({super.key, required this.child});
  final Widget child;
  @override
  Widget build(BuildContext c) => ListView(
    padding: const EdgeInsets.fromLTRB(16, 14, 16, 24),
    children: [child],
  );
}

class OfficerHome extends StatelessWidget {
  const OfficerHome({
    super.key,
    required this.store,
    required this.name,
    required this.go,
  });
  final OfficerStore store;
  final String name;
  final ValueChanged<int> go;
  @override
  Widget build(BuildContext c) {
    return Page(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SigmaHeader(
            title: 'Beranda',
            online: store.connectivity == ConnectivityStatus.online,
          ),
          const SizedBox(height: 16),
          OfflineBanner(store: store),
          SigmaCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Halo, $name',
                  style: const TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w800,
                    color: sigmaNavy,
                  ),
                ),
                const Text('Regu C • Siap bertugas hari ini'),
                const SizedBox(height: 10),
                const SigmaChip(text: 'ONLINE / SIAGA'),
              ],
            ),
          ),
          const SizedBox(height: 14),
          const Row(
            children: [
              Metric(
                label: 'Tugas Aktif',
                value: '2',
                icon: Icons.assignment_outlined,
              ),
              SizedBox(width: 8),
              Metric(
                label: 'Insiden Dekat',
                value: '1',
                icon: Icons.local_fire_department,
                color: sigmaRed,
              ),
            ],
          ),
          const SizedBox(height: 14),
          IncidentCard(
            store: store,
            onDetail: () => push(c, TaskDetail(store: store)),
          ),
          const SizedBox(height: 14),
          SigmaCard(
            color: const Color(0xFFFFF4EA),
            child: const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Rekomendasi AI',
                  style: TextStyle(fontWeight: FontWeight.w800),
                ),
                SizedBox(height: 5),
                Text(
                  'Risiko merambat ke timur. Pendekatan aman dari sisi barat.',
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class OfficerTasks extends StatelessWidget {
  const OfficerTasks({super.key, required this.store});
  final OfficerStore store;
  @override
  Widget build(BuildContext c) => Page(
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SigmaHeader(
          title: 'Tugas',
          online: store.connectivity == ConnectivityStatus.online,
        ),
        const SizedBox(height: 14),
        const Text(
          'Tugas Saya',
          style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800),
        ),
        const SizedBox(height: 10),
        ...store.tasks.map(
          (t) => Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: TaskCard(task: t, store: store),
          ),
        ),
      ],
    ),
  );
}

class TaskCard extends StatelessWidget {
  const TaskCard({super.key, required this.task, required this.store});
  final OfficerTask task;
  final OfficerStore store;
  @override
  Widget build(BuildContext c) {
    final i = task.incident;
    return SigmaCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.local_fire_department, color: sigmaRed),
              const SizedBox(width: 7),
              Expanded(
                child: Text(
                  i.title,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
              SigmaChip(text: i.priority, color: sigmaRed),
            ],
          ),
          const SizedBox(height: 6),
          Text(i.location),
          const SizedBox(height: 8),
          SigmaChip(
            text: statusLabel(task.status),
            color: statusColor(task.status),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Metric(
                label: 'Risiko',
                value: '${i.riskIndex}/100',
                icon: Icons.warning_amber,
              ),
              const SizedBox(width: 8),
              Metric(
                label: 'Jarak',
                value: '${i.distanceKm} km',
                icon: Icons.near_me,
                color: sigmaOrange,
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: SigmaButton(
                  label: 'Rute',
                  secondary: true,
                  onPressed: () => push(c, FieldNavigation(store: store)),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: SigmaButton(
                  label: 'Detail',
                  onPressed: () => push(c, TaskDetail(store: store)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class TaskDetail extends StatelessWidget {
  const TaskDetail({super.key, required this.store});
  final OfficerStore store;
  @override
  Widget build(BuildContext c) {
    final t = store.activeTask;
    final i = t.incident;
    final next = nextStatus(t.status);
    return Scaffold(
      body: SafeArea(
        child: Page(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SigmaHeader(
                title: 'Detail Tugas',
                subtitle: 'Incident KH-0102',
                back: true,
              ),
              const SizedBox(height: 14),
              Text(
                i.title,
                style: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w800,
                ),
              ),
              Text(i.location),
              const SizedBox(height: 12),
              MockMap(
                height: 210,
                onTap: () => push(c, LocationDetail(store: store)),
              ),
              const SizedBox(height: 12),
              SigmaCard(
                child: Column(
                  children: [
                    row('Koordinat', '${i.latitude}, ${i.longitude}'),
                    row('Sumber air', i.waterSource),
                    row('Angin', i.wind),
                    row('Status', statusLabel(t.status)),
                  ],
                ),
              ),
              const SizedBox(height: 12),
              if (next != null)
                SigmaButton(
                  label: actionLabel(next),
                  icon: Icons.play_arrow,
                  onPressed: () async {
                    await store.setTaskStatus(next);
                    if (!c.mounted) return;
                    if (next == TaskStatus.enRoute) {
                      push(c, FieldNavigation(store: store));
                    } else if (next == TaskStatus.arrived) {
                      push(c, LocationDetail(store: store));
                    } else if (next == TaskStatus.handling) {
                      push(c, ProgressHandling(store: store));
                    }
                  },
                ),
              const SizedBox(height: 8),
              SigmaButton(
                label: 'Buat Laporan',
                secondary: true,
                onPressed: () => push(c, CreateReport(store: store)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class OfficerMap extends StatelessWidget {
  const OfficerMap({super.key, required this.store});
  final OfficerStore store;
  @override
  Widget build(BuildContext c) {
    final i = store.activeTask.incident;
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 14, 16, 8),
          child: SigmaHeader(
            title: 'Peta',
            online: store.connectivity == ConnectivityStatus.online,
          ),
        ),
        Expanded(
          child: Stack(
            children: [
              Positioned.fill(
                child: MockMap(
                  height: 800,
                  onTap: () => push(c, LocationDetail(store: store)),
                ),
              ),
              Positioned(
                left: 16,
                right: 16,
                top: 10,
                child: const TextField(
                  decoration: InputDecoration(
                    prefixIcon: Icon(Icons.search),
                    hintText: 'Cari lokasi, hotspot, kanal...',
                  ),
                ),
              ),
              Positioned(
                left: 16,
                right: 16,
                bottom: 16,
                child: SigmaCard(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        i.title,
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      Text(i.location),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Expanded(
                            child: SigmaButton(
                              label: 'Detail Lokasi',
                              secondary: true,
                              onPressed: () =>
                                  push(c, LocationDetail(store: store)),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: SigmaButton(
                              label: 'Pandu Rute',
                              onPressed: () =>
                                  push(c, FieldNavigation(store: store)),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class LocationDetail extends StatelessWidget {
  const LocationDetail({super.key, required this.store});
  final OfficerStore store;
  @override
  Widget build(BuildContext c) {
    final i = store.activeTask.incident;
    return Scaffold(
      body: SafeArea(
        child: Page(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SigmaHeader(
                title: 'Detail Lokasi Kebakaran',
                subtitle: 'KH-0102',
                back: true,
              ),
              const SizedBox(height: 12),
              MockMap(height: 260),
              const SizedBox(height: 12),
              Text(
                i.title,
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w800,
                ),
              ),
              Text(i.location),
              const SizedBox(height: 10),
              SigmaCard(
                child: Column(
                  children: [
                    row('Titik api', '${i.latitude}, ${i.longitude}'),
                    row('Sumber air', i.waterSource),
                    row('Pemukiman', '1,1 km arah timur'),
                    row('Arah angin', i.wind),
                    row('Posisi officer', '2,5 km barat daya'),
                  ],
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: SigmaButton(
                      label: 'AI Verifikasi',
                      secondary: true,
                      onPressed: () => push(c, AIVerification(store: store)),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: SigmaButton(
                      label: 'Progres',
                      onPressed: () => push(c, ProgressHandling(store: store)),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class FieldNavigation extends StatelessWidget {
  const FieldNavigation({super.key, required this.store});
  final OfficerStore store;
  @override
  Widget build(BuildContext c) {
    final i = store.activeTask.incident;
    return Scaffold(
      body: SafeArea(
        child: Stack(
          children: [
            const Positioned.fill(child: MockMap(height: 800, route: true)),
            Positioned(
              left: 16,
              right: 16,
              top: 14,
              child: SigmaCard(
                color: const Color(0xEE172033),
                child: const Row(
                  children: [
                    Icon(Icons.navigation, color: sigmaOrange),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'NAVIGASI LAPANGAN',
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                    SigmaChip(text: 'GPS AKTIF'),
                  ],
                ),
              ),
            ),
            Positioned(
              left: 16,
              right: 16,
              top: 78,
              child: SigmaCard(
                child: const Row(
                  children: [
                    Icon(Icons.turn_right, color: sigmaRed, size: 42),
                    SizedBox(width: 12),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '300 meter',
                          style: TextStyle(
                            fontSize: 26,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        Text('Belok kanan ke Jalur Kanal B'),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            Positioned(
              left: 16,
              right: 16,
              bottom: 18,
              child: SigmaCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      i.title,
                      style: const TextStyle(fontWeight: FontWeight.w800),
                    ),
                    Text(
                      '${i.distanceKm} km • Estimasi ${i.estimatedMinutes} menit',
                    ),
                    const SizedBox(height: 8),
                    OfflineBanner(store: store),
                    const SizedBox(height: 8),
                    SigmaButton(
                      label: 'Tiba di Lokasi',
                      onPressed: () async {
                        await store.setTaskStatus(TaskStatus.arrived);
                        if (c.mounted) {
                          pushReplace(c, LocationDetail(store: store));
                        }
                      },
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class ProgressHandling extends StatelessWidget {
  const ProgressHandling({super.key, required this.store});
  final OfficerStore store;
  @override
  Widget build(BuildContext c) {
    final s = store.activeTask.status;
    final stages = [
      TaskStatus.assigned,
      TaskStatus.accepted,
      TaskStatus.enRoute,
      TaskStatus.arrived,
      TaskStatus.handling,
      TaskStatus.extinguishing,
      TaskStatus.cooling,
      TaskStatus.completed,
    ];
    return Scaffold(
      body: SafeArea(
        child: Page(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SigmaHeader(
                title: 'Progres Penanganan',
                subtitle: 'KH-0102',
                back: true,
              ),
              const SizedBox(height: 14),
              ...stages.map((x) => TimelineItem(status: x, current: s)),
              const SizedBox(height: 12),
              if (nextStatus(s) != null)
                SigmaButton(
                  label: actionLabel(nextStatus(s)!),
                  icon: Icons.play_circle_outline,
                  onPressed: () async {
                    await store.setTaskStatus(nextStatus(s)!);
                  },
                ),
              const SizedBox(height: 8),
              SigmaButton(
                label: 'Buat Laporan',
                secondary: true,
                onPressed: () => push(c, CreateReport(store: store)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class TimelineItem extends StatelessWidget {
  const TimelineItem({super.key, required this.status, required this.current});
  final TaskStatus status, current;
  @override
  Widget build(BuildContext c) {
    final done = status.index <= current.index;
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        children: [
          CircleAvatar(
            radius: 14,
            backgroundColor: done ? sigmaGreen : sigmaBlue,
            child: Icon(
              done ? Icons.check : Icons.circle,
              size: 14,
              color: done ? Colors.white : sigmaNavy,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              statusLabel(status),
              style: TextStyle(
                fontWeight: FontWeight.w700,
                color: done ? sigmaNavy : Colors.grey,
              ),
            ),
          ),
          if (status == current)
            const SigmaChip(text: 'AKTIF', color: sigmaOrange),
        ],
      ),
    );
  }
}

class OfficerReports extends StatelessWidget {
  const OfficerReports({super.key, required this.store});
  final OfficerStore store;
  @override
  Widget build(BuildContext c) {
    final pending = store.reports
        .where((x) => x.status == SyncStatus.pending)
        .length;
    return Page(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SigmaHeader(
            title: 'Laporan',
            online: store.connectivity == ConnectivityStatus.online,
          ),
          const SizedBox(height: 12),
          OfflineBanner(store: store),
          SigmaCard(
            color: const Color(0xFFFFE1CF),
            child: Row(
              children: [
                const Icon(Icons.sync, color: sigmaOrange),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    '$pending data menunggu sinkronisasi',
                    style: const TextStyle(fontWeight: FontWeight.w800),
                  ),
                ),
                TextButton(
                  onPressed: () async {
                    await store.sync();
                    if (c.mounted) {
                      notice(
                        c,
                        store.connectivity == ConnectivityStatus.online
                            ? 'Sinkronisasi selesai'
                            : 'Offline: antrean disimpan',
                      );
                    }
                  },
                  child: const Text('Sinkronkan'),
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),
          ...store.reports.map(
            (r) => SigmaCard(
              child: ListTile(
                contentPadding: EdgeInsets.zero,
                leading: CircleAvatar(
                  backgroundColor: sigmaBlue,
                  child: Icon(reportIcon(r.type), color: sigmaRed),
                ),
                title: Text(
                  r.title,
                  style: const TextStyle(fontWeight: FontWeight.w800),
                ),
                subtitle: Text('${r.type} • ${r.createdAt}'),
                trailing: SigmaChip(
                  text: r.status == SyncStatus.synced ? 'TERKIRIM' : 'MENUNGGU',
                  color: r.status == SyncStatus.synced
                      ? sigmaGreen
                      : sigmaOrange,
                ),
              ),
            ),
          ),
          const SizedBox(height: 8),
          SigmaButton(
            label: 'Buat Laporan Baru',
            icon: Icons.add,
            onPressed: () => push(c, CreateReport(store: store)),
          ),
        ],
      ),
    );
  }
}

class CreateReport extends StatefulWidget {
  const CreateReport({super.key, required this.store});
  final OfficerStore store;
  @override
  State<CreateReport> createState() => _CreateReportState();
}

class _CreateReportState extends State<CreateReport> {
  final note = TextEditingController();
  String fire = 'Aktif';
  final tags = <String>{};
  @override
  void dispose() {
    note.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext c) {
    final offline = widget.store.connectivity == ConnectivityStatus.offline;
    return Scaffold(
      body: SafeArea(
        child: Page(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              SigmaHeader(
                title: 'Buat Laporan',
                subtitle: 'KH-0102',
                back: true,
                online: !offline,
              ),
              const SizedBox(height: 12),
              SigmaCard(
                color: sigmaBlue,
                child: const Text(
                  'Kebakaran Lahan Gambut\nDesa Sukamaju, Sektor Barat 04',
                ),
              ),
              const SizedBox(height: 12),
              const Text(
                'BUKTI LAPANGAN',
                style: TextStyle(fontWeight: FontWeight.w800),
              ),
              Row(
                children: [
                  media(c, Icons.camera_alt, 'Foto'),
                  const SizedBox(width: 8),
                  media(c, Icons.videocam, 'Video'),
                  const SizedBox(width: 8),
                  media(c, Icons.mic, 'Suara'),
                ],
              ),
              const SizedBox(height: 10),
              TextField(
                controller: note,
                maxLines: 3,
                decoration: const InputDecoration(
                  hintText: 'Catatan kondisi api dan personel...',
                ),
              ),
              Wrap(
                spacing: 6,
                children:
                    [
                          '#AsapTebal',
                          '#SekatBakar',
                          '#AnginKencang',
                          '#BaraGambut',
                          '#ButuhAir',
                        ]
                        .map(
                          (x) => FilterChip(
                            label: Text(x),
                            selected: tags.contains(x),
                            onSelected: (v) => setState(
                              () => v ? tags.add(x) : tags.remove(x),
                            ),
                          ),
                        )
                        .toList(),
              ),
              const SizedBox(height: 10),
              const SigmaCard(
                child: ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: Icon(Icons.my_location, color: sigmaGreen),
                  title: Text('Lokasi Otomatis'),
                  subtitle: Text('-0.2841, 101.4298 • Akurasi 3 m'),
                ),
              ),
              SegmentedButton<String>(
                segments: const [
                  ButtonSegment(value: 'Aktif', label: Text('Aktif')),
                  ButtonSegment(
                    value: 'Mulai Padam',
                    label: Text('Mulai Padam'),
                  ),
                  ButtonSegment(
                    value: 'Padam Terkendali',
                    label: Text('Padam'),
                  ),
                ],
                selected: {fire},
                onSelectionChanged: (x) => setState(() => fire = x.first),
              ),
              const SizedBox(height: 10),
              SigmaCard(
                color: sigmaBlue,
                child: Text(
                  offline
                      ? 'AI cloud nonaktif. Media akan masuk antrean sinkronisasi.'
                      : 'AI aktif • KarhutlaNet v4.2 siap memverifikasi.',
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
              ),
              const SizedBox(height: 12),
              SigmaButton(
                label: 'Simpan Laporan',
                icon: Icons.send,
                onPressed: () async {
                  await widget.store.addReport(
                    note.text.isEmpty ? 'Laporan $fire' : note.text,
                    type: 'Catatan',
                  );
                  if (c.mounted) {
                    notice(
                      c,
                      offline
                          ? 'Laporan disimpan lokal'
                          : 'Laporan tersinkronisasi',
                    );
                    Navigator.pop(c);
                  }
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget media(BuildContext c, IconData i, String t) => Expanded(
    child: InkWell(
      onTap: () => notice(c, '$t disimpan sebagai mock media'),
      child: SigmaCard(
        padding: const EdgeInsets.all(12),
        child: Column(
          children: [
            Icon(i, color: sigmaRed),
            Text(t),
          ],
        ),
      ),
    ),
  );
}

class AIVerification extends StatelessWidget {
  const AIVerification({super.key, required this.store});
  final OfficerStore store;
  @override
  Widget build(BuildContext c) {
    final online = store.connectivity == ConnectivityStatus.online;
    return Scaffold(
      body: SafeArea(
        child: Page(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              SigmaHeader(
                title: 'AI Verifikasi',
                subtitle: 'KH-0102',
                back: true,
                online: online,
              ),
              const SizedBox(height: 14),
              Container(
                height: 230,
                decoration: BoxDecoration(
                  color: Colors.black87,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Center(
                  child: Icon(
                    Icons.local_fire_department,
                    color: sigmaRed,
                    size: 76,
                  ),
                ),
              ),
              const SizedBox(height: 12),
              SigmaCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Text(
                          'HASIL KLASIFIKASI',
                          style: TextStyle(fontWeight: FontWeight.w800),
                        ),
                        const Spacer(),
                        SigmaChip(
                          text: online ? 'AI AKTIF' : 'AI OFFLINE',
                          color: online ? sigmaGreen : sigmaOrange,
                        ),
                      ],
                    ),
                    const Text(
                      'Fire • 94%',
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const LinearProgressIndicator(value: .94, color: sigmaRed),
                    const SizedBox(height: 6),
                    Text(
                      online ? 'Model: KarhutlaNet v4.2' : 'AI cloud tidak tersedia. Foto tetap tersimpan lokal.',
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
              SigmaButton(
                label: 'Gunakan Hasil Verifikasi',
                onPressed: () async {
                  await store.addReport(
                    'Verifikasi AI Fire 94%',
                    type: 'Verifikasi AI',
                  );
                  if (c.mounted) Navigator.pop(c);
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class OfficerProfile extends StatelessWidget {
  const OfficerProfile({super.key, required this.store});
  final OfficerStore store;
  @override
  Widget build(BuildContext c) => Page(
    child: Column(
      children: [
        SigmaHeader(
          title: 'Profil',
          online: store.connectivity == ConnectivityStatus.online,
        ),
        const SizedBox(height: 16),
        const CircleAvatar(
          radius: 42,
          backgroundColor: sigmaRed,
          foregroundColor: Colors.white,
          child: Icon(Icons.person, size: 48),
        ),
        const SizedBox(height: 8),
        const Text(
          'Andi Saputra',
          style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900),
        ),
        const Text('Petugas Lapangan • Regu C'),
        const SizedBox(height: 12),
        ...[
          'Data Saya',
          'Organisasi',
          'Sinkronisasi Data',
          'Pengaturan',
          'Notifikasi',
          'Keamanan',
          'Bantuan & SOP',
        ].map(
          (x) => SigmaCard(
            padding: EdgeInsets.zero,
            child: ListTile(
              title: Text(x),
              leading: Icon(menuIcon(x), color: sigmaNavy),
              trailing: const Icon(Icons.chevron_right),
              onTap: () => x == 'Sinkronisasi Data'
                  ? push(c, SyncScreen(store: store))
                  : push(c, ProfileDetail(title: x, store: store)),
            ),
          ),
        ),
        SigmaButton(
          label: 'Keluar Akun Petugas',
          color: Colors.white,
          onPressed: () => Navigator.of(c).popUntil((r) => r.isFirst),
        ),
      ],
    ),
  );
}

class ProfileDetail extends StatelessWidget {
  const ProfileDetail({super.key, required this.title, required this.store});
  final String title;
  final OfficerStore store;
  @override
  Widget build(BuildContext c) {
    return Scaffold(
      body: SafeArea(
        child: Page(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              SigmaHeader(
                title: title,
                back: true,
                online: store.connectivity == ConnectivityStatus.online,
              ),
              const SizedBox(height: 18),
              SigmaCard(
                child: Text(
                  '$title\nInformasi petugas lapangan SIGMA tersedia secara offline.',
                  style: const TextStyle(fontSize: 16, height: 1.6),
                ),
              ),
              if (title == 'Pengaturan')
                SwitchListTile(
                  value: store.connectivity == ConnectivityStatus.online,
                  onChanged: (_) => store.toggleConnectivity(),
                  title: const Text('Mode online'),
                ),
            ],
          ),
        ),
      ),
    );
  }
}

class SyncScreen extends StatelessWidget {
  const SyncScreen({super.key, required this.store});
  final OfficerStore store;
  @override
  Widget build(BuildContext c) {
    final count = store.reports
        .where((r) => r.status == SyncStatus.pending)
        .length;
    return Scaffold(
      body: SafeArea(
        child: Page(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              SigmaHeader(
                title: 'Sinkronisasi Data',
                back: true,
                online: store.connectivity == ConnectivityStatus.online,
              ),
              const SizedBox(height: 16),
              SigmaCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      store.connectivity == ConnectivityStatus.online
                          ? 'ONLINE'
                          : 'OFFLINE',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w900,
                        color: store.connectivity == ConnectivityStatus.online
                            ? sigmaGreen
                            : sigmaOrange,
                      ),
                    ),
                    Text('$count laporan menunggu sinkronisasi'),
                    const SizedBox(height: 12),
                    SigmaButton(
                      label: 'Sinkronkan Sekarang',
                      onPressed: () async {
                        await store.sync();
                        if (c.mounted) {
                          notice(
                            c,
                            store.connectivity == ConnectivityStatus.online
                                ? 'Data tersinkronisasi'
                                : 'Offline: sinkronisasi ditunda',
                          );
                        }
                      },
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
              const SigmaCard(
                color: sigmaBlue,
                child: Text(
                  'Peta cache, tugas, laporan, dan pembaruan status tetap tersedia secara offline.',
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class OfflineBanner extends StatelessWidget {
  const OfflineBanner({super.key, required this.store});
  final OfficerStore store;
  @override
  Widget build(BuildContext c) =>
      store.connectivity == ConnectivityStatus.online
      ? const SizedBox.shrink()
      : const Padding(
          padding: EdgeInsets.only(bottom: 10),
          child: SigmaCard(
            color: Color(0xFFFFE1CF),
            child: Row(
              children: [
                Icon(Icons.cloud_off, color: sigmaOrange),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'OFFLINE • Data disimpan dan menunggu sinkronisasi',
                    style: TextStyle(fontWeight: FontWeight.w700),
                  ),
                ),
              ],
            ),
          ),
        );
}

Widget row(String a, String b) => Padding(
  padding: const EdgeInsets.symmetric(vertical: 5),
  child: Row(
    children: [
      Expanded(
        child: Text(a, style: const TextStyle(color: Colors.black54)),
      ),
      Flexible(
        child: Text(
          b,
          textAlign: TextAlign.right,
          style: const TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
    ],
  ),
);
void push(BuildContext c, Widget page) =>
    Navigator.push(c, MaterialPageRoute(builder: (_) => page));
void pushReplace(BuildContext c, Widget page) =>
    Navigator.pushReplacement(c, MaterialPageRoute(builder: (_) => page));
void notice(BuildContext c, String s) => ScaffoldMessenger.of(
  c,
).showSnackBar(SnackBar(content: Text(s), behavior: SnackBarBehavior.floating));
TaskStatus? nextStatus(TaskStatus s) => switch (s) {
  TaskStatus.assigned => TaskStatus.accepted,
  TaskStatus.accepted => TaskStatus.enRoute,
  TaskStatus.enRoute => TaskStatus.arrived,
  TaskStatus.arrived => TaskStatus.handling,
  TaskStatus.handling => TaskStatus.extinguishing,
  TaskStatus.extinguishing => TaskStatus.cooling,
  TaskStatus.cooling => TaskStatus.completed,
  TaskStatus.completed || TaskStatus.cancelled => null,
};
String actionLabel(TaskStatus s) => switch (s) {
  TaskStatus.accepted => 'Terima Tugas',
  TaskStatus.enRoute => 'Mulai Perjalanan',
  TaskStatus.arrived => 'Tiba di Lokasi',
  TaskStatus.handling => 'Mulai Penanganan',
  TaskStatus.extinguishing => 'Api Padam',
  TaskStatus.cooling => 'Mulai Pendinginan',
  TaskStatus.completed => 'Selesaikan Tugas',
  TaskStatus.assigned || TaskStatus.cancelled => 'Perbarui Tugas',
};
String statusLabel(TaskStatus s) => switch (s) {
  TaskStatus.assigned => 'Ditugaskan',
  TaskStatus.accepted => 'Diterima',
  TaskStatus.enRoute => 'Dalam Perjalanan',
  TaskStatus.arrived => 'Tiba di Lokasi',
  TaskStatus.handling => 'Penanganan',
  TaskStatus.extinguishing => 'Api Padam',
  TaskStatus.cooling => 'Pendinginan',
  TaskStatus.completed => 'Selesai',
  TaskStatus.cancelled => 'Dibatalkan',
};
Color statusColor(TaskStatus s) => s == TaskStatus.completed
    ? sigmaGreen
    : s == TaskStatus.cancelled
    ? Colors.grey
    : sigmaOrange;
IconData reportIcon(String s) => switch (s) {
  'Foto' => Icons.camera_alt,
  'Video' => Icons.videocam,
  'Suara' => Icons.mic,
  'Verifikasi AI' => Icons.auto_awesome,
  _ => Icons.description,
};
IconData menuIcon(String s) => switch (s) {
  'Data Saya' => Icons.badge_outlined,
  'Organisasi' => Icons.groups_outlined,
  'Sinkronisasi Data' => Icons.sync,
  'Pengaturan' => Icons.settings_outlined,
  'Notifikasi' => Icons.notifications_outlined,
  'Keamanan' => Icons.security_outlined,
  _ => Icons.help_outline,
};

class IncidentCard extends StatelessWidget {
  const IncidentCard({super.key, required this.store, required this.onDetail});
  final OfficerStore store;
  final VoidCallback onDetail;
  @override
  Widget build(BuildContext c) {
    final i = store.activeTask.incident;
    return SigmaCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            i.title,
            style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w800),
          ),
          Text(i.location),
          const SizedBox(height: 8),
          SigmaChip(text: 'Risiko ${i.riskIndex}/100', color: sigmaRed),
          const SizedBox(height: 10),
          SigmaButton(label: 'Lihat Tugas', onPressed: onDetail),
        ],
      ),
    );
  }
}
