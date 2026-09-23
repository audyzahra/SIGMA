import 'package:flutter/material.dart';

const sigmaRed = Color(0xFFC82828),
    sigmaNavy = Color(0xFF172033),
    sigmaPale = Color(0xFFF6F8FC),
    sigmaBlue = Color(0xFFE8F0FF),
    sigmaGreen = Color(0xFF078443),
    sigmaOrange = Color(0xFFF97316);

class SigmaCard extends StatelessWidget {
  const SigmaCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(16),
    this.color = Colors.white,
  });
  final Widget child;
  final EdgeInsets padding;
  final Color color;
  @override
  Widget build(BuildContext c) => Container(
    padding: padding,
    decoration: BoxDecoration(
      color: color,
      borderRadius: BorderRadius.circular(16),
      boxShadow: const [
        BoxShadow(
          color: Color(0x10172033),
          blurRadius: 12,
          offset: Offset(0, 4),
        ),
      ],
    ),
    child: child,
  );
}

class SigmaChip extends StatelessWidget {
  const SigmaChip({super.key, required this.text, this.color = sigmaGreen});
  final String text;
  final Color color;
  @override
  Widget build(BuildContext c) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
    decoration: BoxDecoration(
      color: color.withValues(alpha: .13),
      borderRadius: BorderRadius.circular(20),
    ),
    child: Text(
      text,
      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: color),
    ),
  );
}

class SigmaButton extends StatelessWidget {
  const SigmaButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.icon,
    this.secondary = false,
    this.color,
  });
  final String label;
  final VoidCallback onPressed;
  final IconData? icon;
  final bool secondary;
  final Color? color;
  @override
  Widget build(BuildContext c) => SizedBox(
    height: 52,
    child: FilledButton.icon(
      onPressed: onPressed,
      icon: icon == null ? const SizedBox.shrink() : Icon(icon),
      label: Text(label),
      style: FilledButton.styleFrom(
        backgroundColor: secondary ? sigmaBlue : color ?? sigmaRed,
        foregroundColor: secondary ? sigmaNavy : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        textStyle: const TextStyle(fontWeight: FontWeight.w800),
      ),
    ),
  );
}

class SigmaHeader extends StatelessWidget {
  const SigmaHeader({
    super.key,
    required this.title,
    this.subtitle,
    this.back = false,
    this.onBack,
    this.online = true,
  });
  final String title;
  final String? subtitle;
  final bool back, online;
  final VoidCallback? onBack;
  @override
  Widget build(BuildContext c) => Row(
    children: [
      if (back)
        IconButton(
          onPressed: onBack ?? () => Navigator.pop(c),
          icon: const Icon(Icons.arrow_back),
        )
      else
        ClipRRect(
          borderRadius: BorderRadius.circular(9),
          child: Image.asset(
            'assets/images/logo.png',
            width: 39,
            height: 39,
            fit: BoxFit.cover,
          ),
        ),
      const SizedBox(width: 10),
      Expanded(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              back ? title : 'SIGMA',
              style: TextStyle(
                fontWeight: FontWeight.w900,
                fontSize: back ? 20 : 14,
                color: back ? sigmaNavy : sigmaRed,
              ),
            ),
            Text(
              back ? subtitle ?? '' : title,
              style: TextStyle(
                fontWeight: FontWeight.w800,
                fontSize: back ? 12 : 20,
                color: sigmaNavy,
              ),
            ),
          ],
        ),
      ),
      SigmaChip(
        text: online ? '● ONLINE' : '● OFFLINE',
        color: online ? sigmaGreen : sigmaOrange,
      ),
      const SizedBox(width: 8),
      if (!back)
        const CircleAvatar(
          radius: 18,
          backgroundColor: sigmaRed,
          foregroundColor: Colors.white,
          child: Icon(Icons.person_outline),
        ),
    ],
  );
}

class Metric extends StatelessWidget {
  const Metric({
    super.key,
    required this.label,
    required this.value,
    required this.icon,
    this.color = sigmaRed,
  });
  final String label, value;
  final IconData icon;
  final Color color;
  @override
  Widget build(BuildContext c) => Expanded(
    child: Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: sigmaBlue,
        borderRadius: BorderRadius.circular(11),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 18),
          const SizedBox(height: 7),
          Text(label, style: const TextStyle(fontSize: 11)),
          Text(
            value,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w800,
              color: color,
            ),
          ),
        ],
      ),
    ),
  );
}

class OfficerEmptyState extends StatelessWidget {
  const OfficerEmptyState({
    super.key,
    required this.icon,
    required this.title,
    required this.description,
    this.detail,
    this.actionLabel,
    this.onAction,
  });
  final IconData icon;
  final String title;
  final String description;
  final String? detail;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 28),
    child: Column(
      children: [
        Container(
          width: 96,
          height: 96,
          decoration: const BoxDecoration(
            color: sigmaBlue,
            shape: BoxShape.circle,
          ),
          child: Icon(icon, size: 48, color: sigmaRed),
        ),
        const SizedBox(height: 18),
        Text(
          title,
          textAlign: TextAlign.center,
          style: const TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.w800,
            color: sigmaNavy,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          description,
          textAlign: TextAlign.center,
          style: const TextStyle(height: 1.45, color: Colors.black54),
        ),
        if (detail != null) ...[
          const SizedBox(height: 16),
          OfficerInfoCard(icon: Icons.info_outline, text: detail!),
        ],
        if (actionLabel != null && onAction != null) ...[
          const SizedBox(height: 20),
          SizedBox(
            width: double.infinity,
            child: SigmaButton(
              label: actionLabel!,
              icon: Icons.refresh,
              onPressed: onAction!,
            ),
          ),
        ],
      ],
    ),
  );
}

class OfficerInfoCard extends StatelessWidget {
  const OfficerInfoCard({
    super.key,
    required this.icon,
    required this.text,
    this.color = sigmaBlue,
  });
  final IconData icon;
  final String text;
  final Color color;

  @override
  Widget build(BuildContext context) => Container(
    width: double.infinity,
    padding: const EdgeInsets.all(14),
    decoration: BoxDecoration(
      color: color,
      borderRadius: BorderRadius.circular(14),
    ),
    child: Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, color: sigmaNavy),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(height: 1.4, color: sigmaNavy),
          ),
        ),
      ],
    ),
  );
}

class OfficerSectionHeader extends StatelessWidget {
  const OfficerSectionHeader({
    super.key,
    required this.title,
    this.actionLabel,
    this.onAction,
  });
  final String title;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) => Row(
    children: [
      Expanded(
        child: Text(
          title,
          style: const TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.w800,
            color: sigmaNavy,
          ),
        ),
      ),
      if (actionLabel != null && onAction != null)
        TextButton(onPressed: onAction, child: Text(actionLabel!)),
    ],
  );
}
