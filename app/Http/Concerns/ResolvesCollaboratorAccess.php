<?php

namespace App\Http\Concerns;

use App\Http\AuthenticatedRequest;
use App\Support\GuestReview;

/**
 * Shared access rules for the collaboration endpoints (issues and chat), which
 * are reachable by signed-in members and by guest reviewers holding a share
 * link. Requires the host controller to expose `$this->supabase`.
 */
trait ResolvesCollaboratorAccess
{
    /**
     * The reviewer name carried by the session, if this request is a guest.
     */
    protected function guestReviewerName(AuthenticatedRequest $request): ?string
    {
        $name = $request->input('collab_guest_name');

        return is_string($name) && $name !== '' ? $name : null;
    }

    /**
     * Is this request from a guest reviewing through a share link?
     */
    protected function isGuestReviewer(AuthenticatedRequest $request): bool
    {
        return $request->auth_user_id === null && $this->guestReviewerName($request) !== null;
    }

    /**
     * Confirm the guest session actually holds a live token for this build.
     * The token is the only credential a guest has, so it is re-validated
     * server-side on every write rather than trusted from the session.
     */
    protected function guestHasShareAccess(AuthenticatedRequest $request, string $buildId): bool
    {
        $token = $request->input('collab_guest_token');
        $sessionBuildId = $request->input('collab_guest_build_id');

        if (! is_string($token) || $token === '' || $sessionBuildId !== $buildId) {
            return false;
        }

        $shares = $this->supabase->select('build_shares', ['*'], [
            'build_id' => $buildId,
            'share_token' => $token,
        ]);

        if ($shares === []) {
            return false;
        }

        $share = (array) $shares[0];
        $expiresAt = $share['expires_at'] ?? null;

        if (is_string($expiresAt) && $expiresAt !== '' && strtotime($expiresAt) < time()) {
            return false;
        }

        return true;
    }

    /**
     * Members may comment (any role); guests may only comment.
     */
    protected function collaboratorCanComment(AuthenticatedRequest $request, string $buildId): bool
    {
        if ($this->isGuestReviewer($request)) {
            return $this->guestHasShareAccess($request, $buildId);
        }

        return $this->memberHasBuildAccess($request, $buildId);
    }

    /**
     * Only signed-in members may modify or delete existing content.
     */
    protected function collaboratorCanModerate(AuthenticatedRequest $request, string $buildId): bool
    {
        if ($this->isGuestReviewer($request)) {
            return false;
        }

        return $this->memberHasBuildAccess($request, $buildId);
    }

    /**
     * Owner or invited member check (the pre-existing rule).
     */
    protected function memberHasBuildAccess(AuthenticatedRequest $request, string $buildId): bool
    {
        $userId = $request->auth_user_id;

        if ($userId === null) {
            return false;
        }

        $builds = $this->supabase->select('builds', ['created_by'], ['id' => $buildId]);

        if ($builds === []) {
            return false;
        }

        if (($builds[0]['created_by'] ?? null) === $userId) {
            return true;
        }

        $members = $this->supabase->select('build_members', ['role'], [
            'build_id' => $buildId,
            'user_id' => $userId,
        ]);

        return $members !== [];
    }

    /**
     * Attribution strings for a guest-authored record.
     */
    protected function guestNameOrFallback(AuthenticatedRequest $request): string
    {
        return $this->guestReviewerName($request) ?? GuestReview::FALLBACK_NAME;
    }
}
