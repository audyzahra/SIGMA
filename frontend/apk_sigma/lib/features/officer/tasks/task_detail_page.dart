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

    setState(() {
      _updating = true;
    });

    await widget.store.setTaskStatus(widget.task.id, status);

    if (!mounted) return;

    setState(() {
      _updating = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: widget.store,
      builder: (context, _) {
        final updatedTask = currentTask;
        final updatedStatus = updatedTask.status;

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
                        Text(
                          updatedTask.incident.location,
                          style: const TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 18,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${updatedTask.incident.latitude}, '
                          '${updatedTask.incident.longitude}',
                        ),
                        const SizedBox(height: 8),
                        SigmaChip(
                          text: statusLabel(updatedStatus),
                          color: statusColor(updatedStatus),
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
                    status: updatedStatus,
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