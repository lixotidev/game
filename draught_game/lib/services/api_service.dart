import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:logger/logger.dart';

class ApiService {
  // Use '10.0.2.2' for Android Emulator to access localhost, 
  // or your actual local IP (e.g., '192.168.1.x') if testing on physical device.
  // For web, 'localhost' works.
  static const String baseUrl = 'http://t4cock0c04g40s0w8o8gswc4.167.86.90.159.sslip.io/api';
  
  final Logger _logger = Logger();
  
  // Singleton pattern
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  /// Get stored auth token
  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  /// Store auth token
  Future<void> _setToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }

  /// Clear auth token
  Future<void> _clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }

  /// Helper for headers
  Future<Map<String, String>> _getHeaders() async {
    final token = await _getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // --- Authentication ---

  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    String? phone,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/register'),
      headers: await _getHeaders(),
      body: jsonEncode({
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': password,
        'phone': phone,
      }),
    );
    
    _logger.d('Register Response: ${response.statusCode} - ${response.body}');
    
    final data = _handleResponse(response);
    if (response.statusCode == 201 && data is Map) {
      await _setToken(data['access_token']);
    }
    return data;
  }

  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: await _getHeaders(),
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );
    
    final data = _handleResponse(response);
    if (response.statusCode == 200 && data is Map) {
      await _setToken(data['access_token']);
    }
    return data;
  }

  Future<void> logout() async {
    final response = await http.post(
      Uri.parse('$baseUrl/logout'),
      headers: await _getHeaders(),
    );
    if (response.statusCode == 200) {
      await _clearToken();
    }
  }

  Future<Map<String, dynamic>> getUser() async {
    final response = await http.get(
      Uri.parse('$baseUrl/user'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response);
  }

  // --- Wallet ---

  Future<Map<String, dynamic>> getBalance() async {
    final response = await http.get(
      Uri.parse('$baseUrl/wallet/balance'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> getTransactions() async {
    final response = await http.get(
      Uri.parse('$baseUrl/wallet/transactions'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> initializeDeposit(double amount) async {
    final response = await http.post(
      Uri.parse('$baseUrl/wallet/deposit/initialize'),
      headers: await _getHeaders(),
      body: jsonEncode({'amount': amount}),
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> verifyDeposit(String reference) async {
    final response = await http.post(
      Uri.parse('$baseUrl/wallet/deposit/verify'),
      headers: await _getHeaders(),
      body: jsonEncode({'reference': reference}),
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> requestWithdrawal({
    required double amount,
    required String accountNumber,
    required String bankCode,
    required String accountName,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/wallet/withdraw/request'),
      headers: await _getHeaders(),
      body: jsonEncode({
        'amount': amount,
        'account_number': accountNumber,
        'bank_code': bankCode,
        'account_name': accountName,
      }),
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> getBanks() async {
    final response = await http.get(
      Uri.parse('$baseUrl/wallet/withdraw/banks'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response);
  }

  // --- Games ---

  Future<List<dynamic>> getLobby() async {
    final response = await http.get(
      Uri.parse('$baseUrl/games/lobby'),
      headers: await _getHeaders(),
    );
    return jsonDecode(response.body);
  }

  Future<Map<String, dynamic>> createGame(double betAmount) async {
    final response = await http.post(
      Uri.parse('$baseUrl/games/create'),
      headers: await _getHeaders(),
      body: jsonEncode({'bet_amount': betAmount}),
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> joinGame(String code) async {
    final response = await http.post(
      Uri.parse('$baseUrl/games/$code/join'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> getGameDetails(int id) async {
    final response = await http.get(
      Uri.parse('$baseUrl/games/$id'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> makeMove({
    required int gameId,
    required Map<String, dynamic> from,
    required Map<String, dynamic> to,
    List<Map<String, dynamic>>? captured,
    bool? isKingPromotion,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/games/$gameId/move'),
      headers: await _getHeaders(),
      body: jsonEncode({
        'from': from,
        'to': to,
        'captured': captured,
        'is_king_promotion': isKingPromotion,
      }),
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> resign(int id) async {
    final response = await http.post(
      Uri.parse('$baseUrl/games/$id/resign'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> getMyGames() async {
    final response = await http.get(
      Uri.parse('$baseUrl/games/my-games'),
      headers: await _getHeaders(),
    );
    return _handleResponse(response);
  }

  // --- Leaderboard ---

  Future<List<dynamic>> getTopPlayers() async {
    final response = await http.get(
      Uri.parse('$baseUrl/leaderboard/top-players'),
      headers: await _getHeaders(),
    );
    return jsonDecode(response.body);
  }

  Future<List<dynamic>> getTopEarners() async {
    final response = await http.get(
      Uri.parse('$baseUrl/leaderboard/top-earners'),
      headers: await _getHeaders(),
    );
    return jsonDecode(response.body);
  }

  // --- Common Response Handling ---

  dynamic _handleResponse(http.Response response) {
    dynamic body;
    try {
      body = jsonDecode(response.body);
    } catch (e) {
      _logger.e('Failed to parse response: ${response.body}');
      throw Exception('Server returned an invalid format. Status: ${response.statusCode}');
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return body;
    } else {
      _logger.e('API Error: ${response.statusCode} - $body');
      
      if (body is Map && body.containsKey('errors')) {
        // Handle Laravel validation errors
        final errors = body['errors'] as Map;
        final firstError = errors.values.first;
        if (firstError is List) {
          throw Exception(firstError.first);
        }
      }
      
      throw Exception(body['message'] ?? 'Something went wrong (Error ${response.statusCode})');
    }
  }
}
