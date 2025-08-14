<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Social Hub Manager')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 25%, #3730a3 50%, #1e40af 75%, #1d4ed8 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .glass-effect {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .glass-white {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .floating-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }
        
        .sidebar-glow {
            box-shadow: 
                4px 0 40px rgba(59, 130, 246, 0.15),
                0 0 60px rgba(99, 102, 241, 0.1);
        }
        
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            position: relative;
            z-index: 2;
        }
        
        .content-glow {
            box-shadow: 
                0 0 60px rgba(59, 130, 246, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen overflow-x-hidden">
    <!-- Partículas flotantes -->
    <div class="floating-particles">
        <div class="particle" style="left: 10%; width: 3px; height: 3px; animation-delay: 0s;"></div>
        <div class="particle" style="left: 20%; width: 2px; height: 2px; animation-delay: 2s;"></div>
        <div class="particle" style="left: 30%; width: 4px; height: 4px; animation-delay: 4s;"></div>
        <div class="particle" style="left: 40%; width: 2px; height: 2px; animation-delay: 6s;"></div>
        <div class="particle" style="left: 50%; width: 3px; height: 3px; animation-delay: 8s;"></div>
        <div class="particle" style="left: 60%; width: 2px; height: 2px; animation-delay: 10s;"></div>
        <div class="particle" style="left: 70%; width: 4px; height: 4px; animation-delay: 12s;"></div>
        <div class="particle" style="left: 80%; width: 3px; height: 3px; animation-delay: 14s;"></div>
        <div class="particle" style="left: 90%; width: 2px; height: 2px; animation-delay: 16s;"></div>
    </div>

    <x-sidebar />

    <!-- Contenido Principal -->
    <main class="main-content">
        <div class="p-8">
            <div class="glass-white rounded-3xl shadow-2xl content-glow min-h-[calc(100vh-100px)]">
                <div class="p-8">
                    @yield('content')
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script>
        lucide.createIcons();
        
        // Crear más partículas dinámicamente
        function createParticle() {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.width = Math.random() * 4 + 2 + 'px';
            particle.style.height = particle.style.width;
            particle.style.animationDuration = Math.random() * 15 + 10 + 's';
            particle.style.animationDelay = Math.random() * 5 + 's';
            
            document.querySelector('.floating-particles').appendChild(particle);
            
            setTimeout(() => {
                particle.remove();
            }, 25000);
        }
        
        // Crear partículas cada cierto tiempo
        setInterval(createParticle, 3000);
    </script>
    @yield('scripts')
</body>
</html>