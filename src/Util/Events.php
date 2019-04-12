<?php
namespace SignalWire\Util;

abstract class Events {
  const SocketOpen = 'signalwire.socket.open';
  const SocketClose = 'signalwire.socket.close';
  const SocketError = 'signalwire.socket.error';
  const SocketMessage = 'signalwire.socket.message';

  // Internal events
  const SpeedTest = 'signalwire.internal.speedtest';
  const Disconnect = 'signalwire.internal.disconnect';
  const Connect = 'signalwire.internal.connect';

  // Global Events
  const Ready = 'signalwire.ready';
  const Error = 'signalwire.error';
  const Notification = 'signalwire.notification';

  // Blade Events
  // const Messages = 'signalwire.messages';
  // const Calls = 'signalwire.calls';
}
