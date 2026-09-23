import '../datasources/auth_remote_data_source.dart';
import '../../domain/repositories/auth_repository.dart';

class AuthRepositoryImpl implements AuthRepository {
  AuthRepositoryImpl({AuthRemoteDataSource? dataSource}) : _dataSource = dataSource ?? AuthRemoteDataSource();
  final AuthRemoteDataSource _dataSource;

  @override
  Future<Map<String, dynamic>> login(String email, String password, String role) =>
      _dataSource.post('/login', {'email': email, 'password': password, 'role': role});

  Future<void> register(String name, String email, String password, String confirmation, String role) async {
    await _dataSource.post('/register', {'name': name, 'email': email, 'password': password, 'password_confirmation': confirmation, 'role': role});
  }

  Future<void> verifyEmail(String email, String code) async => _dataSource.post('/verify-email', {'email': email, 'otp_code': code});
  Future<void> resendCode(String email) async => _dataSource.post('/resend-code', {'email': email});
  Future<void> logout(String token) async => _dataSource.post('/logout', {}, token: token);
}
