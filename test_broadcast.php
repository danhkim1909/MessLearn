<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$msg = \App\Models\Message::first();
$event = new \App\Events\MessageSent($msg);

$broadcastEvent = new \Illuminate\Broadcasting\BroadcastEvent($event);
$reflection = new ReflectionClass($broadcastEvent);
$method = $reflection->getMethod("getPayloadFromEvent");
$method->setAccessible(true);
$payload = $method->invoke($broadcastEvent, $event);
print_r($payload);

