import 'auth_api.dart';

class AuthRemoteDataSource {
  AuthRemoteDataSource({AuthApi? api}) : _api = api ?? AuthApi();
  final AuthApi _api;

  Future<Map<String, dynamic>> post(String path, Map<String, dynamic> body, {String? token}) async {
    return _api.post(path, body, token: token);
  }
}
