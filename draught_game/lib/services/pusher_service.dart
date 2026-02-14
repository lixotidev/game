import 'package:logger/logger.dart';

class PusherService {
  static final PusherService _instance = PusherService._internal();
  factory PusherService() => _instance;
  PusherService._internal();

  final Logger _logger = Logger();

  static const String appKey = 'draught_key';
  static const String host = '10.0.2.2'; 
  static const int port = 8080;

  Future<void> init({
    required Function(dynamic) onEvent,
    required String channelName,
  }) async {
    // Temporarily disabled for compilation stability
    _logger.i("Pusher simulated for $channelName");
  }

  Future<void> subscribeToGame(int gameId, Function(dynamic) onEvent) async {
    // await init(onEvent: onEvent, channelName: 'private-game.$gameId');
  }

  Future<void> disconnect() async {
    // client?.disconnect();
  }
}
