import 'package:flutter/material.dart';

import 'pages/auth/role_selection_page.dart';
import 'presentation/officer/officer_shell.dart';
import 'core/theme/sigma_theme.dart';

void main() {
  runApp(const SigmaApp());
}

class SigmaApp extends StatelessWidget {
  const SigmaApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SIGMA Disaster Intelligence',
      debugShowCheckedModeBanner: false,
      theme: SigmaTheme.light,
      routes: {
        '/officer/home': (_) => const OfficerShell(name: 'Budi Pratama'),
      },
      home: const RoleSelectionPage(),
    );
  }
}
