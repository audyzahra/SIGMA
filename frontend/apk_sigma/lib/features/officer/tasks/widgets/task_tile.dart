import 'package:flutter/material.dart';

import '../../domain/entities/officer_entities.dart';
import '../../widgets/officer_widgets.dart';
import '../task_status_helper.dart';

class TaskTile extends StatelessWidget {
  const TaskTile({
    super.key,
    required this.task,
    required this.onTap,
  });

  final OfficerTask task;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final incident = task.incident;
    final status = task.status;

    return SigmaCard(
      child: ListTile(
        onTap: onTap,
        contentPadding: EdgeInsets.zero,
        leading: const Icon(
          Icons.local_fire_department_outlined,
          color: sigmaRed,
        ),
        title: Text(incident.location),
        subtitle: Text(
          [statusLabel(status), incident.priority].join(' · '),
        ),
        trailing: const Icon(Icons.chevron_right),
      ),
    );
  }
}