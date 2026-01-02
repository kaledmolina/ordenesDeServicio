<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consulta tu Servicio | Intalnet</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6', // Bright Blue
                            600: '#2563eb', // Intalnet Blue approx
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        },
                        accent: {
                            400: '#22d3ee', // Cyan
                            500: '#06b6d4',
                        }
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    boxShadow: {
                        'glow': '0 0 20px rgba(59, 130, 246, 0.5)',
                        'card': '0 10px 30px -5px rgba(0, 0, 0, 0.05)',
                    }
                }
            }
        }
    </script>
    <style>
        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }
        .hero-pattern {
            background-color: #1e3a8a;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%233b82f6' fill-opacity='0.1'%3E%3Cpath d='M0 38.59l2.83-2.83 1.41 1.41L1.41 40H0v-1.41zM0 1.4l2.83 2.83 1.41-1.41L1.41 0H0v1.41zM38.59 40l-2.83-2.83 1.41-1.41L40 38.59V40h-1.41zM40 1.41l-2.83 2.83-1.41-1.41L38.59 0H40v1.41zM20 18.6l2.83-2.83 1.41 1.41L21.41 20l2.83 2.83-1.41 1.41L20 21.41l-2.83 2.83-1.41-1.41L18.59 20l-2.83-2.83 1.41-1.41L20 18.59z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>

<body class="bg-gray-50 font-sans text-slate-800 antialiased selection:bg-brand-500 selection:text-white">

    <!-- Navbar -->
    <nav class="glass-nav fixed w-full z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-2 animate__animated animate__fadeInLeft">
                    <!-- Modern Logo Representation -->
                    <div class="w-10 h-10 bg-gradient-to-br from-brand-600 to-brand-800 rounded-xl flex items-center justify-center text-white shadow-lg transform hover:rotate-12 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <span class="text-2xl font-black text-slate-800 tracking-tight">
                        INTALNET
                    </span>
                </div>
                <div class="hidden md:flex items-center gap-8 animate__animated animate__fadeInRight">
                    <a href="https://www.intalnet.com/" class="text-sm font-semibold text-slate-500 hover:text-brand-600 transition-colors relative group">
                        Inicio
                        <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-brand-600 transition-all group-hover:w-full"></span>
                    </a>
                    <a href="https://www.intalnet.com/planes" class="text-sm font-semibold text-slate-500 hover:text-brand-600 transition-colors relative group">
                        Planes
                        <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-brand-600 transition-all group-hover:w-full"></span>
                    </a>
                    <a href="/admin/login" class="bg-slate-900 text-white hover:bg-brand-600 px-6 py-2.5 rounded-full font-bold text-sm transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Portal Interno
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-pattern relative min-h-[85vh] flex items-center justify-center pt-20 overflow-hidden">
        <!-- Floating Elements -->
        <div class="absolute top-1/4 left-10 w-24 h-24 bg-brand-400 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob"></div>
        <div class="absolute top-1/3 right-10 w-32 h-32 bg-accent-400 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-2000"></div>
        
        <div class="relative z-10 max-w-5xl mx-auto px-4 text-center">
            
            <div class="animate__animated animate__fadeInDown">
                <span class="inline-block py-1 px-3 rounded-full bg-brand-800/50 border border-brand-500/30 text-brand-200 text-xs font-bold tracking-widest uppercase mb-4 backdrop-blur-sm">
                    Soporte Técnico Especializado
                </span>
                <h1 class="text-5xl md:text-7xl font-black text-white mb-6 leading-tight tracking-tight">
                    Tu Servicio, <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-300 via-white to-brand-300">Bajo Control.</span>
                </h1>
                <p class="text-xl md:text-2xl text-brand-100 mb-12 max-w-2xl mx-auto font-light leading-relaxed">
                    Consulta el estado de tus órdenes en tiempo real y vive la experiencia de servicio Intalnet.
                </p>
            </div>

            <!-- Enhanced Search Form -->
            <div class="max-w-2xl mx-auto animate__animated animate__fadeInUp animate__delay-1s">
                <form action="{{ route('home') }}" method="GET" class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-400 to-accent-400 rounded-2xl blur opacity-40 group-hover:opacity-75 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative flex flex-col md:flex-row bg-white rounded-2xl p-2 shadow-2xl">
                        <div class="flex-1 flex items-center px-4">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <input type="text" name="q" value="{{ $search ?? '' }}" 
                                class="w-full bg-transparent text-slate-800 text-lg px-4 py-4 focus:outline-none placeholder-slate-400" 
                                placeholder="Ingresa tu Cédula o Número de Orden..." required>
                        </div>
                        <button type="submit" class="mt-2 md:mt-0 bg-brand-600 hover:bg-brand-700 text-white px-8 py-4 rounded-xl font-bold text-lg transition-all transform active:scale-95 shadow-lg md:w-auto w-full">
                            Consultar Ahora
                        </button>
                    </div>
                </form>
                <div class="flex items-center justify-center gap-6 mt-8 text-brand-200 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-400 shadow-[0_0_10px_#4ade80]"></div>
                        Respuesta en 48h
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-400 shadow-[0_0_10px_#60a5fa]"></div>
                        Monitoreo en Vivo
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-purple-400 shadow-[0_0_10px_#c084fc]"></div>
                        Soporte Garantizado
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Wave Divider -->
        <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none z-10">
            <svg class="relative block w-[calc(100%+1.3px)] h-[60px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="fill-gray-50"></path>
            </svg>
        </div>
    </section>

    <!-- Results Section -->
    @if(isset($search))
    <section class="py-20 bg-gray-50 min-h-[50vh]" id="resultados">
        <div class="max-w-4xl mx-auto px-4">
            @if($orders->count() > 0)
                <div class="flex items-center justify-between mb-10 animate__animated animate__fadeIn">
                    <h2 class="text-3xl font-black text-slate-800">Resultados Encontrados</h2>
                    <span class="bg-brand-100 text-brand-700 px-4 py-1.5 rounded-full text-sm font-bold border border-brand-200">
                        {{ $orders->count() }} órdenes
                    </span>
                </div>

                <div class="space-y-8">
                    @foreach($orders as $orden)
                    <div class="bg-white rounded-3xl shadow-card p-0 border border-slate-100 overflow-hidden hover:shadow-glow transition-all duration-300 animate__animated animate__fadeInUp group">
                        <!-- Top Bar -->
                        <div class="grad-bg px-8 py-5 border-b border-slate-50 flex flex-wrap gap-4 justify-between items-center bg-white relative">
                            <div class="flex items-center gap-4">
                                <div class="bg-brand-50 p-3 rounded-2xl text-brand-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Orden de Servicio</p>
                                    <p class="text-2xl font-black text-slate-800">#{{ $orden->numero_orden }}</p>
                                </div>
                            </div>
                            
                            @php
                                $statusConf = match($orden->estado_orden) {
                                    'pendiente' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'icon' => '🕒'],
                                    'asignada' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => '👨‍🔧'],
                                    'en_sitio' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => '📍'],
                                    'en_proceso' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'icon' => '⚙️'],
                                    'ejecutada' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => '✅'],
                                    'cerrada' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-800', 'icon' => '🔒'],
                                    'anulada' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'icon' => '❌'],
                                    default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'icon' => '❓']
                                };
                                $label = ucfirst(str_replace('_', ' ', $orden->estado_orden));
                            @endphp
                            <div class="{{ $statusConf['bg'] }} {{ $statusConf['text'] }} px-5 py-2 rounded-xl font-bold text-sm flex items-center gap-2 shadow-sm">
                                <span>{{ $statusConf['icon'] }}</span>
                                {{ $label }}
                            </div>
                        </div>

                        <!-- Content Grid -->
                        <div class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            <div class="space-y-1">
                                <h4 class="text-xs font-bold text-slate-400 uppercase">Servicio Solicitado</h4>
                                <p class="text-lg font-bold text-slate-800">{{ $orden->tipo_orden }}</p>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-xs font-bold text-slate-400 uppercase">Fecha de Solicitud</h4>
                                <p class="text-lg font-bold text-slate-800">{{ $orden->fecha_trn ? $orden->fecha_trn->format('d M, Y') : 'N/A' }}</p>
                            </div>
                            
                            @if($orden->technician)
                            <div class="md:col-span-2 lg:col-span-1 bg-slate-50 p-4 rounded-2xl border border-slate-100 flex items-center gap-3">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-slate-400 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-[10px] font-bold text-slate-400 uppercase">Técnico Asignado</h4>
                                    <p class="font-bold text-sm text-slate-800">{{ $orden->technician->name }}</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Progress Bar (Fake Timeline) -->
                        <div class="px-8 pb-8">
                            <div class="relative pt-6">
                                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded-full bg-slate-100">
                                    @php
                                        $progress = match($orden->estado_orden) {
                                            'pendiente' => 15,
                                            'asignada' => 35,
                                            'en_sitio' => 55,
                                            'en_proceso' => 75,
                                            'ejecutada' => 95,
                                            'cerrada' => 100,
                                            default => 5
                                        };
                                    @endphp
                                    <div style="width:{{ $progress }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-brand-400 to-brand-600 transition-all duration-1000 ease-out relative overflow-hidden group-hover:from-brand-500 group-hover:to-brand-700">
                                        <div class="absolute inset-0 bg-white/20 animate-[shimmer_2s_infinite]"></div>
                                    </div>
                                </div>
                                <div class="flex justify-between text-xs font-bold text-slate-400 uppercase tracking-wider">
                                    <span class="{{ $progress >= 15 ? 'text-brand-600' : '' }}">Solicitud</span>
                                    <span class="{{ $progress >= 35 ? 'text-brand-600' : '' }}">Asignada</span>
                                    <span class="{{ $progress >= 75 ? 'text-brand-600' : '' }}">En Proceso</span>
                                    <span class="{{ $progress >= 95 ? 'text-brand-600' : '' }}">Finalizada</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20 bg-white rounded-3xl shadow-card border border-slate-100 animate__animated animate__fadeIn">
                    <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-2">Sin Resultados</h3>
                    <p class="text-slate-500 max-w-md mx-auto">No hemos encontrado órdenes con la cédula o número brindado: <span class="font-bold text-slate-800">"{{ $search }}"</span>.</p>
                </div>
            @endif
        </div>
    </section>
    @endif

    <!-- Process Features -->
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-black text-slate-900 mb-4">¿Por qué elegir Intalnet?</h2>
                <p class="text-slate-500 max-w-2xl mx-auto">Más que internet, te brindamos una experiencia de conectividad superior con soporte premium.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="group p-8 rounded-3xl bg-slate-50 hover:bg-white border border-transparent hover:border-slate-100 hover:shadow-card transition-all duration-300">
                    <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Velocidad Extrema</h3>
                    <p class="text-slate-600 leading-relaxed">Fibra óptica dedicada hasta tu hogar con latencia mínima para gaming y streaming.</p>
                </div>
                <!-- Feature 2 -->
                <div class="group p-8 rounded-3xl bg-slate-50 hover:bg-white border border-transparent hover:border-slate-100 hover:shadow-card transition-all duration-300">
                    <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Seguridad Total</h3>
                    <p class="text-slate-600 leading-relaxed">Tus datos viajan seguros. Además, ofrecemos soluciones de videovigilancia inteligente.</p>
                </div>
                <!-- Feature 3 -->
                <div class="group p-8 rounded-3xl bg-slate-50 hover:bg-white border border-transparent hover:border-slate-100 hover:shadow-card transition-all duration-300">
                    <div class="w-14 h-14 bg-teal-100 text-teal-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Soporte 24/7</h3>
                    <p class="text-slate-600 leading-relaxed">Nuestro equipo técnico está listo para ayudarte en cualquier momento. Tu conexión no descansa.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center mb-12">
                <span class="text-3xl font-black text-white tracking-tight mb-6 md:mb-0">
                    INTALNET
                </span>
                <div class="flex gap-6">
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-brand-600 hover:text-white transition-all">
                        <span class="sr-only">Facebook</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-brand-600 hover:text-white transition-all">
                        <span class="sr-only">Instagram</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                </div>
            </div>
            
            <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm">
                <p>&copy; 2026 Intalnet Telecomunicaciones. Todos los derechos reservados.</p>
                <div class="flex gap-4 mt-4 md:mt-0">
                    <a href="#" class="hover:text-white transition">Privacidad</a>
                    <a href="#" class="hover:text-white transition">Términos</a>
                    <a href="#" class="hover:text-white transition">Contacto</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Blobs Animation Config -->
    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
    </style>
</body>
</html>
