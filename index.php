<?php
session_start();
// No database connection needed for landing page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ELoan - Modern Loan Application Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        /* Enhanced Styles */
        .hero-section {
            height: 80vh;
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1579621970795-87facc2f976d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            display: flex;
            align-items: center;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .feature-box {
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s;
            border-radius: 10px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .feature-box:hover {
            transform: translateY(-10px);
        }

        .newsletter-section {
            background: #f8f9fa;
            padding: 4rem 0;
        }
        .card {
            transition: transform 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .carousel-item img {
            object-fit: cover;
            height: 300px;
        }
        .search-section {
            background: linear-gradient(135deg, #1e5799, #207cca);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        .search-section h2 {
            color: white;
            margin-bottom: 1.5rem;
        }
        .search-form .form-control, .search-form .form-select {
            border: none;
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
        }
        .search-form .btn-primary {
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: bold;
            background-color: #2c3e50;
            border: none;
            transition: all 0.3s ease;
        }
        .search-form .btn-primary:hover {
            background-color: #34495e;
            transform: translateY(-2px);
        }
        .loan-type-card {
            border-radius: 10px;
            overflow: hidden;
            border: none;
        }
        .loan-type-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .loan-type-card .card-body {
            padding: 1.5rem;
        }
        .testimonial-card {
            background-color: #f8f9fa;
            border-left: 4px solid #1e5799;
        }
        .testimonial-card img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        .stats-card {
            background: linear-gradient(135deg, #1e5799, #207cca);
            color: white;
            border: none;
        }
        .loan-process-step {
            position: relative;
            padding-left: 70px;
            margin-bottom: 30px;
        }
        .loan-process-step .step-number {
            position: absolute;
            left: 0;
            top: 0;
            width: 50px;
            height: 50px;
            background-color: #1e5799;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php include('navbar.php');?>

<!-- Enhanced Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Fast & Easy Loan Applications</h1>
            <p class="lead mb-4">Get the financial support you need with our streamlined online loan application process</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn btn-primary btn-lg me-3">Apply Now</a>
                <a href="#loan-types" class="btn btn-outline-light btn-lg">Explore Loans</a>
            </div>
        </div>
    </div>
</section>

<div class="container mt-5 mb-5">
    <!-- Search Section -->
    <section class="search-section mb-5">
        <h2 class="text-center">Find The Perfect Loan</h2>
        <form method="GET" action="login.php" class="search-form">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-coins"></i></span>
                        <select class="form-select" name="loan_type">
                            <option value="">Select Loan Type</option>
                            <option value="personal">Personal Loan</option>
                            <option value="business">Business Loan</option>
                            <option value="education">Education Loan</option>
                            <option value="home">Home Loan</option>
                            <option value="vehicle">Vehicle Loan</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-dollar-sign"></i></span>
                        <input type="number" class="form-control" placeholder="Loan Amount" name="amount">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-calendar-alt"></i></span>
                        <select class="form-select" name="term">
                            <option value="">Loan Term</option>
                            <option value="6">6 Months</option>
                            <option value="12">12 Months</option>
                            <option value="24">24 Months</option>
                            <option value="36">36 Months</option>
                            <option value="60">60 Months</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 text-center">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search me-2"></i>Find My Loan Options
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- Loan Types Section -->
    <section id="loan-types" class="mb-5">
        <h2 class="text-center mb-4">Our Loan Products</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <div class="col">
                <div class="card loan-type-card h-100">
                    <img src="https://images.unsplash.com/photo-1565514020179-026b92b2d0b5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" class="card-img-top" alt="Personal Loan">
                    <div class="card-body">
                        <h5 class="card-title">Personal Loan</h5>
                        <p class="card-text">Get quick access to funds for your personal needs with competitive interest rates and flexible repayment options.</p>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Interest rates from 10.5%</li>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Loan up to RS 500,000</li>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Tenure up to 5 years</li>
                        </ul>
                        <a href="login.php" class="btn btn-primary w-100">Apply Now</a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card loan-type-card h-100">
                    <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" class="card-img-top" alt="Business Loan">
                    <div class="card-body">
                        <h5 class="card-title">Business Loan</h5>
                        <p class="card-text">Fuel your business growth with our tailored business loans designed for entrepreneurs and SMEs.</p>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Interest rates from 12%</li>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Loan up to RS 10,000,000</li>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Collateral options available</li>
                        </ul>
                        <a href="login.php" class="btn btn-primary w-100">Apply Now</a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card loan-type-card h-100">
                    <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" class="card-img-top" alt="Home Loan">
                    <div class="card-body">
                        <h5 class="card-title">Home Loan</h5>
                        <p class="card-text">Make your dream home a reality with our affordable home loans with competitive interest rates.</p>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Interest rates from 8%</li>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Loan up to RS 50,000,000</li>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Tenure up to 25 years</li>
                        </ul>
                        <a href="login.php" class="btn btn-primary w-100">Apply Now</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="loan_types.php" class="btn btn-outline-primary">View All Loan Types</a>
        </div>
    </section>

    <!-- Loan Process Section -->
    <section class="mb-5">
        <h2 class="text-center mb-5">Simple 4-Step Loan Process</h2>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="loan-process-step">
                    <div class="step-number">1</div>
                    <h4>Apply Online</h4>
                    <p>Fill out our simple online application form with your personal and financial details. It takes less than 5 minutes.</p>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="loan-process-step">
                    <div class="step-number">2</div>
                    <h4>Document Verification</h4>
                    <p>Upload the required documents for verification. Our team will review your application promptly.</p>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="loan-process-step">
                    <div class="step-number">3</div>
                    <h4>Loan Approval</h4>
                    <p>Receive loan approval notification via email and SMS. Check your application status anytime online.</p>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="loan-process-step">
                    <div class="step-number">4</div>
                    <h4>Disbursement</h4>
                    <p>Once approved, the loan amount will be disbursed directly to your bank account within 24-48 hours.</p>
                </div>
            </div>
        </div>
        <div class="text-center">
            <a href="login.php" class="btn btn-lg btn-primary">Start Your Application</a>
        </div>
    </section>
</div>

<!-- Why Choose Us Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose ELoan?</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="feature-box">
                    <i class="fas fa-bolt fa-3x mb-3 text-primary"></i>
                    <h4>Quick Processing</h4>
                    <p>Get your loan approved in as little as 24 hours with our streamlined application process</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-box">
                    <i class="fas fa-lock fa-3x mb-3 text-primary"></i>
                    <h4>Secure & Confidential</h4>
                    <p>Your data is protected with bank-level security and strict privacy protocols</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-box">
                    <i class="fas fa-hand-holding-usd fa-3x mb-3 text-primary"></i>
                    <h4>Competitive Rates</h4>
                    <p>Enjoy some of the most competitive interest rates in the market with transparent terms</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">What Our Customers Say</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card testimonial-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Customer" class="me-3">
                            <div>
                                <h5 class="mb-0">Ahmed Khan</h5>
                                <small class="text-muted">Business Owner</small>
                            </div>
                        </div>
                        <p class="card-text">"The business loan process was incredibly smooth. I received the funds within 3 days of approval, which helped me expand my restaurant business."</p>
                        <div class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card testimonial-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Customer" class="me-3">
                            <div>
                                <h5 class="mb-0">Fatima Ali</h5>
                                <small class="text-muted">Teacher</small>
                            </div>
                        </div>
                        <p class="card-text">"I needed a personal loan for my daughter's education. ELoan offered me the best interest rate and a flexible repayment plan that fits my budget perfectly."</p>
                        <div class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card testimonial-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Customer" class="me-3">
                            <div>
                                <h5 class="mb-0">Imran Malik</h5>
                                <small class="text-muted">Software Engineer</small>
                            </div>
                        </div>
                        <p class="card-text">"The home loan application process was transparent and straightforward. The customer service team was extremely helpful throughout the entire process."</p>
                        <div class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="stats-section text-center my-5">
    <div class="container">
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h3 class="display-4">10K+</h3>
                        <p class="card-text">Happy Customers</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h3 class="display-4">₹500M+</h3>
                        <p class="card-text">Loans Disbursed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h3 class="display-4">5</h3>
                        <p class="card-text">Loan Types</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h3 class="display-4">24/7</h3>
                        <p class="card-text">Customer Support</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h3>Subscribe to Our Newsletter</h3>
                <p class="mb-4">Get updates about new loan products, financial tips, and special offers!</p>
                <form class="row g-3 justify-content-center">
                    <div class="col-auto">
                        <input type="email" class="form-control" placeholder="Enter your email">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Enhanced Footer -->
<footer class="footer bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5>About ELoan</h5>
                <p>ELoan is a leading online loan application management system providing fast, secure, and convenient financial solutions to individuals and businesses.</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white">About Us</a></li>
                    <li><a href="#" class="text-white">Loan Calculator</a></li>
                    <li><a href="#" class="text-white">FAQs</a></li>
                    <li><a href="#" class="text-white">Terms & Conditions</a></li>
                    <li><a href="#" class="text-white">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Contact Us</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-phone me-2"></i> +92-123-456-7890</li>
                    <li><i class="fas fa-envelope me-2"></i> info@eloan.com</li>
                    <li><i class="fas fa-map-marker-alt me-2"></i> 123 Finance Street, Karachi, Pakistan</li>
                </ul>
                <div class="social-links mt-3">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        <hr class="bg-light">
        <div class="row">
            <div class="col text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> ELoan Applications Management System. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>