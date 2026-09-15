<?php

namespace App\Http\Controllers;

use App\Services\SupabaseUserService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Demo checkout: no payment provider is connected. These are the only plan
     * values the flow accepts, and the plan is applied immediately.
     */
    private const DEMO_PLANS = ['free', 'pro', 'enterprise'];

    protected $supabaseUser;

    public function __construct(SupabaseUserService $supabaseUser)
    {
        $this->supabaseUser = $supabaseUser;
    }

    public function index(Request $request, $plan)
    {
        if (! in_array($plan, ['pro', 'enterprise'], true)) {
            return redirect()->route('pricing');
        }

        return view('checkout.index', [
            'plan' => $plan,
            'amount' => $plan === 'pro' ? 19 : 49,
        ]);
    }

    public function process(Request $request)
    {
        $userId = session('supabase_user_id');
        $plan = $request->input('plan');

        if (! $userId) {
            return redirect()->route('login');
        }

        if (! in_array($plan, self::DEMO_PLANS, true)) {
            return redirect()->route('pricing');
        }

        // No payment step: the upgrade is applied straight away.

        // Update plan in Supabase
        $this->supabaseUser->update($userId, [
            'plan' => $plan,
        ]);

        // REFRESH SESSION DATA so the rank updates globally in the UI
        $user = $this->supabaseUser->findById($userId);
        if ($user) {
            session(['supabase_user_plan' => $user['plan'] ?? 'free']);
        }

        return redirect()->route('pricing')->with('payment_success', 'Your account has been upgraded to '.ucfirst($plan).'!');
    }

    public function success()
    {
        return view('checkout.success');
    }
}
