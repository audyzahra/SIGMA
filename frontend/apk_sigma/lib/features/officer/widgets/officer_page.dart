import 'package:flutter/material.dart';

import 'officer_widgets.dart';

class OfficerPage extends StatelessWidget {
  const OfficerPage({
    super.key,
    required this.title,
    required this.child,
    required this.onRefresh,
  });

  final String title;
  final Widget child;
  final Future<void> Function() onRefresh;

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: onRefresh,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 14, 16, 28),
        children: [
          SigmaHeader(title: title),
          const SizedBox(height: 18),
          child,
        ],
      ),
    );
  }
}