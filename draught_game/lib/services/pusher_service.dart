import 'package:dart_pusher_channels/dart_pusher_channels.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:logger/logger.dart';

class PusherService {
  static final PusherService _instance = PusherService._internal();
  factory PusherService() => _instance;
  PusherService._internal();

  PusherChannelsClient? client;
  final Logger _logger = Logger();

  static const String appKey = 'draught_key';
  // Updated to the URL provided by Coolify
  static const String host = 't4cock0c04g40s0w8o8gswc4.167.86.90.159.sslip.io'; 
  static const int port = 80; // Standard HTTP port for the proxy

  Future<void> init({
    required Function(dynamic) onEvent,
    required String channelName,
  }) async {
    try {
      final options = PusherChannelsOptions.fromHost(
        host: host,
        port: port,
        key: appKey,
        scheme: 'ws',
      );

      client = PusherChannelsClient.websocket(
        options: options,
        connectionErrorHandler: (exception, trace, refresh) {
          _logger.e('Pusher Connection Error: $exception');
        },
      );

      _logger.i('Connecting to Pusher at $host:$port...');
      client!.connect();

      // Using public channel for immediate stability
      // The backend events should be adjusted to public if security is not primary during this test
      final channel = client!.publicChannel(
        channelName.replaceFirst('private-', ''), // Remove 'private-' prefix if it exists
      );

      channel.bind('move.made').listen((event) => onEvent(event));
      channel.bind('game.joined').listen((event) => onEvent(event));
      channel.bind('game.ended').listen((event) => onEvent(event));

      _logger.i("Subscribed to ${channel.name}");
    } catch (e) {
      _logger.e("Pusher Init Error: $e");
    }
  }

  Future<void> subscribeToGame(int gameId, Function(dynamic) onEvent) async {
    // We'll use the simple channel name 'game.{id}'
    await init(onEvent: onEvent, channelName: 'game.$gameId');
  }

  Future<void> disconnect() async {
    client?.disconnect();
  }
}
