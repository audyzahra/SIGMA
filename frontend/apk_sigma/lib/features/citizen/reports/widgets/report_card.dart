import 'package:flutter/material.dart';

import '../../../../models/citizen_models.dart';
import '../../widgets/citizen_widgets.dart';

class ReportCard extends StatelessWidget {
  const ReportCard({super.key, required this.report, required this.onTap});
  final CitizenReport report;
  final VoidCallback onTap;
  @override
  Widget build(BuildContext context) => CitizenSurface(
    child: InkWell(
      onTap: onTap,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  report.number,
                  style: const TextStyle(fontWeight: FontWeight.w800),
                ),
              ),
              StatusBadge(
                label: statusLabel(report.status),
                color: statusColor(report.status),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            reportTypeLabel(report.type),
            style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w700),
          ),
          if (report.description.isNotEmpty)
            Text(
              report.description,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          const SizedBox(height: 8),
          Text(
            '${report.latitude.toStringAsFixed(5)}, ${report.longitude.toStringAsFixed(5)}',
            style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
          ),
        ],
      ),
    ),
  );
}
