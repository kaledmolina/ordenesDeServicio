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

    <!-- Hero Section -->
    <section class="hero-pattern relative min-h-screen flex items-center justify-center overflow-hidden">
        <!-- Floating Elements -->
        <div class="absolute top-1/4 left-10 w-24 h-24 bg-brand-400 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob"></div>
        <div class="absolute top-1/3 right-10 w-32 h-32 bg-accent-400 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-2000"></div>
        
        <!-- Access Button Absolute Top Right -->
        <div class="absolute top-6 right-6 z-50">
             <a href="/admin/login" class="text-white/80 hover:text-white text-sm font-semibold transition-colors flex items-center gap-2 group">
                Portal Funcionarios
                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
             </a>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 text-center">
            
            <div class="animate__animated animate__fadeInDown">
                <!-- Logo Centered -->
                <div class="flex justify-center mb-8">
                     <img src="https://www.intalnet.com/assets/img/logo.png" alt="Intalnet Logo" class="h-24 md:h-32 w-auto drop-shadow-2xl hover:scale-105 transition-transform duration-300">
                </div>

                <span class="inline-block py-1 px-3 rounded-full bg-brand-800/50 border border-brand-500/30 text-brand-200 text-xs font-bold tracking-widest uppercase mb-6 backdrop-blur-sm">
                    Consulta de Estado
                </span>
                <h1 class="text-5xl md:text-7xl font-black text-white mb-6 leading-tight tracking-tight">
                    Consulta tu <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-300 via-white to-brand-300">Orden de Servicio</span>
                </h1>
                <p class="text-xl md:text-2xl text-brand-100 mb-12 max-w-2xl mx-auto font-light leading-relaxed">
                    Ingresa tus datos para verificar el estado de tu solicitud en tiempo real.
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
                    @php
                        $animEffect = match($orden->estado_orden) {
                            'pendiente' => 'animate__fadeInUp',
                            'asignada' => 'animate__fadeInLeft',
                            'en_sitio' => 'animate__fadeInRight',
                            'en_proceso' => 'animate__pulse animate__infinite',
                            'ejecutada' => 'animate__tada',
                            'cerrada' => 'animate__zoomIn',
                            'anulada' => 'animate__shakeX',
                            default => 'animate__fadeInUp'
                        };
                    @endphp
                    <div class="bg-white rounded-3xl shadow-card p-0 border border-slate-100 overflow-hidden hover:shadow-glow transition-all duration-300 animate__animated {{ $animEffect }} group">
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
                            
                            @if($orden->estado_orden === 'ejecutada' && !$orden->feedback)
                            <div class="mt-6 flex justify-center">
                                <button onclick="openFeedbackModal('{{ $orden->numero_orden }}')" class="bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-white px-6 py-3 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1 flex items-center gap-2">
                                    <span>⭐</span> Calificar Servicio
                                </button>
                            </div>
                            @endif
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



    <!-- How it Works Section -->
    <section class="relative py-24 px-4 overflow-hidden">
        <div class="max-w-6xl mx-auto relative z-10">
            <h2 class="text-3xl md:text-5xl font-black text-center text-slate-900 mb-20 tracking-tight" data-aos="fade-up">
                Tu Servicio en <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-accent-500">3 Pasos Simples</span>
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <!-- Step 1 -->
                <div class="group relative bg-white p-8 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-slate-100">
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 w-20 h-20 bg-gradient-to-br from-brand-500 to-brand-700 rounded-2xl rotate-3 group-hover:rotate-12 transition-transform shadow-lg flex items-center justify-center text-white font-black text-3xl z-10">
                        1
                    </div>
                    <div class="mt-8 text-center">
                        <h3 class="text-xl font-bold text-slate-800 mb-4">Ingresa tus Datos</h3>
                        <p class="text-slate-500 leading-relaxed">Usa tu número de identificación o el código único de la orden de servicio.</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="group relative bg-white p-8 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-slate-100 delay-100">
                     <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl -rotate-3 group-hover:-rotate-12 transition-transform shadow-lg flex items-center justify-center text-white font-black text-3xl z-10">
                        2
                    </div>
                    <div class="mt-8 text-center">
                        <h3 class="text-xl font-bold text-slate-800 mb-4">Verifica el Estado</h3>
                        <p class="text-slate-500 leading-relaxed">Nuestro sistema te mostrará en tiempo real si tu técnico ya está en camino.</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="group relative bg-white p-8 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-slate-100 delay-200">
                     <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 w-20 h-20 bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl rotate-6 group-hover:rotate-0 transition-transform shadow-lg flex items-center justify-center text-white font-black text-3xl z-10">
                        3
                    </div>
                    <div class="mt-8 text-center">
                        <h3 class="text-xl font-bold text-slate-800 mb-4">Servicio Completado</h3>
                        <p class="text-slate-500 leading-relaxed">Confirma la finalización y califica la atención recibida en menos de 48h.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Canvas for Mouse Effect -->
    <canvas id="interactionCanvas" class="fixed inset-0 pointer-events-none z-0 opacity-60"></canvas>

    <!-- Feedback Modal -->
    <div id="feedbackModal" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-2xl font-black leading-6 text-slate-900 mb-2" id="modal-title">Califica tu Experiencia</h3>
                                <p class="text-sm text-slate-500 mb-6">Ayúdanos a mejorar calificando el servicio recibido.</p>
                                
                                <form id="feedbackForm" class="space-y-6">
                                    @csrf
                                    <input type="hidden" id="feedbackOrdenId" name="orden_id">
                                    
                                    <!-- Star Rating -->
                                    <div class="flex justify-center gap-2 mb-4">
                                        @for($i = 1; $i <= 5; $i++)
                                        <button type="button" onclick="setRating({{ $i }})" class="star-btn text-4xl text-slate-200 transition-colors hover:scale-110 focus:outline-none" data-value="{{ $i }}">★</button>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="rating" id="ratingInput" required>
                                    
                                    <!-- Detailed Questions (Yes/No) -->
                                    <div class="space-y-4 text-left">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Evalúa al Técnico:</label>
                                        
                                        @foreach([
                                            'arrived_on_time' => '¿Llegó a la hora acordada?',
                                            'wears_uniform' => '¿Portaba su uniforme y carnet?',
                                            'is_friendly' => '¿Fue amable y respetuoso?',
                                            'problem_solved' => '¿Solucionó su requerimiento?',
                                            'left_clean' => '¿Dejó limpia el área de trabajo?'
                                        ] as $key => $label)
                                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                                            <span class="text-sm font-medium text-slate-700 w-2/3">{{ $label }}</span>
                                            <div class="flex gap-2">
                                                <input type="hidden" name="{{ $key }}" id="input_{{ $key }}" value="1">
                                                
                                                <button type="button" onclick="setBoolean('{{ $key }}', 1)" id="btn_{{ $key }}_yes" class="px-3 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 transition-all shadow-sm">
                                                    SÍ
                                                </button>
                                                <button type="button" onclick="setBoolean('{{ $key }}', 0)" id="btn_{{ $key }}_no" class="px-3 py-1 rounded-lg text-xs font-bold bg-white text-slate-400 border border-slate-200 hover:bg-red-50 hover:text-red-500 transition-all">
                                                    NO
                                                </button>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                    <!-- Comment -->
                                    <div class="text-left">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Comentario (Opcional)</label>
                                        <textarea name="comment" rows="3" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-500 focus:ring-brand-500 placeholder-slate-400" placeholder="Cuéntanos más detalles..."></textarea>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="button" onclick="submitFeedback()" class="inline-flex w-full justify-center rounded-xl bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 sm:ml-3 sm:w-auto transition-colors">Enviar Calificación</button>
                        <button type="button" onclick="closeFeedbackModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openFeedbackModal(ordenId) {
            document.getElementById('feedbackModal').classList.remove('hidden');
            document.getElementById('feedbackOrdenId').value = ordenId;
            resetForm();
        }

        function closeFeedbackModal() {
            document.getElementById('feedbackModal').classList.add('hidden');
        }

        function resetForm() {
             document.getElementById('feedbackForm').reset();
             setRating(0);
             // Reset all booleans to Yes (Default behavior or neutral?)
             // Let's reset visuals to default "Yes" selected as per DB default, or clear selection? 
             // Better to clear selection or default to Yes. Since we default to true in DB, lets visual Yes.
             ['arrived_on_time', 'wears_uniform', 'is_friendly', 'problem_solved', 'left_clean'].forEach(key => {
                 setBoolean(key, 1);
             });
        }
        
        function setBoolean(key, value) {
            document.getElementById('input_' + key).value = value;
            
            const btnYes = document.getElementById('btn_' + key + '_yes');
            const btnNo = document.getElementById('btn_' + key + '_no');
            
            if(value == 1) {
                btnYes.className = "px-3 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 transition-all shadow-sm transform scale-105";
                btnNo.className = "px-3 py-1 rounded-lg text-xs font-bold bg-white text-slate-400 border border-slate-200 hover:bg-red-50 hover:text-red-500 transition-all opacity-60";
            } else {
                btnYes.className = "px-3 py-1 rounded-lg text-xs font-bold bg-white text-slate-400 border border-slate-200 hover:bg-emerald-50 hover:text-emerald-500 transition-all opacity-60";
                btnNo.className = "px-3 py-1 rounded-lg text-xs font-bold bg-red-100 text-red-700 border border-red-200 transition-all shadow-sm transform scale-105";
            }
        }

        function setRating(value) {
            document.getElementById('ratingInput').value = value;
            document.querySelectorAll('.star-btn').forEach(btn => {
                if (parseInt(btn.dataset.value) <= value) {
                    btn.classList.remove('text-slate-200');
                    btn.classList.add('text-yellow-400');
                } else {
                    btn.classList.add('text-slate-200');
                    btn.classList.remove('text-yellow-400');
                }
            });
        }

        function submitFeedback() {
            const form = document.getElementById('feedbackForm');
            const ordenId = document.getElementById('feedbackOrdenId').value;
            const formData = new FormData(form);
            
            if(!formData.get('rating') || formData.get('rating') == 0) {
                alert('Por favor selecciona una calificación de estrellas.');
                return;
            }

            fetch(`/orden/${ordenId}/feedback`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    closeFeedbackModal();
                    window.location.reload(); // Reload to update button state
                } else {
                    alert(data.message || 'Error al enviar feedback');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al enviar la calificación.');
            });
        }
    </script>

        // Auto-scroll to results if they exist
        @if(isset($search))
        document.addEventListener("DOMContentLoaded", function() {
            const element = document.getElementById("resultados");
            if(element) {
                // Short delay to ensure animations start rendering
                setTimeout(() => {
                    element.scrollIntoView({ behavior: "smooth", block: "start" });
                }, 500);
            }
        });
        @endif

        // Particle System for "Minigame" effect
        const canvas = document.getElementById('interactionCanvas');
        const ctx = canvas.getContext('2d');
        let width, height;
        let particles = [];
        
        // Resize canvas
        function resize() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resize);
        resize();

        // Particle Class
        class Particle {
            constructor(x, y) {
                this.x = x;
                this.y = y;
                this.size = Math.random() * 5 + 2;
                this.speedX = Math.random() * 4 - 2;
                this.speedY = Math.random() * 4 - 2;
                this.color = `hsl(${Math.random() * 60 + 200}, 100%, 50%)`; // Blue/Cyan Hues
                this.life = 100;
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                this.life -= 2;
                this.size *= 0.95;
            }
            draw() {
                ctx.fillStyle = this.color;
                ctx.globalAlpha = this.life / 100;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        // Mouse Interaction
        let mouse = { x: null, y: null };
        document.addEventListener('mousemove', (e) => {
            mouse.x = e.clientX;
            mouse.y = e.clientY;
            // Create particles on move
            for(let i=0; i<3; i++) {
                particles.push(new Particle(mouse.x, mouse.y));
            }
            
            // Blobs parallax (existing)
            const moveX = (e.clientX * -0.05);
            const moveY = (e.clientY * -0.05);
            document.querySelectorAll('.animate-blob').forEach((blob, index) => {
                const speed = (index + 1) * 2;
                blob.style.transform = `translate(${moveX / speed}px, ${moveY / speed}px)`;
            });
        });

        document.addEventListener('click', (e) => {
            // Click explosion
            for(let i=0; i<20; i++) {
                particles.push(new Particle(e.clientX, e.clientY));
            }
        });

        function animate() {
            ctx.clearRect(0, 0, width, height);
            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
                particles[i].draw();
                if (particles[i].life <= 0 || particles[i].size <= 0.5) {
                    particles.splice(i, 1);
                    i--;
                }
            }
            requestAnimationFrame(animate);
        }
        animate();
    </script>
</body>
</html>
