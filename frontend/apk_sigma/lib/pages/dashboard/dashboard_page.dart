import 'package:flutter/material.dart';

import '../../features/citizen/citizen_shell.dart';
import '../../presentation/officer/officer_shell.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key, required this.role, required this.name});

  final String role;
  final String name;

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  bool get _isOfficer => widget.role == 'officer';

  @override
  Widget build(BuildContext context) {
    if (!_isOfficer) {
      return CitizenShell(name: widget.name);
    }

    return OfficerShell(name: widget.name);
  }
}
