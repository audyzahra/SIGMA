import 'package:flutter/material.dart';

import '../../../../../core/theme/sigma_theme.dart';
import '../../../../../models/citizen_models.dart';
import '../../../../../services/citizen_api.dart';
import '../../reports/pages/report_detail_page.dart';
import '../citizen_notification_store.dart';

class NotificationPage extends StatelessWidget {
  const NotificationPage({super.key, required this.store, required this.api});
  final CitizenNotificationStore store;
  final CitizenApi api;

  @override
  Widget build(BuildContext context) => Scaffold(
        body: SafeArea(
          child: AnimatedBuilder(
            animation: store,
            builder: (context, _) {
              if (store.loading && store.items.isEmpty) {
                return const Center(child: CircularProgressIndicator());
              }
              return RefreshIndicator(
                onRefresh: store.load,
                child: ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    Row(
                      children: [
                        IconButton(
                          onPressed: () => Navigator.pop(context),
                          icon: const Icon(Icons.arrow_back),
                        ),
                        const SizedBox(width: 8),
                        const Text(
                          'Notifikasi',
                          style: TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w800,
                            color: SigmaColors.ink,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 18),
                    if (store.unreadCount > 0)
                      Align(
                        alignment: Alignment.centerRight,
                        child: TextButton.icon(
                          onPressed: store.markAllRead,
                          icon: const Icon(Icons.done_all_outlined),
                          label: const Text('Tandai semua dibaca'),
                        ),
                      ),
                    if (store.items.isEmpty)
                      const Padding(
                        padding: EdgeInsets.all(28),
                        child: Center(
                          child: Text('Belum ada notifikasi untuk Anda.'),
                        ),
                      )
                    else
                      ...store.items.map((item) => _NotificationTile(
                            item: item,
                            onTap: () async {
                              await store.markRead(item);
                              if (context.mounted && item.reportId != null) {
                                Navigator.push(context, MaterialPageRoute(
                                  builder: (_) => ReportDetailPage(api: api, id: item.reportId!),
                                ));
                              }
                            },
                          )),
                  ],
                ),
              );
            },
          ),
        ),
      );
}

class _NotificationTile extends StatelessWidget {
  const _NotificationTile({required this.item, required this.onTap});
  final CitizenNotification item;
  final VoidCallback onTap;
  @override
  Widget build(BuildContext context) => Semantics(
        button: true,
        label: item.title,
        child: Container(
          margin: const EdgeInsets.only(bottom: 10),
          decoration: BoxDecoration(
            color: item.isRead ? Colors.white : const Color(0xFFFFF5F5),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: item.isRead ? const Color(0xFFE7EAF0) : const Color(0xFFF3C6C6),
            ),
          ),
          child: ListTile(
            onTap: onTap,
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            leading: CircleAvatar(
              backgroundColor: SigmaColors.primary.withValues(alpha: .1),
              foregroundColor: SigmaColors.primary,
              child: Icon(item.status == 'completed'
                  ? Icons.task_alt_outlined
                  : Icons.notifications_active_outlined),
            ),
            title: Text(item.title, style: TextStyle(fontWeight: item.isRead ? FontWeight.w700 : FontWeight.w900)),
            subtitle: Padding(
              padding: const EdgeInsets.only(top: 4),
              child: Text('${item.message}\n${_relative(item.createdAt)}'),
            ),
            isThreeLine: true,
            trailing: item.isRead ? null : const Icon(Icons.circle, size: 10, color: SigmaColors.primary),
          ),
        ),
      );
}

String _relative(DateTime value) {
  final difference = DateTime.now().difference(value);
  if (difference.inMinutes < 1) return 'Baru saja';
  if (difference.inHours < 1) return '${difference.inMinutes} menit lalu';
  if (difference.inDays < 1) return '${difference.inHours} jam lalu';
  return '${difference.inDays} hari lalu';
}
