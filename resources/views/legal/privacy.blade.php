<x-layouts.legal
    title="Privacy Policy"
    description="Privacy Policy for {{ config('app.name') }}. Learn how we collect, use, and protect your personal data."
    :canonical="route('privacy')"
>
    <h1 class="text-3xl font-bold mb-2">Privacy Policy</h1>
    <p class="text-sm text-gray-400 mb-10">Last updated: {{ date('F j, Y') }}</p>

    <div class="prose prose-gray max-w-none space-y-8">
        <section>
            <h2 class="text-xl font-semibold mb-3">1. Introduction</h2>
            <p class="text-gray-600 leading-relaxed">{{ config('app.name') }} ("we", "our", "us") operates the {{ config('app.name') }} platform. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our service.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-3">2. Information We Collect</h2>
            <p class="text-gray-600 leading-relaxed mb-3">We collect the following types of information:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
                <li><strong>Account information:</strong> name, email address, and password when you register.</li>
                <li><strong>Business profile data:</strong> business name, address, Google Review link, and logo that you provide.</li>
                <li><strong>Customer data:</strong> email addresses of customers you send review requests to.</li>
                <li><strong>Rating and feedback data:</strong> scores and comments submitted by your customers.</li>
                <li><strong>Usage data:</strong> browser type, IP address, pages visited, and interaction timestamps collected automatically.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-3">3. How We Use Your Information</h2>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
                <li>To provide and maintain the service, including sending review requests on your behalf.</li>
                <li>To process ratings and route customers to Google Reviews or private feedback forms.</li>
                <li>To send you service-related notifications (e.g., negative feedback alerts).</li>
                <li>To improve the platform and develop new features.</li>
                <li>To comply with legal obligations.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-3">4. Data Sharing</h2>
            <p class="text-gray-600 leading-relaxed">We do not sell your personal data. We may share information with:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 mt-3">
                <li><strong>Service providers:</strong> email delivery, hosting, and analytics providers that help us operate the platform.</li>
                <li><strong>Legal requirements:</strong> when required by law, regulation, or legal process.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-3">5. Data Retention</h2>
            <p class="text-gray-600 leading-relaxed">We retain your data for as long as your account is active. When you delete your account, we will delete your personal data within 30 days, except where retention is required by law.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-3">6. Data Security</h2>
            <p class="text-gray-600 leading-relaxed">We implement industry-standard security measures including encryption in transit (TLS), secure password hashing, and access controls. However, no method of transmission over the Internet is 100% secure.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-3">7. Your Rights</h2>
            <p class="text-gray-600 leading-relaxed mb-3">Depending on your location, you may have the right to:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
                <li>Access the personal data we hold about you.</li>
                <li>Request correction of inaccurate data.</li>
                <li>Request deletion of your data.</li>
                <li>Export your data in a portable format.</li>
                <li>Withdraw consent where processing is based on consent.</li>
            </ul>
            <p class="text-gray-600 mt-3">To exercise these rights, contact us at the email below.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-3">8. Cookies</h2>
            <p class="text-gray-600 leading-relaxed">We use essential cookies for authentication and session management. We do not use third-party tracking cookies.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-3">9. Changes to This Policy</h2>
            <p class="text-gray-600 leading-relaxed">We may update this Privacy Policy from time to time. We will notify you of material changes by posting the updated policy on this page with a revised "Last updated" date.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-3">10. Contact Us</h2>
            <p class="text-gray-600 leading-relaxed">If you have any questions about this Privacy Policy, please contact us at <a href="mailto:privacy@quickfeedback.app" class="text-indigo-600 hover:underline">privacy@quickfeedback.app</a>.</p>
        </section>
    </div>
</x-layouts.legal>
