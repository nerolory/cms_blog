<?php

namespace Tests\Feature;

use App\Http\Middleware\AssignRequestId;
use Tests\TestCase;

/**
 * Класс assign request id.
 */
class AssignRequestIdTest extends TestCase
{
    /**
     * test response includes request id header.
     */
    public function test_response_includes_request_id_header(): void
    {
        $response = $this->get('/');
        $response->assertHeader(AssignRequestId::HEADER);
        $this->assertNotSame('', (string) $response->headers->get(AssignRequestId::HEADER));
    }

    /**
     * test incoming request id is preserved.
     */
    public function test_incoming_request_id_is_preserved(): void
    {
        $requestId = 'test-request-id-12345';
        $response = $this->withHeader(AssignRequestId::HEADER, $requestId)->get('/');
        $response->assertHeader(AssignRequestId::HEADER, $requestId);
    }
}
