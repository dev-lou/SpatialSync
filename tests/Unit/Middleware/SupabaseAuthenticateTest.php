<?php

namespace Tests\Unit\Middleware;

use App\Http\AuthenticatedRequest;
use App\Http\Middleware\SupabaseAuthenticate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tests\TestCase;

class SupabaseAuthenticateTest extends TestCase
{
    private SupabaseAuthenticate $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new SupabaseAuthenticate;

        // Ensure clean session state between tests
        $this->app['session.store']->flush();

        // Register the login route so redirect()->route('login') works
        if (! Route::has('login')) {
            Route::get('/login')->name('login');
        }
    }

    private function makeRequest(): AuthenticatedRequest
    {
        $request = new AuthenticatedRequest;
        $request->setLaravelSession($this->app['session.store']);

        return $request;
    }

    /** @test */
    public function it_redirects_to_login_when_session_has_no_user_id(): void
    {
        $request = $this->makeRequest();
        $request->headers->set('Accept', 'text/html');

        /** @var SymfonyResponse $response */
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('/login', $response->headers->get('Location'));
    }

    /** @test */
    public function it_returns_401_json_when_session_has_no_user_id_and_request_expects_json(): void
    {
        $request = $this->makeRequest();
        $request->headers->set('Accept', 'application/json');

        /** @var SymfonyResponse $response */
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(401, $response->getStatusCode());
        $responseData = json_decode((string) $response->getContent(), true);
        $this->assertEquals('Unauthenticated.', $responseData['message']);
    }

    /** @test */
    public function it_passes_through_when_session_has_user_id(): void
    {
        $request = $this->makeRequest();
        $request->session()->put('supabase_user_id', 'user-abc-123');

        $nextCalled = false;

        /** @var SymfonyResponse $response */
        $response = $this->middleware->handle($request, function ($req) use (&$nextCalled) {
            $nextCalled = true;

            return new Response('OK');
        });

        $this->assertTrue($nextCalled);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function it_merges_auth_user_id_into_request(): void
    {
        $request = $this->makeRequest();
        $request->session()->put('supabase_user_id', 'user-abc-123');

        /** @var SymfonyResponse $response */
        $this->middleware->handle($request, function (AuthenticatedRequest $req) {
            $this->assertEquals('user-abc-123', $req->auth_user_id);

            return new Response('OK');
        });
    }

    /** @test */
    public function it_merges_all_auth_fields_into_request(): void
    {
        $request = $this->makeRequest();
        $request->session()->put('supabase_user_id', 'user-abc-123');
        $request->session()->put('supabase_user_email', 'test@example.com');
        $request->session()->put('supabase_user_name', 'Test User');
        $request->session()->put('supabase_user_plan', 'pro');
        $request->session()->put('supabase_user_admin', true);
        $request->session()->put('supabase_user_avatar', 'https://example.com/avatar.jpg');

        /** @var SymfonyResponse $response */
        $this->middleware->handle($request, function (AuthenticatedRequest $req) {
            $this->assertEquals('user-abc-123', $req->auth_user_id);
            $this->assertEquals('test@example.com', $req->auth_user_email);
            $this->assertEquals('Test User', $req->auth_user_name);
            $this->assertEquals('pro', $req->auth_user_plan);
            $this->assertTrue($req->auth_user_admin);
            $this->assertEquals('https://example.com/avatar.jpg', $req->auth_user_avatar);

            return new Response('OK');
        });
    }

    /** @test */
    public function it_uses_default_values_when_plan_and_admin_are_not_in_session(): void
    {
        $request = $this->makeRequest();
        $request->session()->put('supabase_user_id', 'user-abc-123');

        /** @var SymfonyResponse $response */
        $this->middleware->handle($request, function (AuthenticatedRequest $req) {
            $this->assertEquals('free', $req->auth_user_plan);
            $this->assertFalse($req->auth_user_admin);

            return new Response('OK');
        });
    }

    /** @test */
    public function it_sets_null_for_optional_fields_not_in_session(): void
    {
        $request = $this->makeRequest();
        $request->session()->put('supabase_user_id', 'user-abc-123');

        /** @var SymfonyResponse $response */
        $this->middleware->handle($request, function (AuthenticatedRequest $req) {
            $this->assertNull($req->auth_user_email);
            $this->assertNull($req->auth_user_name);
            $this->assertNull($req->auth_user_avatar);

            return new Response('OK');
        });
    }

    /** @test */
    public function it_returns_the_next_middleware_response_unmodified(): void
    {
        $request = $this->makeRequest();
        $request->session()->put('supabase_user_id', 'user-abc-123');

        $customResponse = new Response('Custom Content', 201, ['X-Custom' => 'value']);

        /** @var SymfonyResponse $response */
        $response = $this->middleware->handle($request, function ($req) use ($customResponse) {
            return $customResponse;
        });

        $this->assertSame($customResponse, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Custom Content', $response->getContent());
        $this->assertEquals('value', $response->headers->get('X-Custom'));
    }

    /** @test */
    public function it_accepts_guards_parameter_without_error(): void
    {
        $request = $this->makeRequest();
        $request->session()->put('supabase_user_id', 'user-abc-123');

        $nextCalled = false;

        /** @var SymfonyResponse $response */
        $response = $this->middleware->handle($request, function ($req) use (&$nextCalled) {
            $nextCalled = true;

            return new Response('OK');
        }, 'web', 'api');

        $this->assertTrue($nextCalled);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
