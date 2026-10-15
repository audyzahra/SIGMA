import 'package:flutter/material.dart';

import '../state/officer_store.dart';
import '../widgets/officer_page.dart';
import '../widgets/officer_widgets.dart';

class OfficerSecurityPage extends StatefulWidget {
  const OfficerSecurityPage({super.key, required this.store});

  final OfficerStore store;

  @override
  State<OfficerSecurityPage> createState() => _OfficerSecurityPageState();
}

class _OfficerSecurityPageState extends State<OfficerSecurityPage> {
  final _formKey = GlobalKey<FormState>();

  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _obscureCurrentPassword = true;
  bool _obscureNewPassword = true;
  bool _obscureConfirmPassword = true;
  bool _isSubmitting = false;

  @override
  void dispose() {
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  InputDecoration _passwordDecoration({
    required String label,
    required bool obscure,
    required VoidCallback onToggle,
  }) {
    return InputDecoration(
      labelText: label,
      prefixIcon: const Icon(Icons.lock),
      suffixIcon: IconButton(
        tooltip: obscure ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi',
        icon: Icon(
          obscure ? Icons.visibility_outlined : Icons.visibility_off_outlined,
        ),
        onPressed: onToggle,
      ),
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
    );
  }

  void _showMessage(String message) {
    if (!mounted) return;

    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(SnackBar(content: Text(message)));
  }

  Future<void> _handleSubmit() async {
    if (_isSubmitting) return;
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    try {
      await widget.store.updatePassword(
        newPassword: _newPasswordController.text,
        passwordConfirmation: _confirmPasswordController.text,
      );

      if (!mounted) return;

      _newPasswordController.clear();
      _confirmPasswordController.clear();

      _showMessage('Kata sandi berhasil diperbarui.');
    } catch (error) {
      final message = error.toString().replaceFirst('Exception: ', '');
      _showMessage(message);
    } finally {
      if (mounted) {
        setState(() => _isSubmitting = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return OfficerPage(
      title: 'Keamanan',
      onRefresh: () async {},
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Ubah Kata Sandi',
            style: TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w800,
              color: sigmaNavy,
              decoration: TextDecoration.none,
            ),
          ),
          const SizedBox(height: 8),
          SigmaCard(
            padding: const EdgeInsets.all(16),
            child: Material(
              color: Colors.transparent,
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Perbarui kata sandi untuk menjaga keamanan akun petugas.',
                    ),
                    const SizedBox(height: 20),
                    InputDecorator(
                      decoration: InputDecoration(
                        labelText: 'Kata sandi saat ini',
                        prefixIcon: const Icon(Icons.lock),
                        suffixIcon: IconButton(
                          tooltip: _obscureCurrentPassword
                              ? 'Tampilkan status'
                              : 'Sembunyikan status',
                          icon: Icon(
                            _obscureCurrentPassword
                                ? Icons.visibility_outlined
                                : Icons.visibility_off_outlined,
                          ),
                          onPressed: () {
                            setState(() {
                              _obscureCurrentPassword =
                                  !_obscureCurrentPassword;
                            });
                          },
                        ),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(
                              _obscureCurrentPassword
                                  ? '••••••••'
                                  : 'Kata sandi tidak dapat ditampilkan',
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _newPasswordController,
                      obscureText: _obscureNewPassword,
                      enabled: !_isSubmitting,
                      decoration: _passwordDecoration(
                        label: 'Kata sandi baru',
                        obscure: _obscureNewPassword,
                        onToggle: () {
                          setState(() {
                            _obscureNewPassword = !_obscureNewPassword;
                          });
                        },
                      ),
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Kata sandi baru wajib diisi.';
                        }
                        if (value.length < 8) {
                          return 'Kata sandi minimal 8 karakter.';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _confirmPasswordController,
                      obscureText: _obscureConfirmPassword,
                      enabled: !_isSubmitting,
                      decoration: _passwordDecoration(
                        label: 'Konfirmasi kata sandi baru',
                        obscure: _obscureConfirmPassword,
                        onToggle: () {
                          setState(() {
                            _obscureConfirmPassword = !_obscureConfirmPassword;
                          });
                        },
                      ),
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Konfirmasi kata sandi wajib diisi.';
                        }
                        if (value != _newPasswordController.text) {
                          return 'Konfirmasi kata sandi tidak cocok.';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton.icon(
                        onPressed: _isSubmitting ? null : _handleSubmit,
                        icon: _isSubmitting
                            ? const SizedBox(
                                width: 18,
                                height: 18,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: Colors.white,
                                ),
                              )
                            : const Icon(Icons.lock_reset),
                        label: Text(
                          _isSubmitting ? 'Menyimpan...' : 'Ubah Kata Sandi',
                        ),
                        style: FilledButton.styleFrom(
                          backgroundColor: sigmaRed,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(height: 16),
          const OfficerInfoCard(
            icon: Icons.shield_outlined,
            text: 'Kata sandi baru minimal 8 karakter. Jangan membagikan kata sandi kepada orang lain.',
          ),
        ],
      ),
    );
  }
}
