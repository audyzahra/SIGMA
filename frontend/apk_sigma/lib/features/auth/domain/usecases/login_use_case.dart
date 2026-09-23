import '../repositories/auth_repository.dart';

class LoginUseCase {
  const LoginUseCase(this._repository);
  final AuthRepository _repository;

  Future<Map<String, dynamic>> call(String email, String password, String role) =>
      _repository.login(email, password, role);
}
