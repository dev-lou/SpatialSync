<?php

namespace App\Http;

use Illuminate\Http\Request;

/**
 * An authenticated HTTP request with user data merged by the SupabaseAuthenticate middleware.
 *
 * @property-read string|null $auth_user_id
 * @property-read string|null $auth_user_email
 * @property-read string|null $auth_user_name
 * @property-read string      $auth_user_plan
 * @property-read bool        $auth_user_admin
 * @property-read string|null $auth_user_avatar
 */
class AuthenticatedRequest extends Request
{
    //
}
