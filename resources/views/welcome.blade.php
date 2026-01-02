<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consulta tu Servicio | Intalnet</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,600,700,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9', // Sky blue-ish
                            600: '#0284c7', // Primary Brand Color approximation
                            700: '#0369a1',
                            900: '#0c4a6e',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-50 font-sans text-slate-800 antialiased selection:bg-brand-500 selection:text-white">

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <!-- Logo Placehoder - Text based for now -->
                <span class="text-2xl font-black text-brand-700 tracking-tighter">
                    INTALNET
                </span>
            </div>
            <div class="hidden md:flex gap-6 text-sm font-medium text-slate-600">
                <a href="https://www.intalnet.com/" class="hover:text-brand-600 transition">Inicio</a>
                <a href="https://www.intalnet.com/planes" class="hover:text-brand-600 transition">Planes</a>
                <a href="https://www.intalnet.com/nosotros" class="hover:text-brand-600 transition">Nosotros</a>
                <a href="/admin/login" class="text-brand-600 hover:text-brand-700 font-bold border border-brand-200 px-4 py-2 rounded-lg hover:bg-brand-50 transition">Acceso Funcionarios</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-brand-900 via-brand-700 to-brand-600 text-white py-20 lg:py-32 overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
        <div class="max-w-4xl mx-auto px-4 relative z-10 text-center">
            <h1 class="text-4xl md:text-6xl font-black mb-6 leading-tight">
                Consulta el estado de tu <br class="hidden md:block" />
                <span class="text-brand-200">Servicio y Soporte</span>
            </h1>
            <p class="text-lg md:text-xl text-brand-100 mb-10 max-w-2xl mx-auto">
                Realiza el seguimiento de tus órdenes de servicio en tiempo real. 
                Nuestro compromiso es atenderte en menos de <span class="font-bold text-white bg-white/20 px-2 rounded">48 horas hábiles</span>.
            </p>

            <!-- Search Form -->
            <form action="{{ route('home') }}" method="GET" class="max-w-xl mx-auto">
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-300 to-brand-500 rounded-xl blur opacity-25 group-hover:opacity-50 transition duration-200"></div>
                    <div class="relative flex bg-white rounded-xl shadow-2xl p-2">
                        <input type="text" name="q" value="{{ $search ?? '' }}" 
                            class="w-full bg-transparent text-slate-900 text-lg px-4 py-3 focus:outline-none placeholder-slate-400" 
                            placeholder="Número de Orden o Cédula..." required>
                        <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-8 py-3 rounded-lg font-bold text-lg transition shadow-lg flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Consultar
                        </button>
                    </div>
                </div>
                <p class="mt-4 text-sm text-brand-200">Ingresa tu número de identificación o el ID de la orden.</p>
            </form>
        </div>
    </section>

    <!-- Results Section -->
    @if(isset($search))
    <section class="py-16 bg-slate-50" id="resultados">
        <div class="max-w-3xl mx-auto px-4">
            @if($orders->count() > 0)
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold text-slate-800">Resultados de búsqueda</h2>
                    <span class="bg-brand-100 text-brand-700 px-3 py-1 rounded-full text-sm font-semibold">{{ $orders->count() }} órdenes encontradas</span>
                </div>

                <div class="space-y-6">
                    @foreach($orders as $orden)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition duration-300">
                        <!-- Card Header -->
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Orden #</span>
                                <span class="text-xl font-black text-slate-800">{{ $orden->numero_orden }}</span>
                            </div>
                            <div>
                                @php
                                    $colors = [
                                        'pendiente' => 'bg-slate-100 text-slate-600',
                                        'asignada' => 'bg-yellow-100 text-yellow-700',
                                        'en_sitio' => 'bg-blue-100 text-blue-700',
                                        'en_proceso' => 'bg-indigo-100 text-indigo-700',
                                        'ejecutada' => 'bg-green-100 text-green-700',
                                        'cerrada' => 'bg-red-100 text-red-700',
                                        'anulada' => 'bg-gray-100 text-gray-500',
                                    ];
                                    $colorClass = $colors[$orden->estado_orden] ?? 'bg-slate-100 text-slate-600';
                                    $label = ucfirst(str_replace('_', ' ', $orden->estado_orden));
                                @endphp
                                <span class="px-3 py-1 rounded-full text-sm font-bold {{ $colorClass }}">
                                    {{ $label }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-bold text-slate-400 uppercase mb-1">Tipo de Servicio</h4>
                                <p class="text-lg font-medium text-slate-800">{{ $orden->tipo_orden }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-400 uppercase mb-1">Fecha de Solicitud</h4>
                                <p class="text-lg font-medium text-slate-800">{{ $orden->fecha_trn ? $orden->fecha_trn->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                            
                            @if($orden->technician)
                            <div class="col-span-2 bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-center gap-4">
                                <div class="bg-brand-100 p-2 rounded-full text-brand-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-500 uppercase">Técnico Asignado</h4>
                                    <p class="font-bold text-slate-800">{{ $orden->technician->name }}</p>
                                </div>
                            </div>
                            @endif

                             <!-- Timeline Simple -->
                             <div class="col-span-2 mt-4">
                                <div class="w-full bg-slate-100 rounded-full h-2.5">
                                    @php
                                        $progress = match($orden->estado_orden) {
                                            'pendiente' => 10,
                                            'asignada' => 30,
                                            'en_sitio' => 50,
                                            'en_proceso' => 70,
                                            'ejecutada' => 90,
                                            'cerrada' => 100,
                                            default => 0
                                        };
                                    @endphp
                                    <div class="bg-brand-500 h-2.5 rounded-full transition-all duration-1000" style="width: {{ $progress }}%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-slate-400 mt-2 font-medium">
                                    <span>Solicitud</span>
                                    <span>Asignada</span>
                                    <span>En Curso</span>
                                    <span>Finalizada</span>
                                </div>
                            </div>

                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-white rounded-2xl shadow-sm border border-slate-100">
                    <div class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">No encontramos resultados</h3>
                    <p class="text-slate-500">No hay órdenes asociadas al número o cédula "{{ $search }}".</p>
                </div>
            @endif
        </div>
    </section>
    @endif

    <!-- Features -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 text-center">
                <div class="p-6">
                    <div class="w-14 h-14 bg-brand-100 text-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Atención Rápida</h3>
                    <p class="text-slate-600 leading-relaxed">Comprometidos con solucionar tus requerimientos en un plazo máximo de 48 horas hábiles.</p>
                </div>
                <div class="p-6">
                    <div class="w-14 h-14 bg-brand-100 text-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Técnicos Certificados</h3>
                    <p class="text-slate-600 leading-relaxed">Personal experto y calificado para garantizar la mejor calidad en tu instalación.</p>
                </div>
                <div class="p-6">
                    <div class="w-14 h-14 bg-brand-100 text-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Tecnología de Punta</h3>
                    <p class="text-slate-600 leading-relaxed">Equipos de última generación y fibra óptica para una conexión estable.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-1 md:col-span-2">
                <span class="text-2xl font-black text-white tracking-tighter block mb-4">
                    INTALNET
                </span>
                <p class="mb-4 max-w-sm">Conectando hogares y empresas con la fibra óptica más rápida y sistemas de seguridad inteligente.</p>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Enlaces</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="https://www.intalnet.com/" class="hover:text-white transition">Inicio</a></li>
                    <li><a href="https://www.intalnet.com/planes" class="hover:text-white transition">Planes</a></li>
                    <li><a href="https://www.intalnet.com/legal" class="hover:text-white transition">Legal</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Contacto</h4>
                <ul class="space-y-2 text-sm">
                    <li>Soporte: pqr@intalnet.com</li>
                    <li>WhatsApp: 314 804 2601</li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-slate-800 text-center text-sm">
            &copy; 2026 Intalnet Telecomunicaciones. Todos los derechos reservados.
        </div>
    </footer>

</body>
</html>
