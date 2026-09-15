<?php

namespace App\Support;

/**
 * Helpers for attributing issue pins and chat messages left by a client who
 * opened a share link without creating an account.
 *
 * A guest has no row in `users`, so `build_issues.created_by` and
 * `build_messages.user_id` are stored as NULL. To keep the reviewer's name
 * visible to the build owner without a schema change, the name travels inside
 * the free-text field:
 *
 *   - issues:   description starts with "[Guest review by <name>]"
 *   - messages: text is stored as "<name>: <message>"
 *
 * Both are only ever parsed when the author id is NULL, so members' own
 * content is never reinterpreted.
 */
class GuestReview
{
    public const ISSUE_MARKER = '[Guest review by ';

    /** Display name used when a guest's own name cannot be recovered. */
    public const FALLBACK_NAME = 'Client';

    /**
     * Build an issue description that carries the reviewer's name.
     */
    public static function issueDescription(string $reviewerName, ?string $body): string
    {
        $marker = self::ISSUE_MARKER.self::cleanName($reviewerName).']';

        if ($body === null || trim($body) === '') {
            return $marker;
        }

        return $marker."\n\n".trim($body);
    }

    /**
     * Recover the reviewer's name from a guest-authored issue.
     */
    public static function issueReviewerName(?string $description): ?string
    {
        if ($description === null || ! str_starts_with($description, self::ISSUE_MARKER)) {
            return null;
        }

        $end = strpos($description, ']');

        if ($end === false) {
            return null;
        }

        $start = strlen(self::ISSUE_MARKER);
        $name = trim(substr($description, $start, $end - $start));

        return $name === '' ? null : $name;
    }

    /**
     * Strip the attribution marker from an issue description for display.
     */
    public static function issueBody(?string $description): ?string
    {
        if ($description === null || self::issueReviewerName($description) === null) {
            return $description;
        }

        $closing = strpos($description, ']');

        if ($closing === false) {
            return $description;
        }

        $body = trim(substr($description, $closing + 1));

        return $body === '' ? null : $body;
    }

    /**
     * Prefix a guest chat message with the reviewer's name.
     */
    public static function message(string $reviewerName, string $text): string
    {
        return self::cleanName($reviewerName).': '.trim($text);
    }

    /**
     * Split a guest chat message into [name, body]. Members' messages (author id
     * present) are never passed here.
     *
     * @return array{0: string, 1: string}
     */
    public static function splitMessage(?string $message): array
    {
        $message = (string) $message;
        $separator = strpos($message, ': ');

        if ($separator === false || $separator > 60) {
            return [self::FALLBACK_NAME, $message];
        }

        return [trim(substr($message, 0, $separator)), trim(substr($message, $separator + 2))];
    }

    /**
     * Keep a guest-supplied name short, printable and free of the marker syntax.
     */
    public static function cleanName(string $name): string
    {
        $name = str_replace(['[', ']', "\n", "\r"], '', trim($name));
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;

        return $name === '' ? self::FALLBACK_NAME : mb_substr($name, 0, 60);
    }
}
