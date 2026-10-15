import 'package:flutter/material.dart';

import '../../widgets/officer_widgets.dart';

class TaskStepButton extends StatelessWidget {
  const TaskStepButton({
    super.key,
    required this.label,
    required this.icon,
    required this.enabled,
    required this.completed,
    required this.loading,
    required this.onPressed,
  });

  final String label;
  final IconData icon;
  final bool enabled;
  final bool completed;
  final bool loading;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    if (completed) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
        decoration: BoxDecoration(
          color: const Color(0xFFE8F5E9),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: const Color(0xFFA5D6A7)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.check_circle,
              size: 17,
              color: sigmaGreen,
            ),
            const SizedBox(width: 7),
            Text(
              label,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: sigmaGreen,
              ),
            ),
          ],
        ),
      );
    }

    if (!enabled) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
        decoration: BoxDecoration(
          color: const Color(0xFFF1F3F5),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 17,
              color: Colors.grey.shade500,
            ),
            const SizedBox(width: 7),
            Text(
              label,
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: Colors.grey.shade500,
              ),
            ),
          ],
        ),
      );
    }

    return ElevatedButton.icon(
      onPressed: loading ? null : onPressed,
      icon: loading
          ? const SizedBox(
              width: 15,
              height: 15,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                color: Colors.white,
              ),
            )
          : Icon(icon, size: 17),
      label: Text(loading ? 'Memproses...' : label),
      style: ElevatedButton.styleFrom(
        backgroundColor: sigmaRed,
        foregroundColor: Colors.white,
        disabledBackgroundColor: sigmaRed.withValues(alpha: 0.6),
        disabledForegroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 11),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10),
        ),
        elevation: 0,
      ),
    );
  }
}