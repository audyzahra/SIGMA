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
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(
          color: Color(0xFFE5E7EB),
        ),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(
          color: SigmaColors.primary,
          width: 2,
        ),
      ),
    ),
  );

  static final dark = ThemeData(
  useMaterial3: true,
  brightness: Brightness.dark,
  colorScheme: const ColorScheme.dark(
    primary: SigmaColors.primary,
    secondary: SigmaColors.secondary,
    surface: Color(0xFF1E293B),
    surfaceContainerHighest: Color(0xFF263449),
    onSurface: Color(0xFFF8FAFC),
    onSurfaceVariant: Color(0xFFCBD5E1),
    outline: Color(0xFF475569),
  ),
  scaffoldBackgroundColor: const Color(0xFF0F172A),
  appBarTheme: const AppBarTheme(
    backgroundColor: Color(0xFF0F172A),
    foregroundColor: Color(0xFFF8FAFC),
  ),
  inputDecorationTheme: InputDecorationTheme(
    filled: true,
    fillColor: const Color(0xFF1E293B),
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
    ),
    enabledBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: const BorderSide(
        color: Color(0xFF334155),
      ),
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: const BorderSide(
        color: SigmaColors.primary,
        width: 2,
      ),
    ),
  ),
);
}

abstract final class SigmaThemeController {
  static final mode = ValueNotifier<ThemeMode>(ThemeMode.light);

  static void setMode(ThemeMode value) {
    mode.value = value;
  }
}