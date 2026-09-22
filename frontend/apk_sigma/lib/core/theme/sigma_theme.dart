import 'package:flutter/material.dart';

abstract final class SigmaColors {
  static const primary = Color(0xFFC82828);
  static const secondary = Color(0xFFF97316);
  static const success = Color(0xFF16824B);
  static const info = Color(0xFF2563EB);
  static const ink = Color(0xFF172033);
  static const muted = Color(0xFF64748B);
  static const canvas = Color(0xFFF8FAFC);
}

abstract final class SigmaTheme {
  static final light = ThemeData(
    useMaterial3: true,
    colorScheme: ColorScheme.fromSeed(
      seedColor: SigmaColors.primary,
      primary: SigmaColors.primary,
      secondary: SigmaColors.secondary,
      surface: Colors.white,
    ),
    scaffoldBackgroundColor: SigmaColors.canvas,
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: Colors.white,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: SigmaColors.primary, width: 2),
      ),
    ),
  );
}
