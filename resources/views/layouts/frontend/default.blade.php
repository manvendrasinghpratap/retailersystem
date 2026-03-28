<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Retailer	System') }} || {{ config('app.subtitle') }}</title>
    <meta name="keywords"
        content=" {{ config('app.name') }}, {{ config('app.subtitle') }}, {{ config('constants.slogan') }}">
    <meta name="description"
        content="{{ config('app.name') }} - {{ config('app.subtitle') }}. {{ config('constants.slogan') }}">
    <meta content="@lang('translation.webname')" name="author" vale="{{ Config::get('constants.author') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicon -->
    @php($cssRefresh = Config::get('app.css_refresh'))
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/icons/apple-touch-icon.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/icons/favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/icons/favicon-16x16.png')}}">
    <link rel="manifest" href="assets/images/icons/site.webmanifest">
    <link rel="mask-icon" href="assets/images/icons/safari-pinned-tab.svg" color="#666666">
    <link rel="shortcut icon" href="assets/images/icons/favicon.ico">
    <meta name="apple-mobile-web-app-title" content="Molla">
    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="msapplication-TileColor" content="#cc9966">
    <meta name="theme-color" content="#ffffff">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    @include('layouts.frontend.css')
    @stack('styles')
</head>

<body>
    <!-- NAVBAR -->
    @include('layouts.frontend.header')

    <!-- HERO -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">

                <!-- Left Content -->
                <div class="col-lg-6">
                    <h1 class="fw-bold mb-4">
                        Smart Retail Management & POS System
                    </h1>

                    <p class="lead mb-4">
                        Manage billing, inventory, sales, and customers from one powerful cloud platform designed for
                        modern retail stores.
                    </p>

                    <a href="#pricing" class="btn btn-primary btn-lg me-2">
                        Start Free Trial
                    </a>

                    <a href="#features" class="btn btn-outline-primary btn-lg">
                        Explore Features
                    </a>
                </div>

                <!-- Right Image -->
                <div class="col-lg-6 text-center mt-4 mt-lg-0">
                    <img src="{{ asset('assets/images/celeberation/' . rand(1, 7) . '.png') }}"
                        class="img-fluid rounded shadow" alt="African girl working in a retail shop using POS system">
                </div>

            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section id="features" class="py-5">

        <div class="container text-center">

            <h2 class="fw-bold mb-3">Powerful Features</h2>
            <p class="text-muted mb-5">Everything you need to manage and grow your retail business</p>

            <div class="row g-4">

                <!-- Inventory Management -->
                <div class="col-md-4">
                    <div class="card feature-card p-4 h-100 shadow-sm border-0">

                        <i class="fa-solid fa-boxes-stacked text-primary fs-1 mb-3"></i>

                        <h5 class="fw-bold">Inventory Management</h5>

                        <p class="text-muted">
                            Track products, stock levels, and categories in real time.
                        </p>

                    </div>
                </div>

                <!-- POS Sales -->
                <div class="col-md-4">
                    <div class="card feature-card p-4 h-100 shadow-sm border-0">

                        <i class="fa-solid fa-cash-register text-primary fs-1 mb-3"></i>

                        <h5 class="fw-bold">Point of Sale (POS)</h5>

                        <p class="text-muted">
                            Process sales quickly with a fast and easy POS system.
                        </p>

                    </div>
                </div>

                <!-- Reports & Analytics -->
                <div class="col-md-4">
                    <div class="card feature-card p-4 h-100 shadow-sm border-0">

                        <i class="fa-solid fa-chart-line text-primary fs-1 mb-3"></i>

                        <h5 class="fw-bold">Sales Reports</h5>

                        <p class="text-muted">
                            Analyze sales, profits, and performance with detailed reports.
                        </p>

                    </div>
                </div>

            </div>

        </div>

    </section>

    <!-- HOW IT WORKS -->
    <section id="how" class="py-5 bg-light">

        <div class="container text-center">

            <h2 class="fw-bold mb-3">How It Works</h2>
            <p class="text-muted mb-5">Start managing your retail business in just a few simple steps</p>

            <div class="row g-4">

                <!-- Step 1 -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-4">

                        <div class="mb-3">
                            <i class="fa-solid fa-user-plus text-primary fs-1"></i>
                        </div>

                        <h5 class="fw-bold">Create Your Account</h5>

                        <p class="text-muted">
                            Sign up and set up your retail store profile to start using the system.
                        </p>

                    </div>
                </div>

                <!-- Step 2 -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-4">

                        <div class="mb-3">
                            <i class="fa-solid fa-boxes-stacked text-primary fs-1"></i>
                        </div>

                        <h5 class="fw-bold">Add Products & Inventory</h5>

                        <p class="text-muted">
                            Add your products, categories, and stock details to manage inventory easily.
                        </p>

                    </div>
                </div>

                <!-- Step 3 -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-4">

                        <div class="mb-3">
                            <i class="fa-solid fa-cash-register text-primary fs-1"></i>
                        </div>

                        <h5 class="fw-bold">Start Selling</h5>

                        <p class="text-muted">
                            Process sales, track orders, and monitor your business performance in real time.
                        </p>

                    </div>
                </div>

            </div>

        </div>

    </section>

    <!-- PRICING -->
    <section id="pricing" class="pricing py-5 bg-light">

        <div class="container text-center">

            <h2 class="fw-bold mb-3">Subscription Plans</h2>
            <p class="text-muted mb-5">Choose the perfect plan for your business</p>

            <div class="row g-4">

                <!-- Starter -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body p-4">

                            <h5 class="fw-bold">Starter</h5>

                            <h2 class="text-primary my-3">$49<span class="fs-6">/mo</span></h2>

                            <p class="text-muted">Perfect for small clinics</p>

                            <ul class="list-unstyled mb-4">
                                <li>✔ Patient Records</li>
                                <li>✔ Appointment Scheduling</li>
                                <li>✔ Basic Reports</li>
                            </ul>

                            <button class="btn btn-outline-primary w-100">
                                Get Started
                            </button>

                        </div>

                    </div>
                </div>


                <!-- Professional -->
                <div class="col-md-4">
                    <div class="card shadow border-primary h-100">

                        <div class="card-body p-4">

                            <span class="badge bg-primary mb-2">
                                Most Popular
                            </span>

                            <h5 class="fw-bold">Professional</h5>

                            <h2 class="text-primary my-3">$99<span class="fs-6">/mo</span></h2>

                            <p class="text-muted">Best for growing clinics</p>

                            <ul class="list-unstyled mb-4">
                                <li>✔ Everything in Starter</li>
                                <li>✔ Billing & Invoices</li>
                                <li>✔ Analytics Dashboard</li>
                                <li>✔ Priority Support</li>
                            </ul>

                            <button class="btn btn-primary w-100">
                                Start Free Trial
                            </button>

                        </div>

                    </div>
                </div>


                <!-- Enterprise -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body p-4">

                            <h5 class="fw-bold">Enterprise</h5>

                            <h2 class="text-primary my-3">$199<span class="fs-6">/mo</span></h2>

                            <p class="text-muted">Ideal for hospitals</p>

                            <ul class="list-unstyled mb-4">
                                <li>✔ Full System Access</li>
                                <li>✔ Multi-Branch Support</li>
                                <li>✔ Advanced Analytics</li>
                                <li>✔ Dedicated Support</li>
                            </ul>

                            <button class="btn btn-outline-primary w-100">
                                Contact Sales
                            </button>

                        </div>

                    </div>
                </div>

            </div>

        </div>

    </section>

    <!-- CONTACT -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">

            <div class="text-center mb-5">
                <h2 class="fw-bold">Contact Us</h2>
                <p class="text-muted">Our healthcare experts are ready to help you</p>
            </div>

            <div class="row">

                <!-- Contact Info -->
                <div class="col-lg-5 mb-4">
                    <div class="p-4 bg-white rounded-4 shadow-sm h-100">

                        <h5 class="fw-bold mb-4">Get in Touch</h5>

                        <p class="mb-3">
                            <i class="fa-solid fa-envelope text-primary me-2"></i>
                            {{ Config::get('constants.emailcontact') }}
                        </p>

                        <p class="mb-3">
                            <i class="fa-solid fa-phone text-primary me-2"></i>
                            {{ Config::get('constants.phonenumber') }}
                        </p>

                        <p class="mb-0">
                            <i class="fa-solid fa-location-dot text-primary me-2"></i>
                            {{ Config::get('constants.address') }}
                        </p>

                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-lg-7">
                    <div class="p-4 bg-white rounded-4 shadow-sm">

                        <form action="#" method="post">

                            <div class="row mb-3">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <input type="text" name="name" class="form-control" placeholder="Full Name"
                                        required>
                                </div>

                                <div class="col-md-6">
                                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <input type="text" name="organization" class="form-control"
                                    placeholder="Organization Name">
                            </div>

                            <div class="mb-3">
                                <textarea name="message" class="form-control" rows="4" placeholder="Message"
                                    required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Send Message
                            </button>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- TESTIMONIALS -->
    <section id="testimonials" class="py-5 bg-light text-center">

        <div class="container">

            <h2 class="fw-bold mb-5">
                Loved by Retail Businesses
            </h2>

            <div class="row">

                <!-- Testimonial 1 -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body p-4">

                            <p class="text-muted mb-4">
                                RetailCloud made our billing super fast and simple.
                            </p>

                            <h5 class="fw-bold mb-0">Amit Sharma</h5>
                            <p class="text-muted small">Retail Owner</p>

                        </div>

                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body p-4">

                            <p class="text-muted mb-4">
                                Inventory tracking saved us hours every week.
                            </p>

                            <h5 class="fw-bold mb-0">Neha Patel</h5>
                            <p class="text-muted small">Store Manager</p>

                        </div>

                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body p-4">

                            <p class="text-muted mb-4">
                                The analytics helped grow our business fast.
                            </p>

                            <h5 class="fw-bold mb-0">Raj Mehta</h5>
                            <p class="text-muted small">Business Owner</p>

                        </div>

                    </div>
                </div>

            </div>

        </div>

    </section>
    <!-- TESTIMONIALS END -->
    <!-- CTA -->
    <section class="cta py-5 text-center">
        <h2 class="fw-bold mb-3">Start Your Free POS Today!</h2>
        <a class="btn btn-light btn-lg">Start Free Trial</a>
    </section>

    <!-- FOOTER -->
    <footer class="text-white text-center py-3">
        © {{ date('Y') }} {{ config('app.name') }} • Secure • Trusted • Scalable
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Sign in / Register Modal -->
    @include('model.signin-register-modal')
    @include('model.forgot-password')
    <!-- End .modal -->
    <!-- Newsletter Popup -->
    @include('model.newsletter-popup')
    <!-- Plugins JS File -->
    @include('layouts.frontend.js')
    @stack('scripts')
</body>


</html>