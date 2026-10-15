import 'package:flutter/material.dart';

import '../domain/entities/officer_entities.dart';
import '../state/officer_store.dart';
import '../widgets/officer_page.dart';
import '../widgets/officer_widgets.dart';
import 'task_status_helper.dart';
import 'widgets/task_progress_actions.dart';

class TaskDetailPage extends StatefulWidget {
  const TaskDetailPage({
    super.key,
    required this.store,
    required this.task,
  });

  final OfficerStore store;
  final OfficerTask task;

  @override
  State<TaskDetailPage> createState() => TaskDetailPageState();
}

class TaskDetailPageState extends State<TaskDetailPage> {
  bool _updating = false;

  OfficerTask get currentTask {
    return widget.store.tasks.firstWhere(
      (item) => item.id == widget.task.id,
      orElse: () => widget.task,
    );
  }

  Future<void> _updateStatus(TaskStatus status) async {
    if (_updating) return;

    setState(() => _updating = true);

    await widget.store.setTaskStatus(widget.task.id, status);

    if (!mounted) return;

    setState(() => _updating = false);
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 135,
            child: Text(
              label,
              style: const TextStyle(color: Colors.black54),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }

  String _score(double value) {
    return value == 0 ? 'Belum tersedia' : value.toStringAsFixed(1);
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: widget.store,
      builder: (context, _) {
        final task = currentTask;
        final incident = task.incident;
        final status = task.status;

        return Scaffold(
          body: SafeArea(
            child: OfficerPage(
              title: 'Detail Tugas',
              onRefresh: widget.store.load,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SigmaCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Informasi Penugasan',
                          style: TextStyle(
                            fontSize: 17,
                            fontWeight: FontWeight.w800,
                            color: sigmaNavy,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          task.area.isNotEmpty
                              ? task.area
                              : incident.location,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        const SizedBox(height: 12),
                        _infoRow('ID Penugasan', '#${task.id}'),
                        _infoRow('Tim Petugas', task.teamName),
                        _infoRow('Prioritas', incident.priority.toUpperCase()),
                        _infoRow('Status', statusLabel(status)),
                        _infoRow(
                          'Koordinat',
                          '${incident.latitude}, ${incident.longitude}',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  SigmaCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Analisis Wilayah',
                          style: TextStyle(
                            fontSize: 17,
                            fontWeight: FontWeight.w800,
                            color: sigmaNavy,
                          ),
                        ),
                        const SizedBox(height: 10),
                        _infoRow('Skor Risiko', _score(task.riskScore)),
                        _infoRow('Skor Dampak', _score(task.impactScore)),
                        _infoRow(
                          'Skor Prioritas',
                          _score(task.priorityScore),
                        ),
                        if (task.rankingPosition != null)
                          _infoRow(
                            'Peringkat',
                            '#${task.rankingPosition}',
                          ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'Proses Penanganan',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w800,
                      color: sigmaNavy,
                    ),
                  ),
                  const SizedBox(height: 10),
                  TaskProgressActions(
                    status: status,
                    loading: _updating,
                    onStatusSelected: _updateStatus,
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}