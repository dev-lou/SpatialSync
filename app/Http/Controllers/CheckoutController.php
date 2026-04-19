<?php

namespace App\Http\Controllers;

use App\Services\SupabaseUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    protected $supabaseUser;

    public function __construct(SupabaseUserService $supabaseUser)
    {
        $this->supabaseUser = $supabaseUser;
    }

    public function index(Request $request, $plan)
    {
        // Allowed plans for simulation
        if (!in_array($plan, ['pro', 'enterprise'])) {
            return redirect()->route('pricing');
        }

        return view('checkout.index', [
            'plan' => $plan,
            'amount' => $plan === 'pro' ? 19 : 49
        ]);
    }

    public function process(Request $request)
    {
        $userId = session('supabase_user_id');
        $plan = $request->input('plan');

        if (!$userId) {
            return redirect()->route('login');
        }

        // Simulate successful payment delay is handled by the UI spinner
        
        // Update plan in Supabase
        $this->supabaseUser->update($userId, [
            'plan' => $plan
        ]);

        // REFRESH SESSION DATA so the rank updates globally in the UI
        $user = $this->supabaseUser->findById($userId);
        if ($user) {
            session(['supabase_user_plan' => $user['plan'] ?? 'free']);
        }

        return redirect()->route('pricing')->with('payment_success', "Your account has been upgraded to " . ucfirst($plan) . "!");
    }

    public function success()
    {
        return view('checkout.success');
    }
}
