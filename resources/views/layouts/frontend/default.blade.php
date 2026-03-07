<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} || {{ config('app.subtitle') }}</title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content=" Health care | Manvendra Pratap Singh | 8707643218 |">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/icons/apple-touch-icon.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/icons/favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/icons/favicon-16x16.png')}}">
    <link rel="manifest" href="assets/images/icons/site.webmanifest">
    <link rel="mask-icon" href="assets/images/icons/safari-pinned-tab.svg" color="#666666">
    <link rel="shortcut icon" href="assets/images/icons/favicon.ico">
    <meta name="apple-mobile-web-app-title" content="Molla">
    <meta name="application-name" content="healthcms">
    <meta name="msapplication-TileColor" content="#cc9966">
    <meta name="theme-color" content="#ffffff"> 
    @include('layouts.frontend.css') 
</head>

<body>
@include('layouts.frontend.header') 
<!-- NAVBAR -->
    {{-- <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="fa-solid fa-heart-pulse"></i> HealthCMS
            </a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item"><a class="btn btn-primary ms-lg-3" href="#">Login</a></li>
                </ul>
            </div>
        </div>
    </nav> --}}

<!-- HERO -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="fw-bold mb-4">Modern Healthcare Management Platform</h1>
                    <p class="lead mb-4">
                        Secure, cloud-based CMS designed for clinics, hospitals & healthcare providers.
                    </p>
                    <a href="#pricing" class="btn btn-primary btn-lg me-2">Start Free Trial</a>
                    <a href="#features" class="btn btn-outline-primary btn-lg">Explore Features</a>
                </div>
                <div class="col-lg-6 text-center mt-4 mt-lg-0">
                    <img src="https://images.unsplash.com/photo-1584438784894-089d6a62b8fa" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

<!-- FEATURES -->
    <section id="features" class="py-5">
        <div class="container text-center">
            <h2 class="fw-bold mb-4">Powerful Features</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card p-4 h-100">
                        <i class="fa-solid fa-user-doctor mb-3"></i>
                        <h5>Patient Management</h5>
                        <p>Secure centralized patient records.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card p-4 h-100">
                        <i class="fa-solid fa-calendar-check mb-3"></i>
                        <h5>Appointments</h5>
                        <p>Smart scheduling & reminders.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card p-4 h-100">
                        <i class="fa-solid fa-shield-halved mb-3"></i>
                        <h5>Data Security</h5>
                        <p>HIPAA-ready secure cloud system.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- HOW IT WORKS -->
    <section id="how" class="py-5 bg-light">
        <div class="container text-center">
            <h2 class="fw-bold mb-4">How It Works</h2>
            <div class="row g-4">
                <div class="col-md-4"><div class="step"><h5>1. Register</h5><p>Create your organization account.</p></div></div>
                <div class="col-md-4"><div class="step"><h5>2. Choose Plan</h5><p>Select a subscription plan.</p></div></div>
                <div class="col-md-4"><div class="step"><h5>3. Access CMS</h5><p>Manage patients & operations.</p></div></div>
            </div>
        </div>
    </section>

<!-- PRICING -->
    <section id="pricing" class="pricing py-5">
        <div class="container text-center">
            <h2 class="fw-bold mb-4">Subscription Plans</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card p-4 h-100">
                        <h5>Starter</h5>
                        <h2 class="text-primary">$49/mo</h2>
                        <p>Small clinics</p>
                        <button class="btn btn-outline-primary w-100">Get Started</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 h-100 popular">
                        <h5>Professional</h5>
                        <h2 class="text-primary">$99/mo</h2>
                        <p>Most popular</p>
                        <button class="btn btn-primary w-100">Start Free Trial</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 h-100">
                        <h5>Enterprise</h5>
                        <h2 class="text-primary">$199/mo</h2>
                        <p>Hospitals</p>
                        <button class="btn btn-outline-primary w-100">Contact Sales</button>
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
                <div class="col-lg-5 mb-4">
                    <div class="p-4 bg-white rounded-4 shadow-sm">
                        <h5 class="fw-bold mb-3">Get in Touch</h5>
                        <p><i class="fa-solid fa-envelope text-primary me-2"></i> support@healthcms.com</p>
                        <p><i class="fa-solid fa-phone text-primary me-2"></i> +1 (555) 123-4567</p>
                        <p><i class="fa-solid fa-location-dot text-primary me-2"></i> Healthcare Tech Park, USA</p>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="p-4 bg-white rounded-4 shadow-sm">
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <input class="form-control" placeholder="Full Name" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="email" class="form-control" placeholder="Email" required>
                                </div>
                            </div>
                            <input class="form-control" placeholder="Organization Name">
                            <textarea class="form-control" rows="4" placeholder="Message"></textarea>
                            <button class="btn btn-primary w-100">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- CTA -->
    <section class="cta py-5 text-center">
        <h2 class="fw-bold mb-3">Ready to Grow Your Healthcare Business?</h2>
        <a class="btn btn-light btn-lg">Start Your Free Trial</a>
    </section>

<!-- FOOTER -->
    <footer class="text-white text-center py-3">
        © 2026 HealthCMS • Secure • Trusted • Scalable
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

</body>


</html>