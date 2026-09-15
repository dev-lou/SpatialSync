<?php

namespace Tests\Unit\Support;

use App\Support\GuestReview;
use PHPUnit\Framework\TestCase;

class GuestReviewTest extends TestCase
{
    public function test_issue_description_round_trips_the_reviewer_name(): void
    {
        $description = GuestReview::issueDescription('Maria Santos', 'The window sits too high.');

        $this->assertSame('Maria Santos', GuestReview::issueReviewerName($description));
        $this->assertSame('The window sits too high.', GuestReview::issueBody($description));
    }

    public function test_issue_description_without_a_body_is_just_the_marker(): void
    {
        $description = GuestReview::issueDescription('Maria', null);

        $this->assertSame('Maria', GuestReview::issueReviewerName($description));
        $this->assertNull(GuestReview::issueBody($description));
    }

    public function test_member_authored_descriptions_are_left_alone(): void
    {
        $this->assertNull(GuestReview::issueReviewerName('Please widen this doorway'));
        $this->assertSame('Please widen this doorway', GuestReview::issueBody('Please widen this doorway'));
        $this->assertNull(GuestReview::issueBody(null));
    }

    public function test_clean_name_shrinks_whitespace_and_drops_brackets(): void
    {
        $this->assertSame('Maria Santos', GuestReview::cleanName("  Maria\n\tSantos  "));
        $this->assertSame('Maria Reviewed Santos', GuestReview::cleanName('Maria [Reviewed] Santos'));
        $this->assertSame(GuestReview::FALLBACK_NAME, GuestReview::cleanName('   '));
    }

    public function test_a_crafted_name_cannot_break_the_description_marker(): void
    {
        // A guest cannot smuggle in closing brackets to fake a second marker.
        $description = GuestReview::issueDescription('[Guest review by Mallory]', 'body');

        $this->assertSame('Guest review by Mallory', GuestReview::issueReviewerName($description));
        $this->assertSame('body', GuestReview::issueBody($description));
    }

    public function test_clean_name_is_bounded(): void
    {
        $long = str_repeat('a', 200);

        $this->assertSame(60, mb_strlen(GuestReview::cleanName($long)));
    }

    public function test_message_prefix_is_split_back_apart(): void
    {
        $stored = GuestReview::message('Maria', 'Can we widen this?');

        $this->assertSame(['Maria', 'Can we widen this?'], GuestReview::splitMessage($stored));
    }

    public function test_message_without_a_prefix_falls_back_to_the_client_label(): void
    {
        $this->assertSame(
            [GuestReview::FALLBACK_NAME, 'no separator here'],
            GuestReview::splitMessage('no separator here')
        );
    }
}
