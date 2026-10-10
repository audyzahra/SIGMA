import 'package:flutter/material.dart';

import '../../domain/entities/officer_entities.dart';
import '../task_status_helper.dart';
import 'task_step_button.dart';

class TaskProgressActions extends StatelessWidget {
  const TaskProgressActions({
    super.key,
    required this.status,
    required this.loading,
    required this.onStatusSelected,
  });

  final TaskStatus status;
  final bool loading;
  final Future<void> Function(TaskStatus status) onStatusSelected;

  @override
  Widget build(BuildContext context) {
    final steps = <TaskStatus>[
      TaskStatus.accepted,
      TaskStatus.enRoute,
      TaskStatus.arrived,
      TaskStatus.handling,
      TaskStatus.completed,
    ];

    final currentIndex = _currentIndex(status);

    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: steps.asMap().entries.map((entry) {
        final index = entry.key;
        final step = entry.value;

        if (index > currentIndex + 1) {
          return TaskStepButton(
            label: actionLabel(step),
            icon: Icons.lock_outline,
            enabled: false,
            completed: false,
            loading: false,
            onPressed: null,
          );
        }

        if (index <= currentIndex) {
          return TaskStepButton(
            label: actionLabel(step),
            icon: Icons.check,
            enabled: false,
            completed: true,
            loading: false,
            onPressed: null,
          );
        }

        return TaskStepButton(
          label: actionLabel(step),
          icon: _actionIcon(step),
          enabled: !loading,
          completed: false,
          loading: loading,
          onPressed: () {
            onStatusSelected(step);
          },
        );
      }).toList(),
    );
  }

  int _currentIndex(TaskStatus status) {
    return switch (status) {
      TaskStatus.assigned => -1,
      TaskStatus.accepted => 0,
      TaskStatus.enRoute => 1,
      TaskStatus.arrived => 2,
      TaskStatus.handling => 3,
      TaskStatus.completed => 4,
      _ => -1,
    };
  }

  IconData _actionIcon(TaskStatus status) {
    return switch (status) {
      TaskStatus.accepted => Icons.check_circle_outline,
      TaskStatus.enRoute => Icons.directions_car_outlined,
      TaskStatus.arrived => Icons.location_on_outlined,
      TaskStatus.handling => Icons.local_fire_department_outlined,
      TaskStatus.completed => Icons.done_all,
      _ => Icons.arrow_forward,
    };
  }
}