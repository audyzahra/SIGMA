import 'package:flutter/material.dart';

import '../domain/entities/officer_entities.dart';
import '../state/officer_store.dart';
import '../widgets/officer_page.dart';
import '../widgets/officer_widgets.dart';
import 'task_detail_page.dart';
import 'widgets/task_tile.dart';

class OfficerTasksPage extends StatefulWidget {
  const OfficerTasksPage({
    super.key,
    required this.store,
  });

  final OfficerStore store;

  @override
  State<OfficerTasksPage> createState() => _OfficerTasksPageState();
}

class _OfficerTasksPageState extends State<OfficerTasksPage> {
  int _filter = 0;

  @override
  Widget build(BuildContext context) {
    final tasks = widget.store.tasks.where((task) {
      if (_filter == 1) {
        return task.status != TaskStatus.completed &&
            task.status != TaskStatus.cancelled;
      }

      return _filter != 2 || task.status == TaskStatus.completed;
    }).toList();

    return OfficerPage(
      title: 'Tugas',
      onRefresh: widget.store.load,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SegmentedButton<int>(
            segments: const [
              ButtonSegment(
                value: 0,
                label: Text('Semua'),
              ),
              ButtonSegment(
                value: 1,
                label: Text('Aktif'),
              ),
              ButtonSegment(
                value: 2,
                label: Text('Selesai'),
              ),
            ],
            selected: {_filter},
            onSelectionChanged: (value) {
              setState(() => _filter = value.first);
            },
          ),
          if (tasks.isEmpty)
            OfficerEmptyState(
              icon: Icons.assignment_outlined,
              title: _filter == 0
                  ? 'Tidak ada tugas aktif'
                  : 'Belum ada tugas pada filter ini',
              description:
                  'Tugas baru akan muncul setelah pemerintah mengirimkan penugasan melalui sistem SIGMA.',
              detail:
                  'Gunakan tombol muat ulang untuk mengambil data tugas terbaru.',
              actionLabel: 'Muat ulang',
              onAction: widget.store.load,
            )
          else
            ...tasks.map(
              (task) {
                return TaskTile(
                  task: task,
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => TaskDetailPage(
                          store: widget.store,
                          task: task,
                        ),
                      ),
                    );
                  },
                );
              },
            ),
        ],
      ),
    );
  }
}