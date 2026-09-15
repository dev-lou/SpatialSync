<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The client review link is the product's headline flow: a client opens a URL,
 * types a name, and comments on the model without ever creating an account.
 *
 * These tests drive the real routes, middleware, session and views; only the
 * Supabase REST calls are faked, so the flow is verifiable without credentials.
 */
class GuestReviewLinkTest extends TestCase
{
    private const TOKEN = 'client-review-token-abcdef123456';

    private const BUILD_ID = '11111111-1111-1111-1111-111111111111';

    private const OTHER_BUILD_ID = '22222222-2222-2222-2222-222222222222';

    private const REVIEWER = 'Maria from the client side';

    /** The row `build_shares` returns; tests mutate it to simulate expiry. */
    private array $shareRow = [];

    /** Every write the app attempted, so tests can inspect attribution. */
    private array $captured = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->shareRow = [
            'id' => 'share-1',
            'build_id' => self::BUILD_ID,
            'share_token' => self::TOKEN,
            'access_level' => 'view',
            'expires_at' => now()->addDays(7)->toIso8601String(),
        ];

        Http::fake(function (ClientRequest $request) {
            $url = $request->url();
            $method = $request->method();

            if ($method === 'POST' && (str_contains($url, 'build_messages') || str_contains($url, 'build_issues'))) {
                $table = str_contains($url, 'build_messages') ? 'build_messages' : 'build_issues';
                $payload = $request->data();
                $this->captured[] = ['table' => $table, 'data' => $payload];

                return Http::response([$payload], 201);
            }

            if (str_contains($url, 'build_shares')) {
                return Http::response($this->shareRow === [] ? [] : [$this->shareRow], 200);
            }

            if (str_contains($url, 'builds')) {
                return Http::response([[
                    'id' => self::BUILD_ID,
                    'name' => 'Demo house',
                    'created_by' => '99999999-9999-9999-9999-999999999999',
                    'current_floor' => 1,
                    'roof_visible' => true,
                ]], 200);
            }

            return Http::response([], 200);
        });
    }

    /** The session a client has after entering their name on the join screen. */
    private function joinedGuest(string $buildId = self::BUILD_ID): array
    {
        return ['guest_review' => [
            'token' => self::TOKEN,
            'build_id' => $buildId,
            'name' => self::REVIEWER,
        ]];
    }

    private function writesTo(string $table): array
    {
        return array_values(array_filter($this->captured, fn (array $w) => $w['table'] === $table));
    }

    public function test_link_without_a_name_asks_the_client_who_they_are(): void
    {
        $this->get('/share/'.self::TOKEN)
            ->assertStatus(200)
            ->assertSee("What's your name?", false);
    }

    public function test_unknown_or_revoked_token_shows_the_inactive_page(): void
    {
        $this->shareRow = [];

        $this->get('/share/'.self::TOKEN)
            ->assertStatus(404)
            ->assertSee("This review link isn't active", false);
    }

    public function test_expired_token_shows_the_inactive_page(): void
    {
        $this->shareRow['expires_at'] = now()->subDay()->toIso8601String();

        $this->get('/share/'.self::TOKEN)->assertStatus(404);
    }

    public function test_joining_records_the_name_and_returns_to_the_model(): void
    {
        $this->post('/share/'.self::TOKEN.'/join', ['name' => self::REVIEWER])
            ->assertRedirect(route('share.show', ['token' => self::TOKEN]));

        $this->assertSame(self::REVIEWER, session('guest_review_name_'.self::TOKEN));
    }

    public function test_joining_requires_a_name(): void
    {
        $this->post('/share/'.self::TOKEN.'/join', ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_a_named_client_gets_the_model_instead_of_the_join_screen(): void
    {
        $this->withSession(['guest_review_name_'.self::TOKEN => self::REVIEWER])
            ->get('/share/'.self::TOKEN)
            ->assertStatus(200)
            ->assertDontSee("What's your name?", false);
    }

    public function test_a_client_comment_is_stored_with_their_name_attached(): void
    {
        $this->withSession($this->joinedGuest())
            ->postJson('/editor/builds/'.self::BUILD_ID.'/messages', ['message' => 'Move this wall 30cm left'])
            ->assertStatus(201);

        $writes = $this->writesTo('build_messages');

        $this->assertCount(1, $writes);
        $this->assertNull($writes[0]['data']['user_id']);
        $this->assertSame(self::REVIEWER.': Move this wall 30cm left', $writes[0]['data']['message']);
    }

    public function test_a_client_pin_is_attributed_but_never_as_a_member(): void
    {
        $this->withSession($this->joinedGuest())
            ->postJson('/editor/builds/'.self::BUILD_ID.'/issues', [
                'title' => 'Window clashes with the chimney',
                'description' => 'Please shift it to the right.',
                'priority' => 'high',
            ])
            ->assertStatus(201);

        $writes = $this->writesTo('build_issues');

        $this->assertCount(1, $writes);
        $this->assertNull($writes[0]['data']['created_by']);
        $this->assertStringStartsWith('[Guest review by '.self::REVIEWER.']', $writes[0]['data']['description']);
    }

    public function test_a_client_cannot_write_to_a_different_build(): void
    {
        $this->withSession($this->joinedGuest())
            ->postJson('/editor/builds/'.self::OTHER_BUILD_ID.'/messages', ['message' => 'not mine'])
            ->assertStatus(403);

        $this->assertSame([], $this->writesTo('build_messages'));
    }

    public function test_a_revoked_token_stops_writing_even_with_a_live_session(): void
    {
        $this->shareRow = [];

        $this->withSession($this->joinedGuest())
            ->postJson('/editor/builds/'.self::BUILD_ID.'/messages', ['message' => 'should not save'])
            ->assertStatus(403);

        $this->assertSame([], $this->writesTo('build_messages'));
    }

    public function test_an_expired_token_stops_writing_even_with_a_live_session(): void
    {
        $this->shareRow['expires_at'] = now()->subMinute()->toIso8601String();

        $this->withSession($this->joinedGuest())
            ->postJson('/editor/builds/'.self::BUILD_ID.'/messages', ['message' => 'should not save'])
            ->assertStatus(403);
    }

    /**
     * Moderation lives behind the member-only `auth` group, which a share link
     * does not satisfy, so a client is rejected before any policy runs.
     */
    public function test_a_client_cannot_moderate_other_peoples_pins(): void
    {
        $this->withSession($this->joinedGuest())
            ->patchJson('/editor/builds/'.self::BUILD_ID.'/issues/issue-1/status', ['status' => 'resolved'])
            ->assertStatus(401);

        $this->assertSame([], $this->writesTo('build_issues'));
    }

    public function test_a_client_cannot_edit_the_model_geometry(): void
    {
        $this->withSession($this->joinedGuest())
            ->postJson('/editor/builds/'.self::BUILD_ID.'/parts', ['preset_id' => 'wall', 'floor' => 1])
            ->assertStatus(401);
    }

    public function test_an_anonymous_visitor_cannot_post_comments_at_all(): void
    {
        $this->postJson('/editor/builds/'.self::BUILD_ID.'/messages', ['message' => 'hello'])
            ->assertStatus(401);
    }

    public function test_a_signed_in_member_comment_is_not_relabelled_as_a_guest(): void
    {
        $this->withSession([
            'supabase_user_id' => '99999999-9999-9999-9999-999999999999',
            'supabase_user_name' => 'Lou',
        ])
            ->postJson('/editor/builds/'.self::BUILD_ID.'/messages', ['message' => 'Member note'])
            ->assertStatus(201);

        $writes = $this->writesTo('build_messages');

        $this->assertCount(1, $writes);
        $this->assertSame('Member note', $writes[0]['data']['message']);
        $this->assertSame('99999999-9999-9999-9999-999999999999', $writes[0]['data']['user_id']);
    }
}
