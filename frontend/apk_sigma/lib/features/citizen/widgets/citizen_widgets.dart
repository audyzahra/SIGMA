import 'package:flutter/material.dart';

import '../../../core/theme/sigma_theme.dart';
import '../notifications/citizen_notification_scope.dart';
import '../notifications/pages/notification_page.dart';

class CitizenHeader extends StatelessWidget {
  const CitizenHeader({super.key, required this.title, this.back = false});
  final String title;
  final bool back;

  @override
  Widget build(BuildContext context) {
    final notificationScope = CitizenNotificationScope.maybeOf(context);
    return Row(children: [
      if (back)
        IconButton(
          onPressed: () => Navigator.pop(context),
          icon: const Icon(Icons.arrow_back),
        )
      else
        ClipRRect(
          borderRadius: BorderRadius.circular(10),
          child: Image.asset(
            'assets/images/logo.png',
            width: 40,
            height: 40,
            fit: BoxFit.cover,
            semanticLabel: 'Logo SIGMA',
          ),
        ),
      const SizedBox(width: 10),
      Expanded(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (!back)
              const Text('SIGMA WARGA', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w900, color: SigmaColors.primary)),
            Text(
              title,
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w800,
                color: Theme.of(context).colorScheme.onSurface,
              ),
            ),
          ],
        ),
      ),
      if (!back && notificationScope != null) ...[
        IconButton(
          tooltip: 'Notifikasi',
          onPressed: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => NotificationPage(
              store: notificationScope.notifier!, api: notificationScope.api,
            )),
          ),
          icon: Badge(
            isLabelVisible: notificationScope.notifier!.unreadCount > 0,
            label: Text(notificationScope.notifier!.unreadCount > 99 ? '99+' : '${notificationScope.notifier!.unreadCount}'),
            child: const Icon(Icons.notifications_none_rounded),
          ),
        ),
        
      ],
    ]);
  }
}

class CitizenSurface extends StatelessWidget {
  const CitizenSurface({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(16),
  });
  final Widget child;
  final EdgeInsets padding;
  @override
  Widget build(BuildContext context) => Container(
    margin: const EdgeInsets.only(bottom: 12),
    padding: padding,
    decoration: BoxDecoration(
      color: Theme.of(context).colorScheme.surface,
      borderRadius: BorderRadius.circular(16),
      boxShadow: [
        BoxShadow(
          color: Theme.of(context).brightness == Brightness.dark
              ? Colors.black.withValues(alpha: 0.2)
              : const Color(0x0A172033),
          blurRadius: 12,
          offset: const Offset(0, 4),
        ),
      ],
    ),
    child: child,
  );
}

class StatusBadge extends StatelessWidget {
  const StatusBadge({super.key, required this.label, required this.color});
  final String label;
  final Color color;
  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
    decoration: BoxDecoration(
      color: color.withValues(alpha: .12),
      borderRadius: BorderRadius.circular(20),
    ),
    child: Text(
      label,
      style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.w800),
    ),
  );
}

class PageLoading extends StatelessWidget {
  const PageLoading({super.key});
  @override
  Widget build(BuildContext context) =>
      const Center(child: CircularProgressIndicator());
}

class EmptyState extends StatelessWidget {
  const EmptyState({super.key, required this.message});
  final String message;
  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.all(28),
    child: Center(
      child: Text(
        message,
        textAlign: TextAlign.center,
        style: TextStyle(
          color: Theme.of(context).colorScheme.onSurfaceVariant,
        ),
      ),
    ),
  );
}

class PageError extends StatelessWidget {
  const PageError({super.key, required this.onRetry, this.message});
  final VoidCallback onRetry;
  final String? message;
  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.cloud_off_outlined, size: 38),
          const SizedBox(height: 10),
          Text(message ?? 'Data belum dapat dimuat.', textAlign: TextAlign.center),
          TextButton(onPressed: onRetry, child: const Text('Muat ulang')),
        ],
      ),
    ),
  );
}

String statusLabel(String value) =>
    {
      'submitted': 'Diterima',
      'verified': 'Terverifikasi',
      'process': 'Ditangani',
      'completed': 'Selesai',
      'rejected': 'Ditolak',
      'detected': 'Terdeteksi',
      'on_process': 'Ditangani',
      'extinguished': 'Padam',
    }[value] ??
    value;
String reportTypeLabel(String value) =>
    {
      'fire': 'Kebakaran',
      'smoke': 'Asap',
      'burning_activity': 'Aktivitas pembakaran',
      'other': 'Lainnya',
    }[value] ??
    value;
String riskLabel(String value) =>
    {
      'low': 'Rendah',
      'medium': 'Sedang',
      'high': 'Tinggi',
      'extreme': 'Ekstrem',
    }[value] ??
    value;
Color statusColor(String value) =>
    value == 'completed' || value == 'verified' || value == 'extinguished'
    ? SigmaColors.success
    : value == 'rejected'
    ? SigmaColors.primary
    : value == 'process' || value == 'on_process' || value == 'medium'
    ? SigmaColors.secondary
    : value == 'low'
    ? SigmaColors.success
    : value == 'high' || value == 'extreme'
    ? SigmaColors.primary
    : SigmaColors.info;
