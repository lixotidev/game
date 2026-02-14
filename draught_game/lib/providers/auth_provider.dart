import 'package:flutter/material.dart';
import 'package:logger/logger.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  final Logger _logger = Logger();
  
  Map<String, dynamic>? _user;
  double _balance = 0.0;
  bool _isLoading = false;
  String? _error;

  Map<String, dynamic>? get user => _user;
  double get balance => _balance;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isAuthenticated => _user != null;

  Future<void> login(String email, String password) async {
    _setLoading(true);
    _clearError();
    try {
      final response = await _apiService.login(email, password);
      _user = response['user'];
      _balance = double.parse(_user!['wallet_balance'].toString());
      notifyListeners();
    } catch (e) {
      _error = e.toString();
    } finally {
      _setLoading(false);
    }
  }

  Future<void> register(String name, String email, String password, {String? phone}) async {
    _setLoading(true);
    _clearError();
    try {
      final response = await _apiService.register(
        name: name,
        email: email,
        password: password,
        phone: phone,
      );
      _user = response['user'];
      _balance = 0.0;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
    } finally {
      _setLoading(false);
    }
  }

  Future<void> logout() async {
    await _apiService.logout();
    _user = null;
    _balance = 0.0;
    notifyListeners();
  }

  Future<void> fetchUser() async {
    try {
      final response = await _apiService.getUser();
      _user = response;
      _balance = double.parse(_user!['wallet_balance'].toString());
      notifyListeners();
    } catch (e) {
      _logger.e('Failed to fetch user: $e');
    }
  }

  Future<void> refreshBalance() async {
    try {
      final response = await _apiService.getBalance();
      _balance = double.parse(response['balance'].toString());
      notifyListeners();
    } catch (e) {
      _logger.e('Failed to refresh balance: $e');
    }
  }

  void _setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }

  void _clearError() {
    _error = null;
    notifyListeners();
  }
}
