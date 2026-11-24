@extends('layouts.app')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    /* Scoped to edus-theme wrapper */
    .edus-theme {
        --primary: #0d9488;
        --primary-dark: #0f766e;
        --accent: #facc15;
        --accent-hover: #eab308;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --bg-body: #f8fafc;
        --bg-alt: #f0fdfa;
        --white: #ffffff;
        --glass: rgba(255, 255, 255, 0.90);
        --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 12px 28px -4px rgba(15, 23, 42, 0.08);
        --shadow-lg: 0 24px 48px -8px rgba(13, 148, 136, 0.16);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --radius: 22px;
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-color: var(--bg-body);
        color: var(--text-main);
        overflow-x: hidden;
    }

    .edus-theme * {
        box-sizing: border-box;
    }

    .edus-theme h1,
    .edus-theme h2,
    .edus-theme h3,
    .edus-theme h4 {
        color: var(--text-main);
        font-weight: 800;
        line-height: 1.2;
    }

    .edus-theme .container {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    /* Fixed Glass Header */
    .edus-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: var(--glass);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        z-index: 60;
        border-bottom: 1px solid rgba(13, 148, 136, 0.12);
        transition: var(--transition);
    }

    .edus-header.scrolled {
        box-shadow: var(--shadow-md);
        background: rgba(255, 255, 255, 0.96);
    }

    .edus-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 84px;
    }

    .edus-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }

    .edus-logo-icon {
        height: 44px;
        width: 44px;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(13, 148, 136, 0.20);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid rgba(13, 148, 136, 0.15);
        transition: var(--transition);
    }

    .edus-logo:hover .edus-logo-icon {
        transform: scale(1.05);
    }

    .edus-logo-title {
        font-size: 1.45rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--text-main);
        line-height: 1.1;
    }

    .edus-logo-title span {
        color: var(--primary);
    }

    .edus-logo-sub {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .edus-nav-links {
        display: none;
        align-items: center;
        gap: 2rem;
    }

    @media (min-width: 992px) {
        .edus-nav-links {
            display: flex;
        }
    }

    .edus-nav-links a {
        font-weight: 600;
        color: var(--text-muted);
        font-size: 0.96rem;
        text-decoration: none;
        transition: var(--transition);
        position: relative;
        padding: 6px 0;
    }

    .edus-nav-links a:hover {
        color: var(--primary);
    }

    .edus-nav-links a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0%;
        height: 2px;
        background: var(--primary);
        border-radius: 2px;
        transition: var(--transition);
    }

    .edus-nav-links a:hover::after {
        width: 100%;
    }

    /* Buttons */
    .btn-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: var(--primary);
        color: var(--white) !important;
        padding: 13px 30px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.95rem;
        box-shadow: 0 6px 20px rgba(13, 148, 136, 0.35);
        transition: var(--transition);
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .btn-cta:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 10px 26px rgba(13, 148, 136, 0.45);
    }

    .btn-outline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: var(--primary);
        font-weight: 700;
        padding: 12px 28px;
        border: 2px solid var(--primary);
        border-radius: 50px;
        font-size: 0.95rem;
        text-decoration: none;
        transition: var(--transition);
        background: transparent;
    }

    .btn-outline:hover {
        background: var(--primary);
        color: var(--white) !important;
        transform: translateY(-2px);
    }

    .btn-cta-white {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: var(--white);
        color: var(--primary) !important;
        padding: 14px 34px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 1rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        transition: var(--transition);
        text-decoration: none;
    }

    .btn-cta-white:hover {
        background: var(--accent);
        color: var(--text-main) !important;
        transform: translateY(-2px);
    }

    .btn-cta-outline-white {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border: 2px solid rgba(255, 255, 255, 0.85);
        color: var(--white) !important;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1rem;
        text-decoration: none;
        transition: var(--transition);
        background: transparent;
    }

    .btn-cta-outline-white:hover {
        background: rgba(255, 255, 255, 0.18);
        transform: translateY(-2px);
    }

    /* Sections */
    .edus-section {
        padding: 100px 0;
        position: relative;
    }

    .edus-section-alt {
        background: var(--bg-alt);
    }

    .section-header {
        text-align: center;
        margin-bottom: 3.5rem;
    }

    .section-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(13, 148, 136, 0.10);
        color: var(--primary);
        font-weight: 700;
        font-size: 0.82rem;
        padding: 7px 20px;
        border-radius: 50px;
        margin-bottom: 1.2rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        border: 1px solid rgba(13, 148, 136, 0.16);
    }

    .section-title {
        font-size: 2.5rem;
        color: var(--text-main);
        margin-bottom: 1rem;
        letter-spacing: -0.02em;
    }

    .section-title span {
        color: var(--primary);
    }

    .section-subtitle {
        color: var(--text-muted);
        max-width: 680px;
        margin: 0 auto;
        font-size: 1.08rem;
        line-height: 1.6;
    }

    /* Hero */
    .edus-hero {
        padding-top: 150px;
        padding-bottom: 80px;
        background:
            radial-gradient(circle at 82% 18%, rgba(250, 204, 21, 0.12), transparent 45%),
            radial-gradient(circle at 12% 78%, rgba(13, 148, 136, 0.10), transparent 50%),
            var(--bg-body);
        min-height: 92vh;
        display: flex;
        align-items: center;
    }

    .hero-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 4rem;
        align-items: center;
    }

    @media (min-width: 992px) {
        .hero-grid {
            grid-template-columns: 1.15fr 1fr;
        }
    }

    .hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        background: rgba(13, 148, 136, 0.10);
        color: var(--primary);
        font-weight: 700;
        font-size: 0.85rem;
        padding: 8px 20px;
        border-radius: 50px;
        margin-bottom: 1.6rem;
        border: 1px solid rgba(13, 148, 136, 0.18);
    }

    .hero-content h1 {
        font-size: 3.2rem;
        margin-bottom: 1.4rem;
        letter-spacing: -0.03em;
        line-height: 1.15;
    }

    @media (min-width: 768px) {
        .hero-content h1 {
            font-size: 4rem;
        }
    }

    .hero-content h1 span {
        color: var(--primary);
        display: block;
    }

    .hero-content p {
        font-size: 1.12rem;
        color: var(--text-muted);
        margin-bottom: 2.4rem;
        max-width: 560px;
        line-height: 1.7;
    }

    .hero-btns {
        display: flex;
        gap: 1.2rem;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 2.8rem;
    }

    .hero-stats {
        display: flex;
        align-items: center;
        gap: 1.8rem;
        padding-top: 2rem;
        border-top: 1px solid rgba(13, 148, 136, 0.18);
        flex-wrap: wrap;
    }

    .hero-stat {
        text-align: left;
    }

    .stat-num {
        display: block;
        font-size: 2rem;
        font-weight: 800;
        color: var(--primary);
        line-height: 1;
        letter-spacing: -0.02em;
    }

    .stat-label {
        font-size: 0.84rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 4px;
        display: block;
    }

    .hero-stat-divider {
        width: 1px;
        height: 38px;
        background: rgba(13, 148, 136, 0.22);
    }

    /* Hero Showcase & Floating Badges */
    .hero-image-wrapper {
        position: relative;
    }

    .hero-showcase-card {
        background: #ffffff;
        border-radius: 30px;
        padding: 2rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid rgba(13, 148, 136, 0.12);
        position: relative;
        overflow: hidden;
        animation: heroFloat 6s ease-in-out infinite;
    }

    @keyframes heroFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    .hero-badge,
    .hero-badge-2 {
        position: absolute;
        background: var(--white);
        padding: 14px 20px;
        border-radius: 18px;
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        gap: 14px;
        z-index: 10;
        border: 1px solid rgba(13, 148, 136, 0.12);
        animation: badgePulse 4s ease-in-out infinite;
    }

    @keyframes badgePulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }

    .hero-badge {
        bottom: -22px;
        left: -15px;
    }

    .hero-badge-2 {
        top: -22px;
        right: -15px;
    }

    @media (max-width: 640px) {
        .hero-badge, .hero-badge-2 {
            position: static;
            margin-top: 1rem;
        }
    }

    .badge-icon {
        width: 44px;
        height: 44px;
        background: var(--accent);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        color: var(--text-main);
        flex-shrink: 0;
    }

    .badge-icon-teal {
        background: var(--primary);
        color: var(--white);
    }

    .badge-title {
        font-weight: 800;
        font-size: 0.95rem;
        margin: 0;
        color: var(--text-main);
    }

    .badge-sub {
        font-size: 0.76rem;
        color: var(--text-muted);
        margin: 0;
        font-weight: 500;
    }

    /* Marquee Strip */
    .marquee-strip {
        background: var(--primary);
        padding: 16px 0;
        overflow: hidden;
        position: relative;
    }

    .marquee-track {
        display: flex;
        gap: 3.5rem;
        white-space: nowrap;
        animation: marqueeScroll 32s linear infinite;
        width: max-content;
    }

    @keyframes marqueeScroll {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }

    .marquee-track span {
        color: var(--white);
        font-weight: 600;
        font-size: 0.92rem;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
        letter-spacing: 0.02em;
    }

    .marquee-track span i {
        color: var(--accent);
    }

    /* Core Services Grid */
    .services-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    @media (min-width: 768px) {
        .services-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .services-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .service-card {
        background: var(--white);
        padding: 2.4rem 1.8rem;
        border-radius: var(--radius);
        border: 1px solid rgba(13, 148, 136, 0.08);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .service-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-lg);
        border-color: rgba(13, 148, 136, 0.25);
    }

    .service-icon-box {
        width: 62px;
        height: 62px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        margin-bottom: 1.5rem;
        transition: var(--transition);
    }

    .service-icon-teal {
        background: rgba(13, 148, 136, 0.12);
        color: var(--primary);
    }

    .service-icon-yellow {
        background: rgba(250, 204, 21, 0.20);
        color: #ca8a04;
    }

    .service-icon-blue {
        background: rgba(59, 130, 246, 0.14);
        color: #2563eb;
    }

    .service-icon-emerald {
        background: rgba(16, 185, 129, 0.14);
        color: #059669;
    }

    .service-card:hover .service-icon-teal {
        background: var(--primary);
        color: var(--white);
    }

    .service-card:hover .service-icon-yellow {
        background: #eab308;
        color: var(--white);
    }

    .service-card:hover .service-icon-blue {
        background: #2563eb;
        color: var(--white);
    }

    .service-card:hover .service-icon-emerald {
        background: #059669;
        color: var(--white);
    }

    .service-card h3 {
        font-size: 1.25rem;
        margin-bottom: 0.8rem;
    }

    .service-card p {
        color: var(--text-muted);
        font-size: 0.94rem;
        line-height: 1.6;
        margin-bottom: 1.4rem;
    }

    .service-list {
        list-style: none;
        padding: 0;
        margin: 0 0 1.6rem 0;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .service-list li {
        font-size: 0.86rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }

    .service-list li i {
        color: var(--primary);
        font-size: 0.75rem;
    }

    .service-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        color: var(--primary);
        font-size: 0.92rem;
        text-decoration: none;
        margin-top: auto;
        transition: var(--transition);
    }

    .service-link:hover {
        color: var(--primary-dark);
        transform: translateX(4px);
    }

    /* About / Why Choose Us Grid */
    .about-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 4.5rem;
        align-items: center;
    }

    @media (min-width: 992px) {
        .about-grid {
            grid-template-columns: 1fr 1.15fr;
        }
    }

    .about-image-col {
        position: relative;
    }

    .about-card-visual {
        background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%);
        border: 2px dashed rgba(13, 148, 136, 0.25);
        border-radius: 28px;
        padding: 2.8rem 2.2rem;
        box-shadow: var(--shadow-md);
    }

    .about-years-badge {
        position: absolute;
        top: -24px;
        left: -20px;
        background: var(--primary);
        color: var(--white);
        width: 94px;
        height: 94px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 25px rgba(13, 148, 136, 0.35);
        border: 4px solid #ffffff;
    }

    .years-num {
        font-size: 1.6rem;
        font-weight: 900;
        line-height: 1;
    }

    .years-label {
        font-size: 0.58rem;
        text-align: center;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
    }

    .about-floating-card {
        position: absolute;
        bottom: -24px;
        right: -18px;
        background: var(--white);
        padding: 16px 22px;
        border-radius: 18px;
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1px solid rgba(13, 148, 136, 0.12);
    }

    .about-floating-card i {
        font-size: 2rem;
        color: var(--accent);
    }

    .about-list {
        margin: 2rem 0;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 0;
        list-style: none;
    }

    .about-list li {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        font-weight: 500;
        font-size: 1rem;
        color: var(--text-main);
    }

    .about-list li i {
        width: 26px;
        height: 26px;
        background: var(--primary);
        color: var(--white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        flex-shrink: 0;
        margin-top: 2px;
    }

    /* Features Grid (6 Cards) */
    .features-grid-6 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    @media (min-width: 768px) {
        .features-grid-6 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .features-grid-6 {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .feature-card {
        background: var(--white);
        padding: 2.4rem 2rem;
        border-radius: var(--radius);
        border: 1px solid rgba(13, 148, 136, 0.08);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }

    .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-lg);
        border-color: rgba(13, 148, 136, 0.20);
    }

    .feature-icon {
        width: 58px;
        height: 58px;
        background: rgba(13, 148, 136, 0.10);
        color: var(--primary);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        transition: var(--transition);
    }

    .feature-card:hover .feature-icon {
        background: var(--primary);
        color: var(--white);
        transform: rotate(5deg) scale(1.08);
    }

    .feature-card h3 {
        font-size: 1.22rem;
        margin-bottom: 0.75rem;
    }

    .feature-card p {
        color: var(--text-muted);
        font-size: 0.94rem;
        line-height: 1.6;
        margin: 0;
    }

    /* How It Works Steps */
    .steps-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        position: relative;
    }

    @media (min-width: 992px) {
        .steps-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .step-card {
        background: #ffffff;
        padding: 2.5rem 2rem;
        border-radius: var(--radius);
        border: 1px solid rgba(13, 148, 136, 0.10);
        box-shadow: var(--shadow-sm);
        position: relative;
        text-align: center;
        transition: var(--transition);
    }

    .step-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-md);
    }

    .step-number {
        width: 50px;
        height: 50px;
        background: var(--primary);
        color: #ffffff;
        font-weight: 800;
        font-size: 1.2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem auto;
        box-shadow: 0 6px 16px rgba(13, 148, 136, 0.35);
    }

    .step-card h3 {
        font-size: 1.25rem;
        margin-bottom: 0.8rem;
    }

    .step-card p {
        color: var(--text-muted);
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 0;
    }

    /* Testimonials */
    .testimonials-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    @media (min-width: 768px) {
        .testimonials-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .testimonial-card {
        background: #ffffff;
        padding: 2.2rem;
        border-radius: var(--radius);
        border: 1px solid rgba(13, 148, 136, 0.08);
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: var(--transition);
    }

    .testimonial-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-md);
    }

    .testimonial-stars {
        color: #eab308;
        font-size: 0.95rem;
        margin-bottom: 1.2rem;
        display: flex;
        gap: 4px;
    }

    .testimonial-quote {
        color: var(--text-main);
        font-size: 0.98rem;
        line-height: 1.65;
        margin-bottom: 1.8rem;
        font-style: italic;
    }

    .testimonial-author {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .author-avatar {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: rgba(13, 148, 136, 0.12);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
    }

    .author-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-main);
        margin: 0;
    }

    .author-role {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin: 0;
    }

    /* CTA Section */
    .cta-banner {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        border-radius: 32px;
        padding: 4.5rem 2.5rem;
        color: var(--white);
        text-align: center;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(13, 148, 136, 0.35);
    }

    .cta-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(250, 204, 21, 0.15) 0%, transparent 60%);
        border-radius: 50%;
        pointer-events: none;
    }

    .cta-banner h2 {
        color: var(--white);
        font-size: 2.6rem;
        margin-bottom: 1.2rem;
        letter-spacing: -0.02em;
    }

    .cta-banner p {
        color: rgba(255, 255, 255, 0.88);
        max-width: 650px;
        margin: 0 auto 2.5rem auto;
        font-size: 1.15rem;
        line-height: 1.6;
    }

    .cta-banner .cta-buttons {
        display: flex;
        gap: 1.2rem;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
    }

    /* Accordion / FAQ */
    .faq-wrapper {
        max-width: 800px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .faq-item {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid rgba(13, 148, 136, 0.10);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }

    .faq-trigger {
        width: 100%;
        text-align: left;
        padding: 1.3rem 1.6rem;
        background: none;
        border: none;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--text-main);
    }

    .faq-trigger i {
        color: var(--primary);
        transition: var(--transition);
    }

    .faq-content {
        padding: 0 1.6rem 1.4rem 1.6rem;
        color: var(--text-muted);
        font-size: 0.96rem;
        line-height: 1.7;
    }

    /* Footer */
    .edus-footer {
        background: #ffffff;
        border-top: 1px solid rgba(13, 148, 136, 0.12);
        padding: 70px 0 30px 0;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 3rem;
        margin-bottom: 3.5rem;
    }

    @media (min-width: 768px) {
        .footer-grid {
            grid-template-columns: 1.5fr repeat(3, 1fr);
        }
    }

    .footer-brand p {
        color: var(--text-muted);
        font-size: 0.95rem;
        line-height: 1.7;
        margin: 1.2rem 0 1.6rem 0;
        max-width: 320px;
    }

    .footer-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: var(--text-main);
        margin-bottom: 1.4rem;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .footer-links a {
        color: var(--text-muted);
        text-decoration: none;
        font-size: 0.92rem;
        font-weight: 500;
        transition: var(--transition);
    }

    .footer-links a:hover {
        color: var(--primary);
        padding-left: 4px;
    }

    .footer-bottom {
        border-top: 1px solid rgba(0, 0, 0, 0.06);
        padding-top: 25px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    @media (min-width: 768px) {
        .footer-bottom {
            flex-direction: row;
        }
    }

    .footer-bottom p {
        color: var(--text-muted);
        font-size: 0.88rem;
        margin: 0;
    }

    /* Mobile Drawer */
    .mobile-menu-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.40);
        backdrop-filter: blur(4px);
        z-index: 100;
    }

    .mobile-drawer {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 300px;
        background: #ffffff;
        z-index: 101;
        padding: 2rem 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.12);
    }
