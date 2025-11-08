<!DOCTYPE html>
<html lang="zh-Hant">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ChargeHub') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Noto Sans TC', sans-serif;
        }

        /* Hero Section with animated gradient */
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            opacity: 0;
            animation: gradientShift 8s ease-in-out infinite;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }

        /* Floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        /* Fade in animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .fade-in-delay-1 {
            transition-delay: 0.2s;
        }

        .fade-in-delay-2 {
            transition-delay: 0.4s;
        }

        .fade-in-delay-3 {
            transition-delay: 0.6s;
        }

        /* Feature cards */
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            transform: translateY(50px);
        }

        .feature-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* ChargeHub Features Section */
        .chargehub-feature-card {
            background: #000;
            color: white;
            padding: 40px;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            animation: cardFloat 6s ease-in-out infinite;
        }

        .chargehub-feature-card:nth-child(1) {
            animation-delay: 0s;
        }

        .chargehub-feature-card:nth-child(2) {
            animation-delay: 2s;
        }

        .chargehub-feature-card:nth-child(3) {
            animation-delay: 4s;
        }

        @keyframes cardFloat {
            0%, 100% { 
                transform: translateY(0px);
            }
            50% { 
                transform: translateY(-10px);
            }
        }

        .chargehub-feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.2;
            transition: opacity 0.4s ease;
        }

        .chargehub-feature-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            animation: glowRotate 8s linear infinite;
            pointer-events: none;
        }

        @keyframes glowRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .chargehub-feature-card-1::before {
            background-image: 
                repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(59, 130, 246, 0.3) 2px, rgba(59, 130, 246, 0.3) 4px),
                repeating-linear-gradient(90deg, transparent, transparent 2px, rgba(59, 130, 246, 0.3) 2px, rgba(59, 130, 246, 0.3) 4px);
            background-size: 20px 20px;
            animation: gridMove 10s linear infinite;
        }

        @keyframes gridMove {
            0% { background-position: 0 0; }
            100% { background-position: 20px 20px; }
        }

        .chargehub-feature-card-2::before {
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(59, 130, 246, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(16, 185, 129, 0.4) 0%, transparent 50%),
                linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            animation: circuitPulse 3s ease-in-out infinite;
        }

        @keyframes circuitPulse {
            0%, 100% { 
                background-position: 0% 0%, 100% 100%, 0% 0%;
                opacity: 0.2;
                transform: scale(1);
            }
            50% { 
                background-position: 20% 20%, 80% 80%, 10% 10%;
                opacity: 0.3;
                transform: scale(1.1);
            }
        }

        .chargehub-feature-card-3::before {
            background-image: 
                linear-gradient(135deg, transparent 0%, rgba(139, 92, 246, 0.3) 25%, transparent 50%),
                linear-gradient(45deg, transparent 0%, rgba(59, 130, 246, 0.3) 25%, transparent 50%);
            background-size: 40px 40px;
            animation: networkFlow 6s linear infinite;
        }

        @keyframes networkFlow {
            0% { background-position: 0 0; }
            100% { background-position: 40px 40px; }
        }

        .chargehub-feature-card:hover {
            transform: translateY(-12px) scale(1.05);
            box-shadow: 0 25px 50px rgba(59, 130, 246, 0.4);
            animation-play-state: paused;
        }

        .chargehub-feature-card:hover::before {
            opacity: 0.4;
            animation-duration: 5s;
        }

        .chargehub-feature-card:hover::after {
            opacity: 0.3;
            animation-duration: 4s;
        }

        .chargehub-feature-card h4 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: white;
            position: relative;
            z-index: 1;
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .chargehub-feature-card.visible h4 {
            opacity: 1;
            transform: translateY(0);
        }

        .chargehub-feature-card h4:nth-of-type(1) {
            transition-delay: 0.2s;
        }

        .chargehub-feature-card p {
            font-size: 1.4rem;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.95);
            position: relative;
            z-index: 1;
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .chargehub-feature-card.visible p {
            opacity: 1;
            transform: translateY(0);
        }

        .chargehub-feature-card.visible p {
            transition-delay: 0.4s;
        }

        .chargehub-feature-card:hover h4,
        .chargehub-feature-card:hover p {
            transform: translateY(-5px);
        }

        /* Partners Section */
        .partners-container {
            position: relative;
            width: 100%;
            margin: 0 auto;
        }

        .partners-scroll {
            overflow: hidden;
            width: 100%;
        }

        .partners-logos {
            display: flex;
            gap: 40px;
            animation: scrollPartners 30s linear infinite;
            will-change: transform;
        }

        @keyframes scrollPartners {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }

        .partner-logo {
            flex-shrink: 0;
            opacity: 0;
            animation: fadeInLogo 1s ease-in forwards;
        }

        .partner-logo:nth-child(1) { animation-delay: 0.1s; }
        .partner-logo:nth-child(2) { animation-delay: 0.2s; }
        .partner-logo:nth-child(3) { animation-delay: 0.3s; }
        .partner-logo:nth-child(4) { animation-delay: 0.4s; }
        .partner-logo:nth-child(5) { animation-delay: 0.5s; }
        .partner-logo:nth-child(6) { animation-delay: 0.6s; }
        .partner-logo:nth-child(7) { animation-delay: 0.7s; }
        .partner-logo:nth-child(8) { animation-delay: 0.1s; }
        .partner-logo:nth-child(9) { animation-delay: 0.2s; }
        .partner-logo:nth-child(10) { animation-delay: 0.3s; }
        .partner-logo:nth-child(11) { animation-delay: 0.4s; }
        .partner-logo:nth-child(12) { animation-delay: 0.5s; }
        .partner-logo:nth-child(13) { animation-delay: 0.6s; }
        .partner-logo:nth-child(14) { animation-delay: 0.7s; }

        @keyframes fadeInLogo {
            to {
                opacity: 1;
            }
        }

        .partner-logo > div {
            transition: transform 0.3s ease;
        }

        .partner-logo:hover > div {
            transform: scale(1.1);
        }

        /* Service Cards Section */
        .service-cards-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            position: relative;
        }

        .service-card {
            position: relative;
            background: white;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            z-index: 10;
        }

        .service-cards-section:hover .service-card:not(:hover) {
            opacity: 0.6;
            filter: brightness(0.7);
        }

        .service-card-content {
            padding: 40px 30px;
            z-index: 2;
            position: relative;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .service-card:hover .service-card-content {
            transform: translateY(-5px);
            padding: 45px 30px;
        }

        .service-card-title {
            font-size: 2rem;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 16px;
            line-height: 1.2;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .service-card:hover .service-card-title {
            font-size: 2.2rem;
            color: #1e3a8a;
        }

        .service-card-text {
            font-size: 1.25rem;
            color: #1f2937;
            line-height: 1.8;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .service-card:hover .service-card-text {
            font-size: 1.35rem;
            opacity: 0.95;
        }

        .service-card-extra {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(59, 130, 246, 0.98);
            padding: 40px 30px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 3;
            overflow-y: auto;
            border-radius: 16px;
        }

        .service-card:hover .service-card-extra {
            opacity: 1;
            transform: translateY(0);
        }

        .service-card-feature {
            margin-bottom: 24px;
            color: white;
        }

        .service-card-feature-icon {
            width: 40px;
            height: 40px;
            margin-bottom: 12px;
            color: white;
        }

        .service-card-feature-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 8px;
            color: white;
        }

        .service-card-feature-text {
            font-size: 0.95rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
        }

        .service-card-cta-button {
            margin-top: 32px;
            padding: 12px 32px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            display: inline-block;
        }

        .service-card-cta-button:hover {
            background: white;
            color: #3b82f6;
        }

        .service-card-image {
            flex: 1;
            min-height: 250px;
            max-height: 300px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            overflow: hidden;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .service-card-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(2px) brightness(0.6);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
        }

        .service-card-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.2) 0%, transparent 100%);
            z-index: 2;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .service-card:hover .service-card-image {
            transform: scale(1.05);
            filter: brightness(1.1);
        }

        .service-card:hover .service-card-image::before {
            filter: blur(0px) brightness(0.8);
        }

        .service-card:hover .service-card-image::after {
            background: linear-gradient(to top, rgba(0,0,0,0.1) 0%, transparent 100%);
        }

        .service-card-1 .service-card-image {
            background-image: url('https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80');
        }

        .service-card-1 .service-card-image::before {
            background-image: url('https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80');
        }

        .service-card-2 .service-card-image {
            background-image: url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80');
        }

        .service-card-2 .service-card-image::before {
            background-image: url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80');
        }

        @media (max-width: 768px) {
            .service-cards-section {
                grid-template-columns: 1fr;
            }
            
            .service-card-content {
                padding: 40px 30px;
            }
            
            .service-card-title {
                font-size: 2rem;
            }
        }

        /* Icon animations */
        .icon-bounce {
            animation: iconBounce 2s ease-in-out infinite;
        }

        @keyframes iconBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Pulse animation */
        .pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Slide in from left */
        .slide-in-left {
            opacity: 0;
            transform: translateX(-50px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .slide-in-left.visible {
            opacity: 1;
            transform: translateX(0);
        }

        /* Slide in from right */
        .slide-in-right {
            opacity: 0;
            transform: translateX(50px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .slide-in-right.visible {
            opacity: 1;
            transform: translateX(0);
        }

        /* Button hover effect */
        .btn-hover {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .btn-hover::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-hover:hover::before {
            width: 300px;
            height: 300px;
        }

        /* Navbar animation */
        nav {
            transition: all 0.3s ease;
        }

        nav.scrolled {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* Text gradient animation */
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Page load animation */
        body {
            overflow-x: hidden;
            overflow-y: auto;
            height: auto;
        }

        html {
            scroll-behavior: smooth;
        }

        .page-content {
            opacity: 0;
        }

        .page-content.loaded {
            opacity: 1;
            transition: opacity 0.6s ease-in;
        }

        /* Initial state - hide everything */
        nav {
            transform: translateY(-100%);
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        nav.loaded {
            transform: translateY(0);
        }

        /* Hero section initial animation */
        .hero-gradient {
            opacity: 0;
            transform: scale(1.1);
            transition: opacity 1s ease-out, transform 1s ease-out;
        }

        .hero-gradient.loaded {
            opacity: 1;
            transform: scale(1);
        }

        .hero-content > * {
            opacity: 0;
            transform: translateY(30px);
        }

        .hero-content.loaded > *:nth-child(1) {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.8s ease-out 0.3s, transform 0.8s ease-out 0.3s;
        }

        .hero-content.loaded > *:nth-child(2) {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.8s ease-out 0.5s, transform 0.8s ease-out 0.5s;
        }

        .hero-content.loaded > *:nth-child(3) {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.8s ease-out 0.7s, transform 0.8s ease-out 0.7s;
        }

        /* Loading screen (optional) */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
        }

        .loading-screen.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loading-text {
            color: white;
            font-size: 3rem;
            font-weight: bold;
            opacity: 0;
            animation: fadeInText 1s ease-out 0.3s forwards;
        }

        @keyframes fadeInText {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .loading-dots {
            display: inline-block;
        }

        .loading-dots::after {
            content: '...';
            animation: dots 1.5s steps(4, end) infinite;
        }

        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }

        /* Split Section with Hover Effects */
        .split-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 800px;
            position: relative;
        }

        .split-panel {
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px 60px;
        }

        .split-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(2px) brightness(0.5);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
        }

        .split-panel::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.7) 0%, rgba(59, 130, 246, 0.6) 100%);
            z-index: 1;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .split-panel-left::before {
            background-image: url('{{ asset("images/chargingcar.png") }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .split-panel-left::after {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.7) 0%, rgba(59, 130, 246, 0.6) 100%);
        }

        .split-panel-right::before {
            background-image: url('https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1200&q=80&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        /* If you have a local image, uncomment this and comment the line above */
        /* .split-panel-right::before {
            background-image: url('{{ asset("images/chargingstation.png") }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        } */

        .split-panel-right::after {
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.5) 0%, rgba(16, 185, 129, 0.4) 100%);
        }

        .split-panel-content {
            position: relative;
            z-index: 2;
            color: white;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .split-panel-icon {
            width: 50px;
            height: 50px;
            margin-bottom: 24px;
            opacity: 0.9;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .split-panel h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 24px;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .split-panel p {
            font-size: 1.25rem;
            line-height: 1.9;
            opacity: 0.95;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Hover Effects */
        .split-panel:hover {
            flex: 1.2;
            z-index: 10;
        }

        .split-panel:hover::before {
            filter: blur(0px) brightness(0.7);
            transform: scale(1.05);
        }

        .split-panel:hover::after {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.5) 0%, rgba(59, 130, 246, 0.4) 100%);
        }

        .split-panel-right:hover::after {
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.5) 0%, rgba(16, 185, 129, 0.4) 100%);
        }

        .split-panel:hover .split-panel-content {
            transform: translateY(-10px);
        }

        .split-panel:hover .split-panel-icon {
            transform: scale(1.2) rotate(5deg);
            opacity: 1;
        }

        .split-panel:hover h3 {
            transform: translateX(10px);
            font-size: 2.8rem;
        }

        .split-panel:hover p {
            opacity: 1;
            transform: translateX(5px);
        }

        /* When one panel is hovered, dim the other */
        .split-section:hover .split-panel:not(:hover) {
            opacity: 0.6;
            filter: grayscale(30%);
        }

        .split-section:hover .split-panel:not(:hover)::before {
            filter: blur(3px) brightness(0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .split-section {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .split-panel {
                min-height: 500px;
                padding: 60px 40px;
            }

            .split-panel h3 {
                font-size: 2rem;
            }

            .split-panel p {
                font-size: 1.1rem;
            }
        }
            </style>
    </head>
<body class="bg-gray-50">
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="text-center">
            <div class="loading-text">
                ChargeHub<span class="loading-dots"></span>
            </div>
        </div>
    </div>
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-indigo-600">ChargeHub 智能充電樁管理平台</span>
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            Dashboard
                        </a>
                        @if (Route::has('map'))
                            <a href="{{ route('map') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                地圖
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            登入
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition-all transform hover:scale-105">
                                註冊
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
                </nav>

    <!-- Split Section with Hover Effects -->
    <section class="split-section fade-in" id="splitSection">
        <!-- Left Panel -->
        <div class="split-panel split-panel-left">
            <div class="split-panel-content">
                <svg class="split-panel-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                <h3>對車主來說</h3>
                <p>
                    市場上各家充電品牌資訊平行，<br>
                    取得充電服務需要下載多款APP反覆查詢
                </p>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="split-panel split-panel-right">
            <div class="split-panel-content">
                <svg class="split-panel-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                <h3>對充電服務供應/應用商來說</h3>
                <p>
                    ChargeHub擔任整合角色，<br>
                    應用商與供應商僅需單次串接，<br>
                    即可共享充電資源
                </p>
            </div>
        </div>
    </section>

    <!-- What ChargeHub can do Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">What ChargeHub can do?</h2>
                <p class="text-xl md:text-2xl text-gray-600 max-w-4xl mx-auto">
                    專為充電服務應用商與客戶端打造，<br>
                    全台領先的「<span class="text-blue-600 font-semibold">充電樁管理平台</span>」
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature Card 1 -->
                <div class="chargehub-feature-card chargehub-feature-card-1 feature-card">
                    <h4>提供充電服務供應商，</h4>
                    <p class="text-white text-lg leading-relaxed">
                        標準OCPI串接介面，<br>
                        快速串接、即刻上線！
                    </p>
                </div>

                <!-- Feature Card 2 -->
                <div class="chargehub-feature-card chargehub-feature-card-2 feature-card">
                    <h4>提供充電服務應用商</h4>
                    <p class="text-white text-lg leading-relaxed">
                        多元串接介面，<br>
                        API、SDK皆具備！
                    </p>
                </div>

                <!-- Feature Card 3 -->
                <div class="chargehub-feature-card chargehub-feature-card-3 feature-card">
                    <h4>提供完整充電資源，</h4>
                    <p class="text-white text-lg leading-relaxed">
                        創造三贏平台！
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Cards Section -->
    <section class="py-20 bg-gray-50 fade-in">
        <div class="service-cards-section">
            <!-- Left Card: Charging Station Operators -->
            <div class="service-card service-card-1">
                <div class="service-card-content">
                    <h3 class="service-card-title">充電站與充電樁<br>業者</h3>
                    <p class="service-card-text">
                        標準OCPI，快速串接即刻觸及<br>
                        全品牌電動車主。
                    </p>
                </div>
                <div class="service-card-image"></div>
                <!-- Extra content on hover -->
                <div class="service-card-extra">
                    <div class="service-card-feature">
                        <svg class="service-card-feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h4 class="service-card-feature-title">便捷省時</h4>
                        <p class="service-card-feature-text">
                            單次串接。不需與各個電動汽車服務供應商進行複雜的簽約談判，不需串接各個介面模組，並可與眾多電動汽車服務供應商平台相兼容。
                        </p>
                    </div>
                    <div class="service-card-feature">
                        <svg class="service-card-feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h4 class="service-card-feature-title">國際標準</h4>
                        <p class="service-card-feature-text">
                            採用國際標準OCPI充電協議，可快速串接觸及全品牌電動車商。並可提供技術輔導，符合營運商需求。
                        </p>
                    </div>
                    <div class="service-card-feature">
                        <svg class="service-card-feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h4 class="service-card-feature-title">客製服務</h4>
                        <p class="service-card-feature-text">
                            協助營運商建置客製化服務項目，開發專屬web、line等應用介面，串接整合性金流服務。
                        </p>
                    </div>
                    <div class="service-card-feature">
                        <svg class="service-card-feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                        </svg>
                        <h4 class="service-card-feature-title">無界擴展</h4>
                        <p class="service-card-feature-text">
                            未來新的電動車車商(主)加入，即可在全台範圍內找到並使用您的充電站充電，大幅提高各充電站的使用率及收益。
                        </p>
                    </div>
                    <a href="#" class="service-card-cta-button">了解方案</a>
                </div>
            </div>

            <!-- Right Card: EV Service Application Providers -->
            <div class="service-card service-card-2">
                <div class="service-card-content">
                    <h3 class="service-card-title">電動汽車服務<br>應用商</h3>
                    <p class="service-card-text">
                        單一串接介面，一次串接享受<br>
                        用之不竭的充電資源。
                    </p>
                </div>
                <div class="service-card-image"></div>
                <!-- Extra content on hover -->
                <div class="service-card-extra">
                    <div class="service-card-feature">
                        <svg class="service-card-feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h4 class="service-card-feature-title">便捷省時</h4>
                        <p class="service-card-feature-text">
                            單次串接。減少與各個充電站營運商(充電樁業者)進行談判及多個介面串接的問題。即可使您的充電服務與全台眾多充電站營運商相兼容，擴增充電服務組合。
                        </p>
                    </div>
                    <div class="service-card-feature">
                        <svg class="service-card-feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <h4 class="service-card-feature-title">整合服務</h4>
                        <p class="service-card-feature-text">
                            提供充電資源、金流、發票和優惠活動(序號雲服務及優惠卷)之一條龍整合性服務。
                        </p>
                    </div>
                    <div class="service-card-feature">
                        <svg class="service-card-feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        <h4 class="service-card-feature-title">資源共享</h4>
                        <p class="service-card-feature-text">
                            未來若新增充電站營運商(充電樁業者)，車主也無需重新安裝APP。
                        </p>
                    </div>
                    <a href="#" class="service-card-cta-button">了解方案</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Platform Introduction Section -->
    <section class="py-20 bg-gray-50 relative overflow-hidden fade-in">
        <!-- Background image -->
        <div class="absolute top-0 right-0 w-1/2 h-full opacity-10">
            <div class="w-full h-full bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1200&q=80'); filter: blur(20px);"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Titles -->
            <div class="text-center mb-12">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">打造充電服務的開放性平台</h2>
                <h3 class="text-3xl md:text-4xl font-bold text-gray-800">提供最完備友善的充電體驗</h3>
            </div>
            
            <!-- Content Box -->
            <div class="max-w-4xl mx-auto">
                <div class="bg-gradient-to-br from-cyan-50 to-blue-50 rounded-2xl p-8 md:p-12 shadow-xl">
                    <div class="space-y-6 text-gray-800">
                        <p class="text-lg md:text-xl leading-relaxed">
                            ChargeHub智慧充電管理平台系統，整合使用者介面與後台管理模組，實現充電樁動態分配與再生能源整合機制，以提升整體充電效率與綠能利用率。系統具備即時查詢與預約功能，使用者可快速瀏覽附近可用充電樁並完成預約操作。資料平台結合可視化儀表板，能即時呈現充電狀態與歷史訂單紀錄，讓消費者清楚掌握每筆充電資訊，同時協助管理者進行營運監控與決策分析，達成智慧化與永續發展目標。
                        </p>
                    </div>
                    <div class="text-center mt-10">
                        <a href="#" class="inline-block bg-gray-800 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-700 transition-all transform hover:scale-105">
                            了解我們更多
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Partners Section -->
    <section class="py-20 bg-white fade-in">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Title with Icon -->
            <div class="text-center mb-12">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <h2 class="text-4xl md:text-5xl font-bold text-blue-600">合作夥伴</h2>
                </div>
            </div>
            
            <!-- Partners Logos -->
            <div class="partners-container overflow-hidden">
                <div class="partners-scroll">
                    <div class="partners-logos">
                        <!-- 靜宜大學資工系 -->
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                靜宜大學資工系
                            </div>
                        </div>
                        <!-- 劉國有教授 -->
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                劉國有教授
                            </div>
                        </div>
                        <!-- 連翊安 -->
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                連翊安
                            </div>
                        </div>
                        <!-- 吳偉成 -->
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                吳偉成
                            </div>
                        </div>
                        <!-- 吳哲維 -->
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                吳哲維
                            </div>
                        </div>
                        <!-- 王竑勛 -->
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                王竑勛
                            </div>
                        </div>
                        <!-- 杜冠霖 -->
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                杜冠霖
                            </div>
                        </div>
                        <!-- Duplicate for seamless loop -->
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                靜宜大學資工系
                            </div>
                        </div>
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                劉國有教授
                            </div>
                        </div>
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                連翊安
                            </div>
                        </div>
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                吳偉成
                            </div>
                        </div>
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                吳哲維
                            </div>
                        </div>
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                王竑勛
                            </div>
                        </div>
                        <div class="partner-logo">
                            <div class="w-40 h-24 flex items-center justify-center text-gray-900 font-bold text-lg">
                                杜冠霖
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Us Section -->
    <section class="py-20 bg-white fade-in" id="contact">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-12">
                <!-- Left Side: Title and Contact Info -->
                <div class="flex-1">
                    <h2 class="text-4xl md:text-5xl font-bold text-blue-900 mb-8">聯絡我們</h2>
                    
                    <!-- Logo -->
                    <div class="mb-8">
                        <span class="text-4xl font-bold text-blue-900">ChargeHub 智能充電樁管理平台</span>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="space-y-4 text-gray-800">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-blue-600 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                            <p class="text-lg">04 2632 8001</p>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-blue-600 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                            <p class="text-lg">wzwwzwwzw1004@gmail.com</p>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-blue-600 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                            <p class="text-lg">433臺中市沙鹿區台灣大道七段200號</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 fade-in">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-gray-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'ChargeHub') }}. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Page load animation sequence
        document.addEventListener('DOMContentLoaded', () => {
            const loadingScreen = document.getElementById('loadingScreen');
            const navbar = document.getElementById('navbar');
            const pageContent = document.body;

            // Initial animation sequence
            setTimeout(() => {
                // Hide loading screen
                loadingScreen.classList.add('hidden');
                
                // Show navbar
                setTimeout(() => {
                    navbar.classList.add('loaded');
                }, 100);

                // Mark page as loaded
                setTimeout(() => {
                    pageContent.classList.add('loaded');
                }, 800);
            }, 1000); // Show loading screen for 1 second

            // Intersection Observer for fade-in animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);

            // Observe all fade-in elements after page loads
            setTimeout(() => {
                // Observe all elements with fade-in class
                document.querySelectorAll('.fade-in').forEach((element) => {
                    observer.observe(element);
                });

                // Observe feature cards
                document.querySelectorAll('.feature-card').forEach((card, index) => {
                    observer.observe(card);
                    // Stagger animation
                    card.style.transitionDelay = `${index * 0.15}s`;
                    
                    // When card becomes visible, trigger content animation
                    const cardObserver = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('visible');
                            }
                        });
                    }, { threshold: 0.3 });
                    
                    cardObserver.observe(card);
                });

                // Observe split section
                const splitSection = document.getElementById('splitSection');
                if (splitSection) {
                    observer.observe(splitSection);
                }
            }, 1200);

            // Navbar scroll effect
            let lastScroll = 0;
            
            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset;
                
                if (currentScroll > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
                
                lastScroll = currentScroll;
            });
        });
    </script>
    </body>
</html>
