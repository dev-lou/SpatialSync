@extends('layouts.app')
@section('title', 'Pricing')
@section('description', 'Simple, transparent pricing for SpatialSync. Start free and upgrade when you need more.')

@push('styles')
<style>
/* ── PRICING PAGE STYLES ─────────────────────── */
.pricing-hero {
    position: relative;
    padding: calc(80px + var(--space-8)) 0 var(--space-20);
    text-align: center;
    background: radial-gradient(circle at 50% -20%, var(--accent-muted) 0%, var(--bg) 60%);
    overflow: hidden;
    border-bottom: 1px solid var(--border-default);
}

.pricing-hero__title {
    font-family: var(--font-display);
    font-size: clamp(3rem, 7vw, 5rem);
    font-weight: 900;
    letter-spacing: -0.02em;
    color: var(--text-primary);
    margin-bottom: var(--space-6);
    line-height: 1.05;
}

.pricing-hero__title span {
    background: linear-gradient(to right, var(--accent), #9333EA, var(--accent));
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: text-shine 4s linear infinite;
}

.pricing-hero__subtitle {
    font-size: var(--text-xl);
    color: var(--text-secondary);
    max-width: 500px;
    margin: 0 auto var(--space-8);
}

/* ── PRICING CARDS ───────────────────────────── */
.pricing-section {
    padding: var(--space-8) 0 var(--space-20);
    background: var(--bg);
}

.pricing-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-6);
    max-width: 1100px;
    margin: 0 auto;
}

