<?php
// index.php - Fitish Pro Landing Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitish Pro - Your Complete Fitness Journey</title>
    <link rel="stylesheet" href="assets/css/moderns.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="landing-page">
    <!-- Navigation -->
    <nav class="landing-nav">
        <div class="nav-container">
            <a href="index.php" class="landing-logo">
                <i class="fas fa-dumbbell"></i>
                <span>Fitish Pro</span>
            </a>
            <div class="nav-links">
                <a href="#features" class="nav-link">Features</a>
                <a href="#stats" class="nav-link">Why Choose Us</a>
                <a href="#testimonials" class="nav-link">Reviews</a>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-trophy"></i>
                    <span>Join 10,000+ Active Users</span>
                </div>
                <h1 class="hero-title">
                    Transform Your
                    <span class="gradient-text">Fitness Journey</span>
                    with Smart Tracking
                </h1>
                <p class="hero-description">
                    Monitor workouts, track progress, set goals, and stay motivated with our comprehensive fitness platform. 
                    Your personal trainer, nutritionist, and progress tracker - all in one place.
                </p>
                <div class="hero-actions">
                    <a href="register.php" class="btn btn-hero">
                        <i class="fas fa-rocket"></i>
                        Start Free Today
                    </a>
                    <a href="#demo" class="btn btn-ghost">
                        <i class="fas fa-play"></i>
                        Watch Demo
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <strong>10K+</strong>
                        <span>Active Users</span>
                    </div>
                    <div class="stat">
                        <strong>50K+</strong>
                        <span>Workouts Tracked</span>
                    </div>
                    <div class="stat">
                        <strong>95%</strong>
                        <span>Goal Achievement</span>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="fitness-dashboard-preview">
                    <div class="preview-header">
                        <div class="preview-nav">
                            <span class="nav-dot"></span>
                            <span class="nav-dot"></span>
                            <span class="nav-dot"></span>
                        </div>
                        <span class="preview-title">Fitish Pro Dashboard</span>
                    </div>
                    <div class="preview-content">
                        <div class="preview-stat">
                            <i class="fas fa-fire"></i>
                            <div>
                                <strong>1,247</strong>
                                <span>Calories Burned</span>
                            </div>
                        </div>
                        <div class="preview-stat">
                            <i class="fas fa-dumbbell"></i>
                            <div>
                                <strong>24</strong>
                                <span>Workouts This Month</span>
                            </div>
                        </div>
                        <div class="preview-progress">
                            <span>Weekly Goal Progress</span>
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                            </div>
                            <span>78% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-background">
            <div class="hero-shape shape-1"></div>
            <div class="hero-shape shape-2"></div>
            <div class="hero-shape shape-3"></div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2>Everything You Need to Succeed</h2>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Progress Tracking</h3>
                    <p>Visualize your fitness journey with detailed charts and analytics that show your improvements over time.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Goal Setting</h3>
                    <p>Set personalized fitness goals and get intelligent recommendations to achieve them faster.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Social Features</h3>
                    <p>Connect with friends, share achievements, and stay motivated through our supportive community.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3>Smart Insights</h3>
                    <p>Get AI-powered insights about your performance, recovery, and areas for improvement.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Ready</h3>
                    <p>Access your fitness data anywhere with our responsive design that works on all devices.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure & Private</h3>
                    <p>Your health data is encrypted and secure. We never share your personal information.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="stats-showcase">
        <div class="container">
            <div class="stats-grid">
                <div class="stats-content">
                    <h2>Trusted by Fitness Enthusiasts Worldwide</h2>
                    <p>Join thousands of users who have transformed their fitness journey with Fitish Pro.</p>
                    <div class="achievement-stats">
                        <div class="achievement">
                            <i class="fas fa-medal"></i>
                            <div>
                                <strong>2.5M+</strong>
                                <span>Workouts Completed</span>
                            </div>
                        </div>
                        <div class="achievement">
                            <i class="fas fa-fire"></i>
                            <div>
                                <strong>12M+</strong>
                                <span>Calories Burned</span>
                            </div>
                        </div>
                        <div class="achievement">
                            <i class="fas fa-trophy"></i>
                            <div>
                                <strong>85%</strong>
                                <span>Users Reach Goals</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="stats-visual">
                    <div class="floating-card card-1">
                        <i class="fas fa-heartbeat"></i>
                        <span>Heart Rate: 142 BPM</span>
                    </div>
                    <div class="floating-card card-2">
                        <i class="fas fa-stopwatch"></i>
                        <span>Workout: 45 min</span>
                    </div>
                    <div class="floating-card card-3">
                        <i class="fas fa-fire"></i>
                        <span>Calories: 380</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2>What Our Users Say</h2>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"Fitish Pro helped me lose 30 pounds and build the best shape of my life. The progress tracking is incredible!"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <strong>Sarah Johnson</strong>
                            <span>Lost 30 lbs in 6 months</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="testimonial-content">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"The goal setting feature keeps me motivated. I've never been more consistent with my workouts!"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <strong>Mike Chen</strong>
                            <span>Marathon Runner</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="testimonial-content">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"Simple, intuitive, and powerful. Fitish Pro makes fitness tracking actually enjoyable!"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <strong>Emma Davis</strong>
                            <span>Fitness Enthusiast</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Transform Your Fitness?</h2>
                <p>Join thousands of users who are already achieving their fitness goals with Fitish Pro</p>
                <div class="cta-actions">
                    <a href="register.php" class="btn btn-cta">
                        <i class="fas fa-rocket"></i>
                        Get Started Free
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <a href="index.php" class="footer-logo">
                        <i class="fas fa-dumbbell"></i>
                        <span>Fitish Pro</span>
                    </a>
                    <p>Your complete fitness journey starts here</p>
                </div>
                <div class="footer-links">
                    <div class="link-group">
                        <h4>Product</h4>
                        <a href="#features">Features</a>
                        <a href="register.php">Get Started</a>
                        <a href="login.php">Login</a>
                    </div>
                    <div class="link-group">
                        <h4>Support</h4>
                        <a href="#">Help Center</a>
                        <a href="#">Contact Us</a>
                        <a href="#">Privacy Policy</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Fitish Pro. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
