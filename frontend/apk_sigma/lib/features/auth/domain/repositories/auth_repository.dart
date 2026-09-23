abstract interface class AuthRepository {
  Future<Map<String, dynamic>> login(String email, String password, String role);
}