@media (min-width: 768px) {
    .pricing-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.pricing-card {
    position: relative;
    padding: var(--space-8);
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-xl);
    transition: border-color var(--dur-base), box-shadow var(--dur-base), transform var(--dur-base);
    overflow: visible !important;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.pricing-card.glow-card::before {
    border-radius: var(--radius-xl);
}

.pricing-card:hover {
    border-color: var(--border-strong);
    transform: translateY(-4px);
}

.pricing-card--featured {
    border-color: var(--accent);
    box-shadow: var(--shadow-accent), var(--shadow-lg);
    transform: scale(1.02);
}

.pricing-card--featured:hover {
    transform: scale(1.02) translateY(-4px);
}

.pricing-card__badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    padding: var(--space-1) var(--space-4);
    background: var(--accent);
    color: white;
    font-size: var(--text-xs);
    font-weight: 600;
    border-radius: var(--radius-full);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.pricing-card__header {
    text-align: center;
    margin-bottom: var(--space-6);
    padding-bottom: var(--space-6);
    border-bottom: 1px solid var(--border-default);
}

.pricing-card__name {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.pricing-card__price {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: var(--space-1);
    margin-bottom: var(--space-2);
}

.pricing-card__amount {
    font-size: var(--text-5xl);
    font-weight: 700;
    color: var(--text-primary);
}

.pricing-card__period {
    font-size: var(--text-sm);
    color: var(--text-tertiary);
}

.pricing-card__billing {
    font-size: var(--text-sm);
    color: var(--text-tertiary);
}

.pricing-card__features {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
    margin-bottom: var(--space-8);
}
.pricing-card__action {
    margin-top: auto;
    width: 100%;
}

.pricing-card__feature {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

.pricing-card__feature--disabled {
    color: var(--text-tertiary);
    text-decoration: line-through;
}

.pricing-card__icon {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
}

.pricing-card__icon--check {
    color: var(--success);
}

.pricing-card__icon--x {
    color: var(--text-tertiary);
}

/* ── TRUST STRIP ─────────────────────────────── */
.trust-strip {
    padding: var(--space-12) 0;
    background: var(--bg-secondary);
    text-align: center;
}

.trust-strip__title {
    font-size: var(--text-sm);
    font-weight: 600;
    color: var(--text-tertiary);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: var(--space-6);
}

.trust-strip__items {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: var(--space-8);
}

.trust-strip__item {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

.trust-strip__icon {
    width: 20px;
    height: 20px;
    color: var(--success);
}

/* ── COMPARISON TABLE ────────────────────────── */
.comparison-section {
    padding: var(--space-20) 0;
    background: var(--bg);
}

.comparison-section__header {
    text-align: center;
    margin-bottom: var(--space-12);
}

.comparison-section__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.comparison-section__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
}

.comparison-table {
    width: 100%;
    border-collapse: collapse;
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.comparison-table th,
.comparison-table td {
    padding: var(--space-4) var(--space-6);
    text-align: left;
    border-bottom: 1px solid var(--border-default);
}

.comparison-table th {
    background: var(--bg-secondary);
    font-weight: 600;
    color: var(--text-primary);
    font-size: var(--text-sm);
}

.comparison-table td {
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

.comparison-table th:not(:first-child),
.comparison-table td:not(:first-child) {
    text-align: center;
}

.comparison-table tbody tr:last-child td {
    border-bottom: none;
}

.comparison-table tbody tr:hover {
    background: var(--bg-secondary);
}

.comparison-check {
    color: var(--success);
}

.comparison-x {
    color: var(--text-tertiary);
}

/* ── TESTIMONIAL SECTION ─────────────────────── */
.pricing-testimonial {
    padding: var(--space-20) 0;
    background: var(--bg-secondary);
}

.pricing-testimonial__content {
    max-width: 700px;
    margin: 0 auto;
    text-align: center;
}

.pricing-testimonial__quote {
    font-family: var(--font-display);
    font-size: var(--text-2xl);
    font-weight: 400;
    color: var(--text-primary);
    line-height: 1.5;
    margin-bottom: var(--space-8);
}

.pricing-testimonial__author {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-4);
}

.pricing-testimonial__avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
}

.pricing-testimonial__info {
    text-align: left;
}

.pricing-testimonial__name {
    font-weight: 600;
    color: var(--text-primary);
}

.pricing-testimonial__role {
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

/* ── FAQ SECTION ─────────────────────────────── */
.faq-section {
    padding: var(--space-20) 0;
    background: var(--bg);
}

.faq-section__header {
    text-align: center;
    margin-bottom: var(--space-12);
}

.faq-section__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.faq-section__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
}

.faq-wrapper {
    max-width: 700px;
    margin: 0 auto;
}

/* ── CTA SECTION ─────────────────────────────── */
.pricing-cta {
    padding: var(--space-20) 0;
    background: linear-gradient(135deg, var(--accent) 0%, #0052CC 100%);
    text-align: center;
    color: white;
}

.pricing-cta__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    margin-bottom: var(--space-4);
}

.pricing-cta__subtitle {
    font-size: var(--text-lg);
    opacity: 0.9;
    margin-bottom: var(--space-8);
}

.pricing-cta .btn {
    background: white;
    color: var(--accent);
}

.pricing-cta .btn:hover {
    background: var(--bg-secondary);
    transform: translateY(-2px);
}

/* ── RESPONSIVE TABLE ────────────────────────── */
@media (max-width: 767px) {
    .comparison-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="pricing-hero">
    <div class="container">
        <div class="reveal">
            <h1 class="pricing-hero__title">Simple, <span>transparent pricing</span></h1>
            <p class="pricing-hero__subtitle">
                Start for free. Upgrade when you need more. No hidden fees.
            </p>
            <x-pricing-toggle :discount="20" />
        </div>
    </div>
</section>

<!-- Pricing Cards -->
<section class="pricing-section" x-data="{ annual: false }" @billing-change.window="annual = $event.detail.annual">
    <div class="container">
        <div class="pricing-grid stagger">
            <!-- Free Plan -->
            <div class="pricing-card glow-card reveal">
                <div class="pricing-card__header">
                    <h3 class="pricing-card__name">Free</h3>
                    <div class="pricing-card__price">
                        <span class="pricing-card__amount">$0</span>
                    </div>
                    <p class="pricing-card__billing">Forever free</p>
                </div>

                <ul class="pricing-card__features">
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        1 Workspace
                    </li>
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        Up to 5 builds
                    </li>
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        3 team members
                    </li>
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        Basic 3D parts library
                    </li>
                    <li class="pricing-card__feature pricing-card__feature--disabled">
                        <i data-lucide="x" class="pricing-card__icon pricing-card__icon--x"></i>
                        Advanced Team Management
                    </li>
                </ul>

                <div class="pricing-card__action">
                    @php $isLoggedIn = (bool)session('supabase_user_id'); @endphp
                    @if($isLoggedIn && ($auth_user->plan ?? 'free') === 'free')
                        <button class="btn btn--secondary btn--lg w-full" disabled style="opacity: 0.6; cursor: default;">
                            Your Current Plan
                        </button>
                    @elseif($isLoggedIn)
                        <form action="{{ route('checkout.process') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan" value="free">
                            <button type="submit" class="btn btn--secondary btn--lg w-full">
                                Return to Free
                            </button>
                        </form>
                    @else
                        <a href="{{ route('register') }}" class="btn btn--secondary btn--lg w-full">
                            Get started free
                        </a>
                    @endif
                </div>
            </div>

            <!-- Pro Plan (Featured) -->
            <div class="pricing-card pricing-card--featured glow-card reveal">
                <span class="pricing-card__badge">Most Popular</span>
                <div class="pricing-card__header">
                    <h3 class="pricing-card__name">Pro</h3>
                    <div class="pricing-card__price">
                        <span class="pricing-card__amount" x-text="annual ? '$15' : '$19'">$19</span>
                        <span class="pricing-card__period">/month</span>
                    </div>
                    <p class="pricing-card__billing" x-text="annual ? 'Billed annually ($180/year)' : 'Billed monthly'">Billed monthly</p>
                </div>

                <ul class="pricing-card__features">
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        3 Workspaces
                    </li>
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        <strong>Unlimited</strong> builds
                    </li>
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        <strong>Unlimited</strong> team members
                    </li>
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        Full 3D parts + materials library
                    </li>
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        Real-time chat & issue tracking
                    </li>
                </ul>

                <div class="pricing-card__action">
                    @php $isLoggedIn = (bool)session('supabase_user_id'); @endphp
                    @if($isLoggedIn && ($auth_user->plan ?? 'free') === 'pro')
                        <button class="btn btn--primary btn--lg w-full" disabled style="opacity: 0.6; cursor: default;">
                            Your Current Plan
                        </button>
                    @else
                        <a href="{{ $isLoggedIn ? route('checkout', 'pro') : route('register') }}" class="btn btn--primary btn--lg w-full btn-glow">
                            Start 14-day free trial
                        </a>
                    @endif
                </div>
            </div>

            <!-- Team Plan -->
            <div class="pricing-card glow-card reveal">
                <div class="pricing-card__header">
                    <h3 class="pricing-card__name">Enterprise</h3>
                    <div class="pricing-card__price">
                        <span class="pricing-card__amount" x-text="annual ? '$39' : '$49'">$49</span>
                        <span class="pricing-card__period">/month</span>
                    </div>
                    <p class="pricing-card__billing" x-text="annual ? 'Billed annually ($468/year)' : 'Billed monthly'">Billed monthly</p>
                </div>

                <ul class="pricing-card__features">
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        Everything in Pro
                    </li>
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        Unlimited Workspaces
                    </li>
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        Organization Management
                    </li>
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        Role-based access permissions
                    </li>
                    <li class="pricing-card__feature">
                        <i data-lucide="check" class="pricing-card__icon pricing-card__icon--check"></i>
                        Priority database scaling
                    </li>
                </ul>

                <div class="pricing-card__action">
                    @if(session('supabase_user_id') && ($auth_user->plan ?? 'free') === 'enterprise')
                        <button class="btn btn--secondary btn--lg w-full" disabled style="opacity: 0.6; cursor: default;">
                            Your Current Plan
                        </button>
                    @else
                        <a href="{{ route('contact.sales') }}" class="btn btn--secondary btn--lg w-full">
                            Contact sales
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trust Strip -->
<section class="trust-strip">
    <div class="container">
        <p class="trust-strip__title reveal">Trusted by architects worldwide</p>
        <div class="trust-strip__items reveal">
            <div class="trust-strip__item">
                <i data-lucide="shield-check" class="trust-strip__icon"></i>
                Enterprise-grade security
            </div>
            <div class="trust-strip__item">
                <i data-lucide="credit-card" class="trust-strip__icon"></i>
                Cancel anytime
            </div>
            <div class="trust-strip__item">
                <i data-lucide="clock" class="trust-strip__icon"></i>
                14-day free trial
            </div>
            <div class="trust-strip__item">
                <i data-lucide="headphones" class="trust-strip__icon"></i>
                24/7 support
            </div>
        </div>
    </div>
</section>

<!-- Comparison Table -->
<section class="comparison-section">
    <div class="container">
        <div class="comparison-section__header reveal">
            <h2 class="comparison-section__title">Compare plans</h2>
            <p class="comparison-section__subtitle">Find the perfect plan for your needs.</p>
        </div>

        <div class="reveal" style="overflow-x: auto;">
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>Free</th>
                        <th>Pro</th>
                        <th>Enterprise</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>builds</td>
                        <td>10</td>
                        <td>Unlimited</td>
                        <td>Unlimited</td>
                    </tr>
                    <tr>
                        <td>Team members</td>
                        <td>3</td>
                        <td>Unlimited</td>
                        <td>Unlimited</td>
                    </tr>
                    <tr>
                        <td>Workspaces</td>
                        <td>1</td>
                        <td>3</td>
                        <td>5</td>
                    </tr>
                    <tr>
                        <td>Shape library</td>
                        <td><i data-lucide="check" class="w-5 h-5 comparison-check"></i></td>
                        <td><i data-lucide="check" class="w-5 h-5 comparison-check"></i></td>
                        <td><i data-lucide="check" class="w-5 h-5 comparison-check"></i></td>
                    </tr>
                    <tr>
                        <td>Real-time collaboration</td>
                        <td><i data-lucide="check" class="w-5 h-5 comparison-check"></i></td>
                        <td><i data-lucide="check" class="w-5 h-5 comparison-check"></i></td>
                        <td><i data-lucide="check" class="w-5 h-5 comparison-check"></i></td>
                    </tr>
                    <tr>
                        <td>Role-based Access</td>
                        <td>Owner & Editor only</td>
                        <td><i data-lucide="check" class="w-5 h-5 comparison-check"></i></td>
                        <td><i data-lucide="check" class="w-5 h-5 comparison-check"></i></td>
                    </tr>
                    <tr>
                        <td>Issue Tracking</td>
                        <td><i data-lucide="x" class="w-5 h-5 comparison-x"></i></td>
                        <td><i data-lucide="check" class="w-5 h-5 comparison-check"></i></td>
                        <td><i data-lucide="check" class="w-5 h-5 comparison-check"></i></td>
                    </tr>
                    <tr>
                        <td>Admin dashboard</td>
                        <td><i data-lucide="x" class="w-5 h-5 comparison-x"></i></td>
                        <td><i data-lucide="x" class="w-5 h-5 comparison-x"></i></td>
                        <td><i data-lucide="check" class="w-5 h-5 comparison-check"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Testimonial -->
<section class="pricing-testimonial">
    <div class="container">
        <div class="pricing-testimonial__content reveal">
            <blockquote class="pricing-testimonial__quote">
                "SpatialSync completely transformed how our team reviews 3D designs together. 
                The real-time collaboration and issue pinning alone save us hours every week."
            </blockquote>
            <div class="pricing-testimonial__author">
                <img 
                    src="https://ui-avatars.com/api/?name=Alex+Rivera&background=0066FF&color=fff&size=112" 
                    alt="Alex Rivera"
                    class="pricing-testimonial__avatar"
                    width="56"
                    height="56"
                    loading="lazy"
                >
                <div class="pricing-testimonial__info">
                    <span class="pricing-testimonial__name">Alex Rivera</span>
                    <span class="pricing-testimonial__role">Design Lead, Spatial Studio</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <div class="faq-section__header reveal">
            <h2 class="faq-section__title">Frequently asked questions</h2>
            <p class="faq-section__subtitle">Everything you need to know about our pricing.</p>
        </div>

        <div class="faq-wrapper">
            <x-faq-accordion :items="[
                ['question' => 'Can I upgrade or downgrade anytime?', 'answer' => 'Yes! You can change your plan at any time. Upgrades take effect immediately, and downgrades apply at your next billing cycle. We\'ll prorate any charges.'],
                ['question' => 'What payment methods do you accept?', 'answer' => 'We accept all major credit cards (Visa, Mastercard, American Express), PayPal, and bank transfers for annual Enterprise plans. All payments are processed securely through Stripe.'],
                ['question' => 'Is there a free trial?', 'answer' => 'Yes! All paid plans include a 14-day free trial with full access to features. No credit card required to start. You can also use our Free plan indefinitely.'],
                ['question' => 'What happens to my data if I downgrade?', 'answer' => 'Your builds are always safe. If you downgrade to Free and exceed the 10 build limit, your existing builds remain accessible but you won\'t be able to create new ones until you\'re under the limit.'],
                ['question' => 'Do you offer discounts for non-profits or education?', 'answer' => 'Yes! We offer 50% discounts for verified non-profit organizations and educational institutions. Contact our sales team with your organization details.'],
                ['question' => 'Can I get a refund?', 'answer' => 'We offer a 30-day money-back guarantee for all paid plans. If you\'re not satisfied, contact support within 30 days of purchase for a full refund.'],
            ]" />
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="pricing-cta">
    <div class="container">
        <div class="reveal">
            <h2 class="pricing-cta__title">Ready to start designing?</h2>
            <p class="pricing-cta__subtitle">
                Join teams already building in 3D with SpatialSync.
            </p>
            <a href="{{ route('register') }}" class="btn btn--xl">
                Start your free trial
                <i data-lucide="arrow-right" class="w-5 h-5"></i>
            </a>
        </div>
    </div>
</section>
@if(session('payment_success'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Payment Successful!',
            text: "{{ session('payment_success') }}",
            icon: 'success',
            background: 'var(--surface)',
            color: 'var(--text-primary)',
            confirmButtonColor: 'var(--accent)',
            confirmButtonText: 'Great!',
            backdrop: `
                rgba(0,0,123,0.1)
                left top
                no-repeat
            `
        });
    });
</script>
@endif
@endsection

