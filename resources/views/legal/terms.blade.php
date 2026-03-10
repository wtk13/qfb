<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service — {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="description" content="Terms of Service for {{ config('app.name') }}. Read the terms and conditions for using our review management platform.">
    <link rel="canonical" href="{{ route('terms') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-white text-gray-900">

    <nav class="border-b border-gray-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <a href="/" class="text-xl font-bold text-indigo-600">{{ config('app.name') }}</a>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-bold mb-2">Terms of Service</h1>
        <p class="text-sm text-gray-400 mb-10">Last updated: {{ date('F j, Y') }}</p>

        <div class="prose prose-gray max-w-none space-y-8">
            <section>
                <h2 class="text-xl font-semibold mb-3">1. Acceptance of Terms</h2>
                <p class="text-gray-600 leading-relaxed">By accessing or using {{ config('app.name') }} ("the Service"), you agree to be bound by these Terms of Service. If you do not agree, do not use the Service.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-3">2. Description of Service</h2>
                <p class="text-gray-600 leading-relaxed">{{ config('app.name') }} is a review management platform that allows businesses to send review requests to customers, collect ratings, route positive reviews to Google, and gather private feedback from dissatisfied customers.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-3">3. Account Registration</h2>
                <ul class="list-disc list-inside text-gray-600 space-y-2">
                    <li>You must provide accurate and complete registration information.</li>
                    <li>You are responsible for maintaining the security of your account credentials.</li>
                    <li>You must be at least 18 years old to create an account.</li>
                    <li>One person or legal entity may not maintain more than one account.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-3">4. Acceptable Use</h2>
                <p class="text-gray-600 leading-relaxed mb-3">You agree not to:</p>
                <ul class="list-disc list-inside text-gray-600 space-y-2">
                    <li>Send review requests to people who have not done business with you.</li>
                    <li>Use the Service to send spam or unsolicited communications.</li>
                    <li>Attempt to manipulate or fabricate reviews.</li>
                    <li>Violate any applicable laws or regulations, including anti-spam laws (CAN-SPAM, GDPR).</li>
                    <li>Interfere with or disrupt the Service or its infrastructure.</li>
                    <li>Reverse-engineer, decompile, or attempt to extract the source code of the Service.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-3">5. Customer Data</h2>
                <p class="text-gray-600 leading-relaxed">You are responsible for obtaining appropriate consent from your customers before submitting their email addresses to the Service. You represent that you have a legitimate business relationship with each customer you send review requests to.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-3">6. Subscription and Payment</h2>
                <ul class="list-disc list-inside text-gray-600 space-y-2">
                    <li>The Service offers a 14-day free trial. No credit card is required to start.</li>
                    <li>After the trial period, a paid subscription is required to continue using the Service.</li>
                    <li>Subscriptions are billed monthly. Prices are listed on our pricing page.</li>
                    <li>You may cancel your subscription at any time. Cancellation takes effect at the end of the current billing period.</li>
                    <li>We reserve the right to change pricing with 30 days' notice.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-3">7. Intellectual Property</h2>
                <p class="text-gray-600 leading-relaxed">The Service, including its design, code, and branding, is owned by {{ config('app.name') }} and protected by intellectual property laws. You retain ownership of the content you upload (logos, business information). By uploading content, you grant us a license to use it solely for providing the Service.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-3">8. Limitation of Liability</h2>
                <p class="text-gray-600 leading-relaxed">To the maximum extent permitted by law, {{ config('app.name') }} shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including loss of profits, data, or business opportunities, arising from your use of the Service. Our total liability shall not exceed the amount you paid us in the 12 months preceding the claim.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-3">9. Disclaimer of Warranties</h2>
                <p class="text-gray-600 leading-relaxed">The Service is provided "as is" and "as available" without warranties of any kind, either express or implied. We do not guarantee that the Service will be uninterrupted, error-free, or that it will meet your specific requirements.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-3">10. Termination</h2>
                <p class="text-gray-600 leading-relaxed">We may suspend or terminate your account if you violate these Terms. Upon termination, your right to use the Service ceases immediately. We will make your data available for export for 30 days after termination.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-3">11. Changes to Terms</h2>
                <p class="text-gray-600 leading-relaxed">We may modify these Terms at any time. Material changes will be communicated via email or a notice on the Service at least 14 days before they take effect. Continued use after changes constitutes acceptance.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-3">12. Governing Law</h2>
                <p class="text-gray-600 leading-relaxed">These Terms are governed by the laws of Poland. Any disputes shall be resolved in the courts of Poland.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-3">13. Contact Us</h2>
                <p class="text-gray-600 leading-relaxed">If you have questions about these Terms, please contact us at <a href="mailto:legal@quickfeedback.io" class="text-indigo-600 hover:underline">legal@quickfeedback.io</a>.</p>
            </section>
        </div>
    </main>

    <footer class="py-8 border-t border-gray-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-center gap-6 text-sm text-gray-400">
            <a href="{{ route('privacy') }}" class="hover:text-gray-600">Privacy Policy</a>
            <a href="{{ route('terms') }}" class="hover:text-gray-600">Terms of Service</a>
        </div>
    </footer>

</body>
</html>
