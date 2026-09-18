import 'package:flutter/material.dart';

const sigmaRed = Color(0xFFC62828);
const sigmaNavy = Color(0xFF1F2937);

class SigmaHeader extends StatelessWidget {
  const SigmaHeader({super.key, required this.subtitle});

  final String subtitle;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(22),
          child: Image.asset(
            'assets/images/logo.jpeg',
            width: 112,
            height: 112,
            fit: BoxFit.cover,
          ),
        ),
        const SizedBox(height: 14),
        const Text(
          'SIGMA',
          style: TextStyle(
            color: sigmaNavy,
            fontSize: 28,
            fontWeight: FontWeight.w900,
          ),
        ),
        Text(subtitle, style: const TextStyle(color: Colors.black54)),
      ],
    );
  }
}

class SigmaButton extends StatelessWidget {
  const SigmaButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.busy = false,
  });

  final String label;
  final VoidCallback? onPressed;
  final bool busy;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      height: 52,
      child: DecoratedBox(
        decoration: BoxDecoration(
          gradient: const LinearGradient(colors: [sigmaRed, Color(0xFFF97316)]),
          borderRadius: BorderRadius.circular(14),
        ),
        child: FilledButton(
          style: FilledButton.styleFrom(
            backgroundColor: Colors.transparent,
            shadowColor: Colors.transparent,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(14),
            ),
          ),
          onPressed: busy ? null : onPressed,
          child: busy
              ? const CircularProgressIndicator(color: Colors.white)
              : Text(
                  label,
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
        ),
      ),
    );
  }
}
