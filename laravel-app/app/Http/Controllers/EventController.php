<?php

namespace App\Http\Controllers;

use App\Http\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Realtime Web Metrics API",
 *     version="1.0.0",
 *     description="Laravel API for collecting HTTP/web analytics events. Events are published to Kafka and aggregated in real time by the companion Spring Boot Kafka Streams service.",
 *     @OA\Contact(email="dev@example.com")
 * )
 * @OA\Server(url=L5_SWAGGER_CONST_HOST, description="API server")
 * @OA\Tag(name="Events", description="Publish HTTP metric events for real-time aggregation")
 */
class EventController extends Controller
{
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * @OA\Post(
     *     path="/events/registerEvent",
     *     tags={"Events"},
     *     summary="Register an HTTP metric event",
     *     description="Validates the event type and publishes the event onto the 'http-events' Kafka topic for real-time windowed aggregation by the Spring Boot processing service.",
     *     security={{"apiKeyAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"event_type","url"},
     *             @OA\Property(property="event_type", type="string", example="click-event", description="One of: click-event, view_event, purchase_event"),
     *             @OA\Property(property="url", type="string", example="/products/42")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event accepted and published to Kafka",
     *         @OA\JsonContent(type="boolean", example=true)
     *     ),
     *     @OA\Response(response=400, description="Invalid or missing API key"),
     *     @OA\Response(response=422, description="Invalid event type")
     * )
     *
     * @throws \Exception
     */
    public function registerEvent(Request $request): JsonResponse
    {
        $event_type = $request->input('event_type');
        $url = $request->input('url');
        $api_id = $request->header("X-API-KEY");
        $registerEvent = $this->eventService->registerEvent($api_id, $event_type, $url);
        return response()->json($registerEvent);
    }
}