</style>

<div class="edus-theme" x-data="{ mobileMenu: false, scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">

    <!-- ===== FIXED GLASS HEADER ===== -->
    <header class="edus-header" :class="{ 'scrolled': scrolled }">
        <div class="container">
            <nav class="edus-nav">
                <!-- Brand Logo -->
                <a href="/" class="edus-logo">
                    <div class="edus-logo-icon">
                        <img src="{{ asset('logo/favicon-512.png') }}" alt="{{ $siteName ?? 'May22SMS' }}" class="h-8 w-8 object-contain">
                    </div>
                    <div>
                        <div class="edus-logo-title">{{ $siteName ?? 'May22SMS' }}<span>.</span></div>
                        <div class="edus-logo-sub">Automated Digital Hub</div>
                    </div>
                </a>

                <!-- Desktop Nav Links -->
                <ul class="edus-nav-links">
                    <li><a href="#services">Services</a></li>
                    <li><a href="#about">Why Us</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="/tutorials" class="text-teal-600 font-extrabold flex items-center gap-1"><span>🎓</span> Tutorials</a></li>
                    <li><a href="#faq">FAQ</a></li>
                </ul>

                <!-- Desktop Actions -->
                <div class="hidden md:flex items-center gap-3">
                    <!-- Dark / Light Mode Toggle -->
                    <button
                        type="button"
                        x-data="{ dark: document.documentElement.classList.contains('dark') }"
                        @may22-theme-changed.window="dark = ($event.detail.theme === 'dark')"
                        @click="dark = (window.toggleSiteTheme() === 'dark')"
                        class="w-10 h-10 rounded-full border border-teal-500/25 bg-white hover:border-teal-500 text-slate-700 flex items-center justify-center transition cursor-pointer shadow-xs"
                        :title="dark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
                        aria-label="Toggle Dark or Light Mode">
                        <i class="fas text-sm" :class="dark ? 'fa-sun text-amber-400' : 'fa-moon text-slate-600'"></i>
                    </button>

                    @auth
                        <a href="/dashboard" class="btn-cta">
                            <i class="fas fa-gauge-high"></i> Dashboard
                        </a>
                    @else
                        <a href="/login" class="font-bold text-slate-700 hover:text-teal-600 px-4 py-2 transition-colors">
                            Log In
                        </a>
                        <a href="/register" class="btn-cta">
                            Get Started Free <i class="fas fa-arrow-right"></i>
                        </a>
                    @endauth
                </div>

                <!-- Mobile Hamburger & Theme Toggle -->
                <div class="flex md:hidden items-center gap-2">
                    <button
                        type="button"
                        x-data="{ dark: document.documentElement.classList.contains('dark') }"
                        @may22-theme-changed.window="dark = ($event.detail.theme === 'dark')"
                        @click="dark = (window.toggleSiteTheme() === 'dark')"
                        class="w-9 h-9 rounded-full border border-teal-500/25 bg-white text-slate-700 flex items-center justify-center transition cursor-pointer"
                        aria-label="Toggle Dark or Light Mode">
                        <i class="fas text-sm" :class="dark ? 'fa-sun text-amber-400' : 'fa-moon text-slate-600'"></i>
                    </button>
                    <button
                        type="button"
                        class="text-teal-600 p-2 text-2xl focus:outline-none"
                        @click="mobileMenu = true"
                        aria-label="Open menu"
                    >
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </nav>
        </div>
    </header>

    <!-- ===== MOBILE DRAWER ===== -->
    <div x-show="mobileMenu" x-cloak class="relative z-50">
        <div class="mobile-menu-backdrop" @click="mobileMenu = false" x-transition.opacity></div>
        <div class="mobile-drawer" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="transform translate-x-full" x-transition:enter-end="transform translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="transform translate-x-0" x-transition:leave-end="transform translate-x-full">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <a href="/" class="edus-logo">
                    <div class="edus-logo-icon">
                        <img src="{{ asset('logo/favicon-512.png') }}" alt="{{ $siteName ?? 'May22SMS' }}" class="h-8 w-8 object-contain">
                    </div>
                    <span class="font-extrabold text-slate-800 text-lg">{{ $siteName ?? 'May22SMS' }}</span>
                </a>
                <button @click="mobileMenu = false" class="text-slate-400 hover:text-slate-700 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="flex flex-col gap-4 text-base font-semibold text-slate-600">
                <a href="#services" @click="mobileMenu = false" class="hover:text-teal-600 py-1">Services</a>
                <a href="#about" @click="mobileMenu = false" class="hover:text-teal-600 py-1">Why Us</a>
                <a href="#features" @click="mobileMenu = false" class="hover:text-teal-600 py-1">Features</a>
                <a href="#how-it-works" @click="mobileMenu = false" class="hover:text-teal-600 py-1">How It Works</a>
                <a href="#faq" @click="mobileMenu = false" class="hover:text-teal-600 py-1">FAQ</a>
            </div>

            <div class="pt-6 border-t border-slate-100 mt-auto flex flex-col gap-3">
                @auth
                    <a href="/dashboard" class="btn-cta w-full text-center">Go to Dashboard</a>
                @else
                    <a href="/login" class="btn-outline w-full text-center">Log In</a>
                    <a href="/register" class="btn-cta w-full text-center">Get Started Free</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- ===== HERO SECTION ===== -->
    <section class="edus-hero" id="home">
        <div class="container">
            <!-- Seasonal Celebration Banner on Homepage if active -->
            @if(isset($activeSeasonalMode) && $activeSeasonalMode && $activeSeasonalMode->banner_enabled && $activeSeasonalMode->shouldDisplayOnPage('home'))
                <div class="mb-8 rounded-3xl p-4 sm:p-5 text-white shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-white/20"
                     style="background: {{ $activeSeasonalMode->banner_gradient ?: "linear-gradient(135deg, {$activeSeasonalMode->secondary_color} 0%, {$activeSeasonalMode->primary_color} 100%)" }};">
                    <div class="flex items-center gap-3.5">
                        <span class="text-3xl shrink-0">{{ $activeSeasonalMode->icon ?: '🎉' }}</span>
                        <div>
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-400 text-slate-950 mb-1">
                                {{ $activeSeasonalMode->badge_text ?: $activeSeasonalMode->name }}
                            </span>
                            <h3 class="text-base sm:text-lg font-black text-white leading-snug" style="color: #ffffff !important;">
                                {{ $activeSeasonalMode->greeting_title }}
                            </h3>
                            <p class="text-xs text-white/90 mt-0.5">{{ $activeSeasonalMode->greeting_subtitle }}</p>
                        </div>
                    </div>
                    <a href="{{ auth()->check() ? '/dashboard#seasonal-celebration-hub' : '/register' }}"
                       class="px-5 py-2.5 rounded-full bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs uppercase tracking-wider shrink-0 transition shadow-md">
                        🎁 Claim Seasonal Gift &rarr;
                    </a>
                </div>
            @endif

            <!-- Announcement Banner if active -->
            @if(($announcementEnabled ?? false) && !empty($announcementText ?? ''))
                <div class="mb-8 inline-flex items-center gap-3 bg-teal-50 border border-teal-200/80 px-5 py-3 rounded-full text-teal-900 shadow-sm">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-teal-600"></span>
                    </span>
                    <span class="text-sm font-bold">{{ $announcementText }}</span>
                    @if(!empty($announcementLink ?? ''))
                        <a href="{{ $announcementLink }}" class="ml-1 text-teal-700 font-extrabold hover:underline text-xs uppercase tracking-wider">Details &rarr;</a>
                    @endif
                </div>
            @endif

            <div class="hero-grid">
                <!-- Left Hero Content -->
                <div class="hero-content">
                    <div class="hero-tag">
                        <i class="fas fa-star text-amber-400"></i> #1 Automated SMS & Digital Services Platform
                    </div>

                    <h1>
                        Empowering Minds
                        <span>Shaping the Digital Future</span>
                    </h1>

                    <p>
                        {{ $homeHeroSubtitle ?? 'At May22SMS, we provide a modern environment for SMS verifications, verified account logs, and social growth. Built for speed, high-volume reliability, and instant delivery.' }}
                    </p>

                    <div class="hero-btns">
                        @auth
                            <a href="/dashboard" class="btn-cta">
                                <i class="fas fa-gauge"></i> Open Dashboard
                            </a>
                        @else
                            <a href="/register" class="btn-cta">
                                Start Free Trial <i class="fas fa-arrow-right"></i>
                            </a>
                        @endauth
                        <a href="#services" class="btn-outline">
                            Explore Services <i class="fas fa-angle-down"></i>
                        </a>
                    </div>

                    <!-- Hero Stats (matching edus stats strip) -->
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <span class="stat-num">50,000+</span>
                            <span class="stat-label">Active Users</span>
                        </div>
                        <div class="hero-stat-divider"></div>
                        <div class="hero-stat">
                            <span class="stat-num">99.9%</span>
                            <span class="stat-label">Delivery Rate</span>
                        </div>
                        <div class="hero-stat-divider"></div>
                        <div class="hero-stat">
                            <span class="stat-num">100+</span>
                            <span class="stat-label">Global Countries</span>
                        </div>
                    </div>
                </div>

                <!-- Right Hero Image & Showcase -->
                <div class="hero-image-wrapper">
                    <!-- Floating Badge Top Right -->
                    <div class="hero-badge-2">
                        <div class="badge-icon badge-icon-teal">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <p class="badge-title">Under 5s</p>
                            <p class="badge-sub">Average OTP Delivery</p>
                        </div>
                    </div>

                    <!-- Main Interactive Showcase Card -->
                    <div class="hero-showcase-card">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Live Verification Hub</span>
                            </div>
                            <span class="bg-teal-50 text-teal-700 text-xs font-extrabold px-3 py-1 rounded-full border border-teal-100">
                                24/7 Automated
                            </span>
                        </div>

                        <!-- Simulated OTP Terminal Widget -->
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 mb-4">
                            <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                                <span class="font-semibold"><i class="fas fa-globe-americas text-teal-600 mr-1"></i> United States (+1)</span>
                                <span class="text-emerald-600 font-bold"><i class="fas fa-check-circle mr-1"></i> Active Number</span>
                            </div>
                            <div class="flex items-center justify-between bg-white rounded-xl p-3 border border-slate-200/80 shadow-xs mb-3">
                                <div class="font-mono font-bold text-slate-800 text-sm tracking-wide">
                                    +1 (205) 490-8812
                                </div>
                                <span class="bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-md font-medium">WhatsApp</span>
                            </div>

                            <!-- Real-time incoming SMS simulator -->
                            <div class="bg-gradient-to-r from-teal-500/10 to-amber-500/10 border border-teal-500/20 rounded-xl p-3.5">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-bold text-teal-900">Incoming Message Received</span>
                                    <span class="text-slate-400 text-[10px]">Just now</span>
                                </div>
                                <p class="text-xs text-slate-600 mb-2">Your WhatsApp registration code is below. Do not share with anyone.</p>
                                <div class="flex items-center justify-between bg-white rounded-lg px-3 py-2 border border-teal-200">
                                    <span class="font-mono font-black text-teal-700 text-lg tracking-widest">749 - 208</span>
                                    <span class="text-xs font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded">
                                        <i class="fas fa-copy"></i> Copied
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Wallet Preview Strip -->
                        <div class="flex items-center justify-between bg-teal-900 text-white rounded-2xl p-4 shadow-sm">
                            <div>
                                <span class="text-[11px] text-teal-200 uppercase tracking-wider font-semibold block">Multi-Currency Balance</span>
                                <span class="font-black text-xl text-yellow-300">₦25,400.00 <span class="text-xs text-white/80 font-normal">($18.50)</span></span>
                            </div>
                            <a href="{{ auth()->check() ? '/wallet' : '/register' }}" class="bg-teal-700 hover:bg-teal-600 text-white text-xs font-bold px-3.5 py-2 rounded-xl transition">
                                <i class="fas fa-plus mr-1"></i> Top Up
                            </a>
                        </div>
                    </div>

                    <!-- Floating Badge Bottom Left -->
                    <div class="hero-badge">
                        <div class="badge-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <div>
                            <p class="badge-title">Top Rated 2026</p>
                            <p class="badge-sub">Auto-Refund Protection</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== MARQUEE STRIP ===== -->
    <div class="marquee-strip">
        <div class="marquee-track">
            <span><i class="fas fa-bolt"></i> Instant SMS OTP Delivery in Under 5 Seconds</span>
            <span><i class="fas fa-globe"></i> Numbers from 100+ Countries Across the Globe</span>
            <span><i class="fas fa-shield-alt"></i> 100% Automatic Refund If No Code Arrives</span>
            <span><i class="fas fa-wallet"></i> Instant Multi-Currency Wallet Top-Up (NGN & USD)</span>
            <span><i class="fas fa-fire"></i> Premium Aged Logs & Social Accounts Store</span>
            <span><i class="fas fa-chart-line"></i> Real Social Media Boosting & Channel Growth</span>
            <span><i class="fas fa-headset"></i> 24/7 Dedicated Support on Telegram & WhatsApp</span>
            <!-- Duplicated for seamless infinite loop -->
            <span><i class="fas fa-bolt"></i> Instant SMS OTP Delivery in Under 5 Seconds</span>
            <span><i class="fas fa-globe"></i> Numbers from 100+ Countries Across the Globe</span>
            <span><i class="fas fa-shield-alt"></i> 100% Automatic Refund If No Code Arrives</span>
            <span><i class="fas fa-wallet"></i> Instant Multi-Currency Wallet Top-Up (NGN & USD)</span>
            <span><i class="fas fa-fire"></i> Premium Aged Logs & Social Accounts Store</span>
            <span><i class="fas fa-chart-line"></i> Real Social Media Boosting & Channel Growth</span>
            <span><i class="fas fa-headset"></i> 24/7 Dedicated Support on Telegram & WhatsApp</span>
        </div>
    </div>

    <!-- ===== CORE SERVICES SECTION ===== -->
    <section class="edus-section" id="services">
        <div class="container">
            <div class="section-header">
                <span class="section-tag"><i class="fas fa-cubes"></i> Our Core Services</span>
                <h2 class="section-title">Everything You Need <span>In One Platform</span></h2>
                <p class="section-subtitle">
                    From automated virtual numbers to social growth campaigns and account logs, we provide cutting-edge infrastructure with guaranteed performance.
                </p>
            </div>

            <div class="services-grid">
                <!-- SMS Verification -->
                <div class="service-card">
                    <div>
                        <div class="service-icon-box service-icon-teal">
                            <i class="fas fa-message"></i>
                        </div>
                        <h3>SMS Verification</h3>
                        <p>Buy temporary virtual numbers from 100+ countries to verify WhatsApp, Telegram, Google, Claude, OpenAI, and more.</p>
                        <ul class="service-list">
                            <li><i class="fas fa-check-circle"></i> Real SIM & VoIP global routes</li>
                            <li><i class="fas fa-check-circle"></i> Automatic instant refund if no code</li>
                            <li><i class="fas fa-check-circle"></i> 10 to 20 minute reception window</li>
                        </ul>
                    </div>
                    <a href="{{ auth()->check() ? '/sms-verify' : '/register' }}" class="service-link">
                        Get Virtual Number <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Logs Store -->
                <div class="service-card">
                    <div>
                        <div class="service-icon-box service-icon-yellow">
                            <i class="fas fa-box-archive"></i>
                        </div>
                        <h3>Logs & Accounts Store</h3>
                        <p>Browse fresh and aged social media and utility logs. Instant automated delivery directly to your dashboard.</p>
                        <ul class="service-list">
                            <li><i class="fas fa-check-circle"></i> High-trust aged profiles</li>
                            <li><i class="fas fa-check-circle"></i> Instant automated checkout</li>
                            <li><i class="fas fa-check-circle"></i> Bulk discounts & promo codes</li>
                        </ul>
                    </div>
                    <a href="{{ auth()->check() ? '/logs-store' : '/register' }}" class="service-link">
                        Browse Logs Store <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Social Boosting -->
                <div class="service-card">
                    <div>
                        <div class="service-icon-box service-icon-blue">
                            <i class="fas fa-arrow-trend-up"></i>
                        </div>
                        <h3>Social Media Boosting</h3>
                        <p>Supercharge your online reach with followers, channel subscribers, video views, and likes across major platforms.</p>
                        <ul class="service-list">
                            <li><i class="fas fa-check-circle"></i> Telegram, Twitter/X, Instagram, TikTok</li>
                            <li><i class="fas fa-check-circle"></i> Fast start with live order tracking</li>
                            <li><i class="fas fa-check-circle"></i> High retention organic engagement</li>
                        </ul>
                    </div>
                    <a href="{{ auth()->check() ? '/social-boosting' : '/register' }}" class="service-link">
                        Boost Your Profile <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Instant Wallet -->
                <div class="service-card">
                    <div>
                        <div class="service-icon-box service-icon-emerald">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h3>Automated Wallet</h3>
                        <p>Fund your account easily with dedicated Nigerian bank virtual accounts, cards, or crypto. Instant credit guaranteed.</p>
                        <ul class="service-list">
                            <li><i class="fas fa-check-circle"></i> Automated virtual bank accounts</li>
                            <li><i class="fas fa-check-circle"></i> Dual currency support (USD & NGN)</li>
                            <li><i class="fas fa-check-circle"></i> Transparent real-time transaction ledger</li>
                        </ul>
                    </div>
                    <a href="{{ auth()->check() ? '/wallet' : '/register' }}" class="service-link">
                        Manage Wallet <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== ABOUT / WHY CHOOSE US (Matching Edus Layout) ===== -->
    <section class="edus-section edus-section-alt" id="about">
        <div class="container">
            <div class="about-grid">
                <!-- Left Visual Column with badges -->
                <div class="about-image-col">
                    <div class="about-card-visual">
                        <div class="flex items-center justify-between mb-6">
                            <span class="text-xs font-bold uppercase tracking-wider text-teal-800 bg-teal-100/70 px-3 py-1 rounded-full">
                                Platform Guarantee
                            </span>
                            <span class="text-amber-500 text-sm">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </span>
                        </div>

                        <h4 class="text-2xl font-black text-slate-800 mb-4">Unmatched Speed & Reliability</h4>
                        <p class="text-slate-600 text-sm leading-relaxed mb-6">
                            We engineered May22SMS from the ground up to eliminate delays, frustrating manual approvals, and lost funds. Everything executes programmatically 24/7.
                        </p>

                        <!-- Inner stat bar -->
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-teal-200/50">
                            <div>
                                <span class="block text-2xl font-black text-teal-700">1,200,000+</span>
                                <span class="text-xs font-semibold text-slate-500">Orders Completed</span>
                            </div>
                            <div>
                                <span class="block text-2xl font-black text-teal-700">&lt; 5s</span>
                                <span class="text-xs font-semibold text-slate-500">Average Webhook Speed</span>
                            </div>
                        </div>
                    </div>

                    <!-- Circular Badge Top Left -->
                    <div class="about-years-badge">
                        <span class="years-num">99.9%</span>
                        <span class="years-label">Success Rate</span>
                    </div>

                    <!-- Floating Card Bottom Right -->
                    <div class="about-floating-card">
                        <i class="fas fa-shield-halved"></i>
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm">Zero Risk Policy</h4>
                            <p class="text-xs text-slate-500">Instant refund if no code arrives</p>
                        </div>
                    </div>
                </div>

                <!-- Right Text Column -->
                <div>
                    <span class="section-tag"><i class="fas fa-shield-check"></i> Why Choose Us</span>
                    <h2 class="section-title">Built for Modern Users <span>Who Value Speed</span></h2>
                    <p class="text-slate-600 text-base leading-relaxed mb-4">
                        Whether you are verifying business messaging accounts, scaling digital marketing agencies, or managing high-volume operations, our automated infrastructure ensures you never hit a bottleneck.
                    </p>

                    <ul class="about-list">
                        <li>
                            <i class="fas fa-check"></i>
                            <div>
                                <strong class="text-slate-800 block text-base font-bold">100% Automated Workflow</strong>
                                <span class="text-slate-500 text-sm">Instant order execution without manual intervention. Your numbers, codes, and logs arrive in seconds.</span>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-check"></i>
                            <div>
                                <strong class="text-slate-800 block text-base font-bold">Automatic Wallet Refunds</strong>
                                <span class="text-slate-500 text-sm">If an SMS verification code is not received within the countdown window, your account balance is credited back immediately.</span>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-check"></i>
                            <div>
                                <strong class="text-slate-800 block text-base font-bold">Bank-Grade Privacy & Security</strong>
                                <span class="text-slate-500 text-sm">Strict zero-retention policies on sensitive communication. End-to-end data encryption across all endpoints.</span>
                            </div>
                        </li>
                    </ul>

                    <div class="pt-2">
                        @auth
                            <a href="/dashboard" class="btn-cta">
                                Visit Dashboard <i class="fas fa-arrow-right"></i>
                            </a>
                        @else
                            <a href="/register" class="btn-cta">
                                Create Free Account <i class="fas fa-arrow-right"></i>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FEATURES SECTION (6 CARDS IN 3 COLS) ===== -->
    <section class="edus-section" id="features">
        <div class="container">
            <div class="section-header">
                <span class="section-tag"><i class="fas fa-wand-magic-sparkles"></i> Platform Features</span>
                <h2 class="section-title">Engineered For <span>Maximum Excellence</span></h2>
                <p class="section-subtitle">
                    Experience seamless digital services backed by robust APIs, reliable payment gateways, and intuitive design.
                </p>
            </div>

            <div class="features-grid-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Lightning Delivery</h3>
                    <p>Orders and SMS messages are routed through ultra-fast direct webhooks, eliminating tedious delays.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-globe-africa"></i>
                    </div>
                    <h3>100+ Global Regions</h3>
                    <p>Access phone numbers from the United States, United Kingdom, Canada, Nigeria, Kenya, Germany, and beyond.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-heart"></i>
                    </div>
                    <h3>Safe & Confidential</h3>
                    <p>We respect your privacy. All verifications and orders are encrypted and isolated from external access.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-money-bill-transfer"></i>
                    </div>
                    <h3>Instant Multi-Payment</h3>
                    <p>Fund your wallet anytime via dedicated virtual account bank transfer, debit cards, or supported crypto.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-screen-button"></i>
                    </div>
                    <h3>Responsive Experience</h3>
                    <p>Fluid and lightweight design that looks gorgeous and responds flawlessly on mobile phones, tablets, and laptops.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Dedicated Support</h3>
                    <p>Need assistance? Reach our friendly support team through internal tickets, WhatsApp community, or Telegram.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== HOW IT WORKS (3 SIMPLE STEPS) ===== -->
    <section class="edus-section edus-section-alt" id="how-it-works">
        <div class="container">
            <div class="section-header">
                <span class="section-tag"><i class="fas fa-route"></i> Simple Process</span>
                <h2 class="section-title">How It Works in <span>3 Easy Steps</span></h2>
                <p class="section-subtitle">Getting started takes less than two minutes. Follow these simple steps.</p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Create Your Account</h3>
                    <p>Register in seconds with your email and instantly fund your multi-currency wallet via bank transfer or card.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>Select Service & Country</h3>
                    <p>Pick the service you need — SMS verification, logs store, or social boost — and choose your target country.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Instant Real-Time Results</h3>
                    <p>View your OTP codes live, download your logs, or watch your social metrics surge in real-time on your dashboard.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== USER TESTIMONIALS ===== -->
    <section class="edus-section" id="testimonials">
        <div class="container">
            <div class="section-header">
                <span class="section-tag"><i class="fas fa-comments"></i> Real Feedback</span>
                <h2 class="section-title">Trusted By <span>50,000+ Customers</span></h2>
                <p class="section-subtitle">See what our community members and digital entrepreneurs say about their experience.</p>
            </div>

            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div>
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-quote">
                            "The SMS verification speed is insane. I used to wait minutes with other vendors; here the code appears in under 5 seconds. The automated refund is a lifesaver."
                        </p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">EO</div>
                        <div>
                            <p class="author-name">Emmanuel O.</p>
                            <p class="author-role">Digital Marketer • Lagos</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div>
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-quote">
                            "Top notch logs store and customer support. Whenever I need Telegram boosts or accounts for my team campaigns, May22SMS delivers without hassle."
                        </p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">TA</div>
                        <div>
                            <p class="author-name">Tunde A.</p>
                            <p class="author-role">Agency Lead • Abuja</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div>
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-quote">
                            "Funding my wallet through the virtual bank account was instant. Clean UI, transparent prices, and genuinely dependable service."
                        </p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">CB</div>
                        <div>
                            <p class="author-name">Chidinma B.</p>
                            <p class="author-role">Crypto Trader • Port Harcourt</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FAQ SECTION ===== -->
    <section class="edus-section edus-section-alt" id="faq">
        <div class="container">
            <div class="section-header">
                <span class="section-tag"><i class="fas fa-circle-question"></i> Common Questions</span>
                <h2 class="section-title">Frequently Asked <span>Questions</span></h2>
                <p class="section-subtitle">Everything you need to know about our services, ordering process, and policies.</p>
            </div>

            <div class="faq-wrapper" x-data="{ active: null }">
                <!-- Q1 -->
                <div class="faq-item">
                    <button class="faq-trigger" @click="active = (active === 1 ? null : 1)">
                        <span>How fast do SMS verification codes arrive?</span>
                        <i class="fas fa-chevron-down" :class="{ 'transform rotate-180 text-teal-600': active === 1 }"></i>
                    </button>
                    <div class="faq-content" x-show="active === 1" x-cloak x-transition>
                        SMS verification codes typically arrive within 3 to 10 seconds after being sent by the service. Our direct webhook infrastructure detects incoming SMS instantly and pushes them directly to your screen.
                    </div>
                </div>

                <!-- Q2 -->
                <div class="faq-item">
                    <button class="faq-trigger" @click="active = (active === 2 ? null : 2)">
                        <span>What happens if I do not receive an SMS code?</span>
                        <i class="fas fa-chevron-down" :class="{ 'transform rotate-180 text-teal-600': active === 2 }"></i>
                    </button>
                    <div class="faq-content" x-show="active === 2" x-cloak x-transition>
                        You are 100% protected. If an SMS code is not received within the active session countdown, your account balance is automatically and instantly refunded without needing to contact support.
                    </div>
                </div>

                <!-- Q3 -->
                <div class="faq-item">
                    <button class="faq-trigger" @click="active = (active === 3 ? null : 3)">
                        <span>How do I fund my account wallet?</span>
                        <i class="fas fa-chevron-down" :class="{ 'transform rotate-180 text-teal-600': active === 3 }"></i>
                    </button>
                    <div class="faq-content" x-show="active === 3" x-cloak x-transition>
                        You can fund your wallet via instant automated bank transfer to your dedicated virtual account (Wema / Moniepoint / Sterling), debit card, or crypto. Your balance is credited automatically in seconds.
                    </div>
                </div>

                <!-- Q4 -->
                <div class="faq-item">
                    <button class="faq-trigger" @click="active = (active === 4 ? null : 4)">
                        <span>Can I buy accounts and logs in bulk?</span>
                        <i class="fas fa-chevron-down" :class="{ 'transform rotate-180 text-teal-600': active === 4 }"></i>
                    </button>
                    <div class="faq-content" x-show="active === 4" x-cloak x-transition>
                        Yes! Our Logs Store supports bulk purchases with promo code discounts and downloadable order archives directly accessible inside your dashboard.
                    </div>
                </div>

                <!-- Q5 -->
                <div class="faq-item">
                    <button class="faq-trigger" @click="active = (active === 5 ? null : 5)">
                        <span>How can I reach support if I need assistance?</span>
                        <i class="fas fa-chevron-down" :class="{ 'transform rotate-180 text-teal-600': active === 5 }"></i>
                    </button>
                    <div class="faq-content" x-show="active === 5" x-cloak x-transition>
                        Our support team is active 24/7. You can submit an internal support ticket, chat with us on WhatsApp, or join our official Telegram channel for live announcements and help.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== HIGH-CONVERTING CTA BANNER ===== -->
    <section class="edus-section" id="cta">
        <div class="container">
            <div class="cta-banner">
                <h2>Ready to Supercharge Your Workflow?</h2>
                <p>
                    Join over 50,000 satisfied users today. Create your free account in under 60 seconds and experience the future of automated digital services.
                </p>
                <div class="cta-buttons">
                    @auth
                        <a href="/dashboard" class="btn-cta-white">
                            Go to Dashboard <i class="fas fa-arrow-right"></i>
                        </a>
                    @else
                        <a href="/register" class="btn-cta-white">
                            Get Started Free <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="/login" class="btn-cta-outline-white">
                            Log In to Account
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- ===== MODERN EDUS-STYLE FOOTER ===== -->
    <footer class="edus-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand Info -->
                <div class="footer-brand">
                    <a href="/" class="edus-logo mb-3">
                        <div class="edus-logo-icon">
                            <img src="{{ asset('logo/favicon-512.png') }}" alt="{{ $siteName ?? 'May22SMS' }}" class="h-8 w-8 object-contain">
                        </div>
                        <div class="edus-logo-title">{{ $siteName ?? 'May22SMS' }}<span>.</span></div>
                    </a>
                    <p>
                        High-speed automated platform for SMS verifications, high-grade account logs, and social growth services.
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="https://chat.whatsapp.com/FCD1Iqs14iPBG4DpiT4Vec?mode=gi_t" target="_blank" rel="noopener noreferrer" class="h-10 w-10 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition">
                            <i class="fab fa-whatsapp text-lg"></i>
                        </a>
                        <a href="https://t.me/may22sms" target="_blank" rel="noopener noreferrer" class="h-10 w-10 rounded-full bg-sky-50 text-sky-600 border border-sky-200 flex items-center justify-center hover:bg-sky-600 hover:text-white transition">
                            <i class="fab fa-telegram text-lg"></i>
                        </a>
                    </div>
                </div>

                <!-- Services Links -->
                <div>
                    <h4 class="footer-title">Services</h4>
                    <ul class="footer-links">
                        <li><a href="{{ auth()->check() ? '/sms-verify' : '/register' }}">SMS Verification</a></li>
                        <li><a href="{{ auth()->check() ? '/logs-store' : '/register' }}">Logs Store</a></li>
                        <li><a href="{{ auth()->check() ? '/social-boosting' : '/register' }}">Social Boosting</a></li>
                        <li><a href="{{ auth()->check() ? '/wallet' : '/register' }}">Wallet Top-Up</a></li>
                    </ul>
                </div>

                <!-- Navigation Links -->
                <div>
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#about">Why Choose Us</a></li>
                        <li><a href="#features">Platform Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="/tutorials">🎓 Service Tutorials</a></li>
                        <li><a href="#faq">Frequently Asked</a></li>
                        <li><a href="/login">User Login</a></li>
                    </ul>
                </div>

                <!-- Contact & Support -->
                <div>
                    <h4 class="footer-title">Support & Community</h4>
                    <ul class="footer-links">
                        <li><a href="https://t.me/may22sms" target="_blank">Telegram Community</a></li>
                        <li><a href="https://chat.whatsapp.com/FCD1Iqs14iPBG4DpiT4Vec?mode=gi_t" target="_blank">WhatsApp Group</a></li>
                        <li><a href="{{ auth()->check() ? '/support-tickets' : '/login' }}">Support Tickets</a></li>
                        <li class="pt-2">
                            <span class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Systems Operational
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Strip -->
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ $siteName ?? 'May22SMS' }}. All rights reserved.</p>
                <p class="text-xs text-slate-400">Engineered with modern digital architecture for peak speed and security.</p>
            </div>
        </div>
    </footer>
</div>
@endsection
