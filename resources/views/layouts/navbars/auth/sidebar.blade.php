<!-- sidenav  -->
@if (Request::is('virtual-reality'))
<aside
  class="fixed inset-y-0 xl:animate-fade-up z-990 xl:scale-60 xl:left-[18%] flex-wrap items-center justify-between block w-full p-0 my-4 xl:ml-4 overflow-y-auto antialiased transition-transform duration-200 -translate-x-full bg-white border-0 shadow-none max-w-62.5 ease-nav-brand rounded-2xl xl:translate-x-0 interactive-sidebar">
  @else
  <aside class="max-w-62.5 ease-nav-brand z-990 fixed inset-y-0 my-4 block w-full flex-wrap items-center justify-between overflow-y-auto rounded-2xl border-0 bg-white p-0 antialiased shadow-none transition-transform duration-200 

    {{ (Request::is('rtl') ? 'xl:right-0 mr-4 translate-x-full' : 'xl:left-0 ml-4 -translate-x-full ') }} xl:translate-x-0 xl:bg-transparent interactive-sidebar
    ">

    @endif
    <div class="h-19.5 relative">
      <!-- Background Glow Effect -->
      <div class="absolute -inset-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl blur opacity-0 transition-all duration-700 sidebar-glow"></div>
      
      <i class="absolute top-0 right-0 hidden p-4 opacity-50 cursor-pointer fas fa-times text-slate-400 xl:hidden"
        sidenav-close></i>
      <a class="block px-8 py-6 m-0 text-size-sm whitespace-nowrap text-slate-700 relative z-10 logo-container" href="{{ url('') }}" target="_blank">
        <img src="/assets/img/logo-ct.png"
          class="inline h-full max-w-full transition-all duration-200 ease-nav-brand max-h-8 logo-image" alt="main_logo" />
        <span
          class="{{ (Request::is('rtl') ? 'mr-1' : 'ml-1') }} font-semibold transition-all duration-200 ease-nav-brand logo-text">Devnex FlowBoard</span>
          
        <!-- Animated particles around logo -->
        <div class="logo-particles absolute inset-0 opacity-0 transition-opacity duration-500">
          <div class="particle particle-1"></div>
          <div class="particle particle-2"></div>
          <div class="particle particle-3"></div>
        </div>
      </a>
    </div>

    <hr
      class="h-px mt-0 bg-transparent {{ (Request::is('virtual-reality') ? 'bg-gradient-horizontal-dark' : 'via-black/40 bg-gradient-to-r from-transparent to-transparent') }} animated-hr" />

  <div class="items-center block w-auto h-auto overflow-auto grow basis-full navigation-container">
    <!-- ubah dari max-h-screen h-sidenav menjadi h-auto -->
      <ul class="flex flex-col pl-0 mb-0 nav-list">
        <li class="mt-0.5 w-full nav-item">
          <a class="nav-link py-2.7 text-size-sm ease-nav-brand my-0 mx-4 flex items-center whitespace-nowrap px-4 transition-all duration-300 relative overflow-hidden
                {{ (Request::is('dashboard') ? 'shadow-soft-xl rounded-lg bg-white font-semibold text-slate-700 active' : '') }}"
            href="{{ url('dashboard') }}">

            <!-- Ripple Effect Container -->
            <div class="ripple-container absolute inset-0 overflow-hidden rounded-lg pointer-events-none"></div>
            
            <!-- Hover Background Effect -->
            <div class="hover-bg absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-lg opacity-0 transition-opacity duration-300"></div>

            <div
              class="{{ (Request::is('dashboard') ? ' bg-gradient-fuchsia' : '') }} shadow-soft-2xl mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center stroke-0 text-center xl:p-2.5 relative z-10 icon-wrapper">
              
              <!-- Animated Icon Background -->
              <div class="icon-bg absolute inset-0 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 opacity-0 transition-all duration-300"></div>
              
              <svg width="12px" height="12px" viewBox="0 0 45 40" version="1.1" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink" class="relative z-10 icon-svg">
                <title>home</title>
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                  <g transform="translate(-1716.000000, -439.000000)" fill="#FFFFFF" fill-rule="nonzero">
                    <g transform="translate(1716.000000, 291.000000)">
                      <g transform="translate(0.000000, 148.000000)">
                        <path class="{{ (Request::is('dashboard') ? '' : 'fill-slate-800') }} icon-path"
                          d="M46.7199583,10.7414583 L40.8449583,0.949791667 C40.4909749,0.360605034 39.8540131,0 39.1666667,0 L7.83333333,0 C7.1459869,0 6.50902508,0.360605034 6.15504167,0.949791667 L0.280041667,10.7414583 C0.0969176761,11.0460037 -1.23209662e-05,11.3946378 -1.23209662e-05,11.75 C-0.00758042603,16.0663731 3.48367543,19.5725301 7.80004167,19.5833333 L7.81570833,19.5833333 C9.75003686,19.5882688 11.6168794,18.8726691 13.0522917,17.5760417 C16.0171492,20.2556967 20.5292675,20.2556967 23.494125,17.5760417 C26.4604562,20.2616016 30.9794188,20.2616016 33.94575,17.5760417 C36.2421905,19.6477597 39.5441143,20.1708521 42.3684437,18.9103691 C45.1927731,17.649886 47.0084685,14.8428276 47.0000295,11.75 C47.0000295,11.3946378 46.9030823,11.0460037 46.7199583,10.7414583 Z">
                        </path>
                        <path class="{{ (Request::is('dashboard') ? '' : 'fill-slate-800') }} icon-path"
                          d="M39.198,22.4912623 C37.3776246,22.4928106 35.5817531,22.0149171 33.951625,21.0951667 L33.92225,21.1107282 C31.1430221,22.6838032 27.9255001,22.9318916 24.9844167,21.7998837 C24.4750389,21.605469 23.9777983,21.3722567 23.4960833,21.1018359 L23.4745417,21.1129513 C20.6961809,22.6871153 17.4786145,22.9344611 14.5386667,21.7998837 C14.029926,21.6054643 13.533337,21.3722507 13.0522917,21.1018359 C11.4250962,22.0190609 9.63246555,22.4947009 7.81570833,22.4912623 C7.16510551,22.4842162 6.51607673,22.4173045 5.875,22.2911849 L5.875,44.7220845 C5.875,45.9498589 6.7517757,46.9451667 7.83333333,46.9451667 L19.5833333,46.9451667 L19.5833333,33.6066734 L27.4166667,33.6066734 L27.4166667,46.9451667 L39.1666667,46.9451667 C40.2482243,46.9451667 41.125,45.9498589 41.125,44.7220845 L41.125,22.2822926 C40.4887822,22.4116582 39.8442868,22.4815492 39.198,22.4912623 Z">
                        </path>
                      </g>
                    </g>
                  </g>
                </g>
              </svg>
              
              <!-- Active Indicator -->
              <div class="active-indicator absolute -right-1 -top-1 w-2 h-2 bg-green-500 rounded-full opacity-0 transition-opacity duration-300"></div>
            </div>
            
            <span
              class="{{ (Request::is('rtl') ? 'mr-1' : 'ml-1') }} duration-300 opacity-100 pointer-events-none ease-soft relative z-10 nav-text">Dashboard</span>
              
            <!-- Hover Arrow -->
            <div class="nav-arrow absolute right-4 opacity-0 transform translate-x-2 transition-all duration-300">
              <i class="fas fa-chevron-right text-size-xs"></i>
            </div>
          </a>
        </li>

        <li class="mt-0.5 w-full nav-item">
          <a class="nav-link py-2.7 text-size-sm ease-nav-brand my-0 mx-4 flex items-center whitespace-nowrap px-4 transition-all duration-300 relative overflow-hidden
              {{ (Request::is('signals*') ? 'shadow-soft-xl rounded-lg bg-white font-semibold text-slate-700 active' : '') }}"
            href="{{ route('signals.index') }}">
            
            <!-- Ripple Effect Container -->
            <div class="ripple-container absolute inset-0 overflow-hidden rounded-lg pointer-events-none"></div>
            
            <!-- Hover Background Effect -->
            <div class="hover-bg absolute inset-0 bg-gradient-to-r from-green-500/10 to-emerald-500/10 rounded-lg opacity-0 transition-opacity duration-300"></div>

            <div
              class="{{ (Request::is('signals*') ? ' bg-gradient-fuchsia' : '') }} shadow-soft-2xl mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center stroke-0 text-center xl:p-2.5 relative z-10 icon-wrapper">
              
              <!-- Animated Icon Background -->
              <div class="icon-bg absolute inset-0 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 opacity-0 transition-all duration-300"></div>
              
              <!-- Icon AI/Neural Network dari Font Awesome -->
              <i style="font-size: 1rem;"
                class="fas fa-lg fa-brain ps-2 pe-2 text-center {{ (Request::is('signals*') ? 'text-white' : 'text-dark') }} relative z-10 icon-fa"
                aria-hidden="true"></i>
              
              <!-- Active Indicator -->
              <div class="active-indicator absolute -right-1 -top-1 w-2 h-2 bg-green-500 rounded-full opacity-0 transition-opacity duration-300"></div>
            </div>
            
            <span
              class="{{ (Request::is('rtl') ? 'mr-1' : 'ml-1') }} duration-300 opacity-100 pointer-events-none ease-soft relative z-10 nav-text">Ai Signals</span>
              
            <!-- Hover Arrow -->
            <div class="nav-arrow absolute right-4 opacity-0 transform translate-x-2 transition-all duration-300">
              <i class="fas fa-chevron-right text-size-xs"></i>
            </div>
          </a>
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">       
        </li>        

        <li class="mt-0.5 w-full nav-item">
          <a class="nav-link py-2.7 text-size-sm ease-nav-brand my-0 mx-4 flex items-center whitespace-nowrap px-4 transition-all duration-300 relative overflow-hidden
              {{ (Request::is('performance*') ? 'shadow-soft-xl rounded-lg bg-white font-semibold text-slate-700 active' : '') }}"
            href="{{ route('performance.index') }}">
            
            <div class="ripple-container absolute inset-0 overflow-hidden rounded-lg pointer-events-none"></div>
            <div class="hover-bg absolute inset-0 bg-gradient-to-r from-blue-500/10 to-cyan-500/10 rounded-lg opacity-0 transition-opacity duration-300"></div>

            <div
              class="{{ (Request::is('performance*') ? ' bg-gradient-fuchsia' : '') }} shadow-soft-2xl mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center stroke-0 text-center xl:p-2.5 relative z-10 icon-wrapper">
              
              <div class="icon-bg absolute inset-0 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600 opacity-0 transition-all duration-300"></div>
              
              <i style="font-size: 1rem;"
                class="fas fa-solid fa-chart-line text-center {{ (Request::is('performance*') ? 'text-white' : 'text-dark') }} relative z-10 icon-fa"
                aria-hidden="true"></i>
              
              <div class="active-indicator absolute -right-1 -top-1 w-2 h-2 bg-green-500 rounded-full opacity-0 transition-opacity duration-300"></div>
            </div>
            
            <span
              class="{{ (Request::is('rtl') ? 'mr-1' : 'ml-1') }} duration-300 opacity-100 pointer-events-none ease-soft relative z-10 nav-text">Ai performance</span>
              
            <div class="nav-arrow absolute right-4 opacity-0 transform translate-x-2 transition-all duration-300">
              <i class="fas fa-chevron-right text-size-xs"></i>
            </div>
          </a>
        </li> 

        <li class="mt-0.5 w-full nav-item">
          <a class="nav-link py-2.7 text-size-sm ease-nav-brand my-0 mx-4 flex items-center whitespace-nowrap px-4 transition-all duration-300 relative overflow-hidden
              {{ (Request::is('sector*') ? 'shadow-soft-xl rounded-lg bg-white font-semibold text-slate-700 active' : '') }}"
            href="{{ route('sector.index') }}">
            
            <div class="ripple-container absolute inset-0 overflow-hidden rounded-lg pointer-events-none"></div>
            <div class="hover-bg absolute inset-0 bg-gradient-to-r from-orange-500/10 to-red-500/10 rounded-lg opacity-0 transition-opacity duration-300"></div>

            <div
              class="{{ (Request::is('sector*') ? ' bg-gradient-fuchsia' : '') }} shadow-soft-2xl mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center stroke-0 text-center xl:p-2.5 relative z-10 icon-wrapper">
              
              <div class="icon-bg absolute inset-0 rounded-lg bg-gradient-to-br from-orange-500 to-red-600 opacity-0 transition-all duration-300"></div>
              
              <i style="font-size: 1rem;"
                class="fas fa-fire text-center {{ (Request::is('sector*') ? 'text-white' : 'text-dark') }} relative z-10 icon-fa"
                aria-hidden="true"></i>
              
              <div class="active-indicator absolute -right-1 -top-1 w-2 h-2 bg-green-500 rounded-full opacity-0 transition-opacity duration-300"></div>
            </div>
            
            <span
              class="{{ (Request::is('rtl') ? 'mr-1' : 'ml-1') }} duration-300 opacity-100 pointer-events-none ease-soft relative z-10 nav-text">Hot Sectors</span>
              
            <div class="nav-arrow absolute right-4 opacity-0 transform translate-x-2 transition-all duration-300">
              <i class="fas fa-chevron-right text-size-xs"></i>
            </div>
          </a>
        </li>

        <!-- Menu Baru: Market Regime -->
        <li class="mt-0.5 w-full nav-item">
          <a class="nav-link py-2.7 text-size-sm ease-nav-brand my-0 mx-4 flex items-center whitespace-nowrap px-4 transition-all duration-300 relative overflow-hidden
              {{ (Request::is('market-regime*') ? 'shadow-soft-xl rounded-lg bg-white font-semibold text-slate-700 active' : '') }}"
            href="{{ route('dashboard.market') }}">
            
            <div class="ripple-container absolute inset-0 overflow-hidden rounded-lg pointer-events-none"></div>
            <div class="hover-bg absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 rounded-lg opacity-0 transition-opacity duration-300"></div>

            <div
              class="{{ (Request::is('market-regime*') ? ' bg-gradient-fuchsia' : '') }} shadow-soft-2xl mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center stroke-0 text-center xl:p-2.5 relative z-10 icon-wrapper">
              
              <div class="icon-bg absolute inset-0 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 opacity-0 transition-all duration-300"></div>
              
              <i style="font-size: 1rem;"
                class="fas fa-chart-bar text-center {{ (Request::is('market-regime*') ? 'text-white' : 'text-dark') }} relative z-10 icon-fa"
                aria-hidden="true"></i>
              
              <div class="active-indicator absolute -right-1 -top-1 w-2 h-2 bg-green-500 rounded-full opacity-0 transition-opacity duration-300"></div>
            </div>
            
            <span
              class="{{ (Request::is('rtl') ? 'mr-1' : 'ml-1') }} duration-300 opacity-100 pointer-events-none ease-soft relative z-10 nav-text">Market Regime</span>
              
            <!-- New Badge -->
            <div class="new-badge absolute right-8 bg-teal-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-size-xs font-bold opacity-0 transform scale-0 transition-all duration-300">
              <i class="fas fa-star text-size-xs"></i>
            </div>
              
            <div class="nav-arrow absolute right-4 opacity-0 transform translate-x-2 transition-all duration-300">
              <i class="fas fa-chevron-right text-size-xs"></i>
            </div>
          </a>
        </li>

        <!-- Menu Baru: Virtual Portfolio -->
        <li class="mt-0.5 w-full nav-item">
          <a class="nav-link py-2.7 text-size-sm ease-nav-brand my-0 mx-4 flex items-center whitespace-nowrap px-4 transition-all duration-300 relative overflow-hidden
              {{ (Request::is('portfolio*') ? 'shadow-soft-xl rounded-lg bg-white font-semibold text-slate-700 active' : '') }}"
            href="{{ route('portfolio') }}">
            
            <div class="ripple-container absolute inset-0 overflow-hidden rounded-lg pointer-events-none"></div>
            <div class="hover-bg absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-green-500/10 rounded-lg opacity-0 transition-opacity duration-300"></div>

            <div
              class="{{ (Request::is('portfolio*') ? ' bg-gradient-fuchsia' : '') }} shadow-soft-2xl mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center stroke-0 text-center xl:p-2.5 relative z-10 icon-wrapper">
              
              <div class="icon-bg absolute inset-0 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 opacity-0 transition-all duration-300"></div>
              
              <i style="font-size: 1rem;"
                class="fas fa-wallet text-center {{ (Request::is('portfolio*') ? 'text-white' : 'text-dark') }} relative z-10 icon-fa"
                aria-hidden="true"></i>
              
              <div class="active-indicator absolute -right-1 -top-1 w-2 h-2 bg-green-500 rounded-full opacity-0 transition-opacity duration-300"></div>
            </div>
            
            <span
              class="{{ (Request::is('rtl') ? 'mr-1' : 'ml-1') }} duration-300 opacity-100 pointer-events-none ease-soft relative z-10 nav-text">Virtual Portfolio</span>
              
            <!-- New Badge -->
            <div class="new-badge absolute right-8 bg-teal-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-size-xs font-bold opacity-0 transform scale-0 transition-all duration-300">
              <i class="fas fa-plus text-size-xs"></i>
            </div>
              
            <div class="nav-arrow absolute right-4 opacity-0 transform translate-x-2 transition-all duration-300">
              <i class="fas fa-chevron-right text-size-xs"></i>
            </div>
          </a>
        </li>

        <!-- Menu Baru: Smart Signals -->
        <li class="mt-0.5 w-full nav-item">
          <a class="nav-link py-2.7 text-size-sm ease-nav-brand my-0 mx-4 flex items-center whitespace-nowrap px-4 transition-all duration-300 relative overflow-hidden
              {{ (Request::is('smart-signals*') ? 'shadow-soft-xl rounded-lg bg-white font-semibold text-slate-700 active' : '') }}"
            href="{{ route('smart-signals') }}">
            
            <div class="ripple-container absolute inset-0 overflow-hidden rounded-lg pointer-events-none"></div>
            <div class="hover-bg absolute inset-0 bg-gradient-to-r from-rose-500/10 to-pink-500/10 rounded-lg opacity-0 transition-opacity duration-300"></div>

            <div
              class="{{ (Request::is('smart-signals*') ? ' bg-gradient-fuchsia' : '') }} shadow-soft-2xl mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center stroke-0 text-center xl:p-2.5 relative z-10 icon-wrapper">
              
              <div class="icon-bg absolute inset-0 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 opacity-0 transition-all duration-300"></div>
              
              <i style="font-size: 1rem;"
                class="fas fa-bolt text-center {{ (Request::is('smart-signals*') ? 'text-white' : 'text-dark') }} relative z-10 icon-fa"
                aria-hidden="true"></i>
              
              <div class="active-indicator absolute -right-1 -top-1 w-2 h-2 bg-green-500 rounded-full opacity-0 transition-opacity duration-300"></div>
            </div>
            
            <span
              class="{{ (Request::is('rtl') ? 'mr-1' : 'ml-1') }} duration-300 opacity-100 pointer-events-none ease-soft relative z-10 nav-text">Smart Signals</span>
              
            <!-- Hot Badge -->
            <div class="new-badge absolute right-8 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-size-xs font-bold opacity-0 transform scale-0 transition-all duration-300">
              <i class="fas fa-bolt text-size-xs"></i>
            </div>
              
            <div class="nav-arrow absolute right-4 opacity-0 transform translate-x-2 transition-all duration-300">
              <i class="fas fa-chevron-right text-size-xs"></i>
            </div>
          </a>
        </li>

        <li class="w-full mt-4 section-divider">
          <h6
            class="{{ (Request::is('rtl') ? 'pr-6 mr-2' : 'pl-6 ml-2') }} font-bold leading-tight uppercase text-size-xs opacity-60 section-title">
            Manage
            <!-- Animated underline -->
            <div class="section-underline w-8 h-0.5 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full mt-1 transition-all duration-500"></div>
          </h6>
        </li>

        <!-- Virtual Reality dengan efek futuristik -->
        <li class="mt-0.5 w-full nav-item">
          <a class="nav-link py-2.7 text-size-sm ease-nav-brand my-0 mx-4 flex items-center whitespace-nowrap px-4 transition-all duration-300 relative overflow-hidden
              {{ (Request::is('virtual-reality') ? 'shadow-soft-xl rounded-lg bg-white font-semibold text-slate-700 active' : '') }}"
            href="{{ url('virtual-reality') }}">
            
            <div class="ripple-container absolute inset-0 overflow-hidden rounded-lg pointer-events-none"></div>
            <div class="hover-bg absolute inset-0 bg-gradient-to-r from-teal-500/10 to-cyan-500/10 rounded-lg opacity-0 transition-opacity duration-300"></div>

            <div
              class="{{ (Request::is('virtual-reality') ? ' bg-gradient-fuchsia' : '') }} shadow-soft-2xl mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center stroke-0 text-center xl:p-2.5 relative z-10 icon-wrapper">
              
              <div class="icon-bg absolute inset-0 rounded-lg bg-gradient-to-br from-teal-500 to-cyan-600 opacity-0 transition-all duration-300"></div>
              
              <!-- VR Icon dengan efek khusus -->
              <div class="relative z-10 vr-icon-container">
                <i style="font-size: 1rem;"
                  class="fas fa-vr-cardboard text-center {{ (Request::is('virtual-reality') ? 'text-white' : 'text-dark') }} icon-fa"
                  aria-hidden="true"></i>
              </div>
              
              <!-- Glow Effect untuk VR -->
              <div class="absolute -inset-1 bg-cyan-500 rounded-lg blur opacity-0 group-hover:opacity-30 transition-all duration-500"></div>
              
              <div class="active-indicator absolute -right-1 -top-1 w-2 h-2 bg-green-500 rounded-full opacity-0 transition-opacity duration-300"></div>
            </div>
            
            <span
              class="{{ (Request::is('rtl') ? 'mr-1' : 'ml-1') }} duration-300 opacity-100 pointer-events-none ease-soft relative z-10 nav-text">Virtual Reality</span>
              
            <div class="nav-arrow absolute right-4 opacity-0 transform translate-x-2 transition-all duration-300">
              <i class="fas fa-chevron-right text-size-xs"></i>
            </div>
          </a>
        </li>

        <!-- Documentation dengan efek profesional -->
        <li class="mt-0.5 w-full nav-item">
          <a class="nav-link py-2.7 text-size-sm ease-nav-brand my-0 mx-4 flex items-center whitespace-nowrap px-4 transition-all duration-300 relative overflow-hidden
              {{ (Request::is('documentation') ? 'shadow-soft-xl rounded-lg bg-white font-semibold text-slate-700 active' : '') }}"
            href="{{ url('documentation') }}">
            
            <div class="ripple-container absolute inset-0 overflow-hidden rounded-lg pointer-events-none"></div>
            <div class="hover-bg absolute inset-0 bg-gradient-to-r from-amber-500/10 to-orange-500/10 rounded-lg opacity-0 transition-opacity duration-300"></div>

            <div
              class="{{ (Request::is('documentation') ? ' bg-gradient-fuchsia' : '') }} shadow-soft-2xl mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center stroke-0 text-center xl:p-2.5 relative z-10 icon-wrapper">
              
              <div class="icon-bg absolute inset-0 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 opacity-0 transition-all duration-300"></div>
              
              <!-- Documentation Icon dengan efek khusus -->
              <div class="relative z-10 docs-icon-container">
                <i style="font-size: 1rem;"
                  class="fas fa-book text-center {{ (Request::is('documentation') ? 'text-white' : 'text-dark') }} icon-fa"
                  aria-hidden="true"></i>
              </div>
              
              <!-- Page Flip Effect -->
              <div class="absolute inset-0 rounded-lg bg-amber-200 opacity-0 group-hover:opacity-20 transition-all duration-500 transform group-hover:rotate-2"></div>
              
              <div class="active-indicator absolute -right-1 -top-1 w-2 h-2 bg-green-500 rounded-full opacity-0 transition-opacity duration-300"></div>
            </div>
            
            <span
              class="{{ (Request::is('rtl') ? 'mr-1' : 'ml-1') }} duration-300 opacity-100 pointer-events-none ease-soft relative z-10 nav-text">Documentation</span>
              
            <!-- Help Badge -->
            <div class="help-badge absolute right-8 bg-amber-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-size-xs font-bold opacity-0 transform scale-0 transition-all duration-300">
              <i class="fas fa-question text-size-xs"></i>
            </div>
              
            <div class="nav-arrow absolute right-4 opacity-0 transform translate-x-2 transition-all duration-300">
              <i class="fas fa-chevron-right text-size-xs"></i>
            </div>
          </a>
        </li>

      </ul>
    </div>
    
    <!-- Sidebar Footer dengan Interactive Elements -->
    <div class="sidebar-footer p-4 border-t border-gray-200 mt-auto">
      <div class="user-profile flex items-center space-x-3 cursor-pointer group relative p-2 rounded-lg transition-all duration-300 hover:bg-gray-50">
        <div class="relative">
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold relative overflow-hidden user-avatar">
            <span>U</span>
            <!-- Online Status Indicator -->
            <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 rounded-full border-2 border-white online-indicator"></div>
          </div>
          <!-- Pulse Animation -->
          <div class="absolute inset-0 rounded-full bg-green-500 opacity-0 group-hover:opacity-30 group-hover:animate-ping transition-all duration-1000"></div>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-size-sm font-semibold text-gray-800 truncate user-name">User Name</p>
          <p class="text-size-xs text-gray-500 truncate user-role">Administrator</p>
        </div>
        <div class="transform transition-transform duration-300 group-hover:rotate-180">
          <i class="fas fa-chevron-down text-size-xs text-gray-400"></i>
        </div>
        
        <!-- Dropdown Menu (akan muncul saat hover) -->
        <div class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-lg shadow-xl border border-gray-200 opacity-0 invisible transform translate-y-2 transition-all duration-300 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 z-20 user-dropdown">
          <div class="py-2">
            <a href="{{ url('user-profile') }}" class="flex items-center px-4 py-2 text-size-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200">
              <i class="fas fa-user-circle mr-3 text-gray-400"></i>
              My Profile
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-size-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200">
              <i class="fas fa-cog mr-3 text-gray-400"></i>
              Settings
            </a>
            <a href="#" class="flex items-center px-4 py-2 text-size-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200">
              <i class="fas fa-bell mr-3 text-gray-400"></i>
              Notifications
            </a>
            <hr class="my-2">
            <a href="#" class="flex items-center px-4 py-2 text-size-sm text-red-600 hover:bg-red-50 transition-colors duration-200">
              <i class="fas fa-sign-out-alt mr-3"></i>
              Logout
            </a>
          </div>
        </div>
      </div>
    </div>
  </aside>

  <!-- end sidenav -->

<style>
/* Enhanced Sidebar Styles */
.interactive-sidebar {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(20px);
}

/* Logo Animation */
.logo-container:hover .logo-image {
  transform: scale(1.1) rotate(5deg);
  filter: drop-shadow(0 0 10px rgba(99, 102, 241, 0.3));
}

.logo-container:hover .logo-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.logo-container:hover .logo-particles {
  opacity: 1;
}

.particle {
  position: absolute;
  width: 4px;
  height: 4px;
  background: currentColor;
  border-radius: 50%;
  animation: float 3s infinite linear;
}

.particle-1 {
  top: 10%;
  left: 20%;
  color: #3b82f6;
  animation-delay: 0s;
}

.particle-2 {
  top: 30%;
  right: 15%;
  color: #8b5cf6;
  animation-delay: 1s;
}

.particle-3 {
  bottom: 20%;
  left: 50%;
  color: #06b6d4;
  animation-delay: 2s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0) translateX(0);
    opacity: 0.6;
  }
  25% {
    transform: translateY(-10px) translateX(5px);
    opacity: 0.8;
  }
  50% {
    transform: translateY(-5px) translateX(10px);
    opacity: 0.4;
  }
  75% {
    transform: translateY(-8px) translateX(-5px);
    opacity: 0.7;
  }
}

/* Navigation Item Animations */
.nav-link {
  position: relative;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav-link:hover {
  transform: translateX(8px);
}

.nav-link:hover .hover-bg {
  opacity: 1;
}

.nav-link:hover .icon-wrapper {
  transform: scale(1.1);
}

.nav-link:hover .icon-bg {
  opacity: 1;
}

.nav-link:hover .nav-arrow {
  opacity: 1;
  transform: translateX(0);
}

.nav-link:hover .icon-fa,
.nav-link:hover .icon-svg {
  animation: iconWobble 0.6s ease;
}

/* Special effects for profile menu */
.nav-link:hover .profile-badge {
  opacity: 1;
  transform: scale(1);
}

.nav-link:hover .new-badge {
  opacity: 1;
  transform: scale(1);
}

.nav-link:hover .help-badge {
  opacity: 1;
  transform: scale(1);
}

.nav-link.active .active-indicator {
  opacity: 1;
}

.nav-link.active .icon-wrapper {
  box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.4);
}

/* Profile Icon Special Effect */
.profile-icon-container:hover .fa-user {
  animation: profileBounce 0.8s ease;
}

@keyframes profileBounce {
  0%, 20%, 53%, 80%, 100% {
    transform: translate3d(0,0,0);
  }
  40%, 43% {
    transform: translate3d(0,-8px,0);
  }
  70% {
    transform: translate3d(0,-4px,0);
  }
  90% {
    transform: translate3d(0,-2px,0);
  }
}

/* VR Icon Special Effect */
.vr-icon-container:hover .fa-vr-cardboard {
  animation: vrSpin 1s ease;
}

@keyframes vrSpin {
  0% {
    transform: rotateY(0deg);
  }
  50% {
    transform: rotateY(180deg);
  }
  100% {
    transform: rotateY(360deg);
  }
}

/* Documentation Icon Special Effect */
.docs-icon-container:hover .fa-book {
  animation: bookOpen 0.6s ease;
}

@keyframes bookOpen {
  0% {
    transform: rotateY(0deg) scale(1);
  }
  50% {
    transform: rotateY(90deg) scale(1.1);
  }
  100% {
    transform: rotateY(0deg) scale(1);
  }
}

/* Ripple Effect */
.ripple {
  position: absolute;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 70%);
  transform: scale(0);
  animation: ripple 0.6s linear;
  pointer-events: none;
}

@keyframes ripple {
  to {
    transform: scale(4);
    opacity: 0;
  }
}

/* Icon Animations */
@keyframes iconWobble {
  0%, 100% {
    transform: scale(1);
  }
  25% {
    transform: scale(1.1) rotate(5deg);
  }
  50% {
    transform: scale(1.05) rotate(-5deg);
  }
  75% {
    transform: scale(1.1) rotate(2deg);
  }
}

/* Section Title Animation */
.section-title:hover .section-underline {
  width: 100%;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
}

/* HR Animation */
.animated-hr {
  background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.5), transparent);
  height: 2px;
}

/* Sidebar Glow Effect */
.interactive-sidebar:hover .sidebar-glow {
  opacity: 0.1;
}

/* Active State Enhancements */
.nav-link.active {
  box-shadow: 0 10px 30px -10px rgba(139, 92, 246, 0.3);
  border: 1px solid rgba(139, 92, 246, 0.2);
}

/* User Profile Dropdown */
.user-dropdown {
  min-width: 200px;
}

.user-avatar:hover {
  transform: scale(1.05);
  box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
}

/* Badge Animations */
.profile-badge, .new-badge, .help-badge {
  animation: badgePulse 2s infinite;
}

@keyframes badgePulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  }
  50% {
    box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
  }
}

.new-badge {
  animation: newBadgePulse 2s infinite;
}

@keyframes newBadgePulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(20, 184, 166, 0.7);
  }
  50% {
    box-shadow: 0 0 0 6px rgba(20, 184, 166, 0);
  }
}

.help-badge {
  animation: helpBadgePulse 2s infinite;
}

@keyframes helpBadgePulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
  }
  50% {
    box-shadow: 0 0 0 6px rgba(245, 158, 11, 0);
  }
}

/* Smooth Scroll for Navigation */
.navigation-container {
  scroll-behavior: smooth;
}

/* Custom Scrollbar */
.navigation-container::-webkit-scrollbar {
  width: 4px;
}

.navigation-container::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

.navigation-container::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-radius: 10px;
}

.navigation-container::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #2563eb, #7c3aed);
}

/* User Profile Hover Effects */
.user-profile:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}

/* Responsive Enhancements */
@media (max-width: 1200px) {
  .interactive-sidebar {
    transform: translateX(-100%);
  }
  
  .interactive-sidebar.mobile-open {
    transform: translateX(0);
    box-shadow: 0 0 50px rgba(0, 0, 0, 0.3);
  }
}

/* Loading Animation for Initial Load */
@keyframes sidebarSlideIn {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.nav-item {
  animation: sidebarSlideIn 0.6s ease-out forwards;
  opacity: 0;
}

.nav-item:nth-child(1) { animation-delay: 0.1s; }
.nav-item:nth-child(2) { animation-delay: 0.2s; }
.nav-item:nth-child(3) { animation-delay: 0.3s; }
.nav-item:nth-child(4) { animation-delay: 0.4s; }
.nav-item:nth-child(5) { animation-delay: 0.5s; }
.nav-item:nth-child(6) { animation-delay: 0.6s; }
.nav-item:nth-child(7) { animation-delay: 0.7s; }
.nav-item:nth-child(8) { animation-delay: 0.8s; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize interactive sidebar
  const sidebar = document.querySelector('.interactive-sidebar');
  const navLinks = document.querySelectorAll('.nav-link');
  const logoContainer = document.querySelector('.logo-container');
  
  // Ripple Effect
  function createRipple(event, element) {
    const rippleContainer = element.querySelector('.ripple-container');
    const ripple = document.createElement('div');
    ripple.className = 'ripple';
    
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    
    rippleContainer.appendChild(ripple);
    
    setTimeout(() => {
      ripple.remove();
    }, 600);
  }
  
  // Add click effects to nav links
  navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      createRipple(e, link);
      
      // Add active state animation
      link.classList.add('active');
      
      // Remove active state from other links
      navLinks.forEach(otherLink => {
        if (otherLink !== link) {
          otherLink.classList.remove('active');
        }
      });
    });
    
    // Hover effects
    link.addEventListener('mouseenter', () => {
      link.style.transform = 'translateX(8px)';
      
      // Show badges on hover
      const profileBadge = link.querySelector('.profile-badge');
      const newBadge = link.querySelector('.new-badge');
      const helpBadge = link.querySelector('.help-badge');
      
      if (profileBadge) profileBadge.style.opacity = '1';
      if (newBadge) newBadge.style.opacity = '1';
      if (helpBadge) helpBadge.style.opacity = '1';
    });
    
    link.addEventListener('mouseleave', () => {
      if (!link.classList.contains('active')) {
        link.style.transform = 'translateX(0)';
      }
    });
  });
  
  // Logo hover effects
  if (logoContainer) {
    logoContainer.addEventListener('mouseenter', () => {
      const particles = logoContainer.querySelector('.logo-particles');
      if (particles) {
        particles.style.opacity = '1';
      }
    });
    
    logoContainer.addEventListener('mouseleave', () => {
      const particles = logoContainer.querySelector('.logo-particles');
      if (particles) {
        particles.style.opacity = '0';
      }
    });
  }
  
  // Section title hover effects
  const sectionTitles = document.querySelectorAll('.section-title');
  sectionTitles.forEach(title => {
    title.addEventListener('mouseenter', () => {
      const underline = title.querySelector('.section-underline');
      if (underline) {
        underline.style.width = '100%';
      }
    });
    
    title.addEventListener('mouseleave', () => {
      const underline = title.querySelector('.section-underline');
      if (underline) {
        underline.style.width = '32px';
      }
    });
  });
  
  // User profile interaction
  const userProfile = document.querySelector('.user-profile');
  if (userProfile) {
    userProfile.addEventListener('click', () => {
      userProfile.style.transform = 'translateY(-2px) scale(1.02)';
      setTimeout(() => {
        userProfile.style.transform = 'translateY(-2px) scale(1)';
      }, 150);
    });
  }
  
  // Keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      // Close sidebar on escape (for mobile)
      sidebar.classList.remove('mobile-open');
    }
  });
  
  // Add loading animation
  setTimeout(() => {
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach((item, index) => {
      item.style.animationDelay = `${(index + 1) * 0.1}s`;
    });
  }, 100);
  
  // Special effects for profile menu
  const profileLink = document.querySelector('a[href*="user-profile"]');
  if (profileLink) {
    profileLink.addEventListener('mouseenter', () => {
      const badge = profileLink.querySelector('.profile-badge');
      if (badge) {
        badge.style.transform = 'scale(1) rotate(360deg)';
      }
    });
  }
  
  // Special effects for VR menu
  const vrLink = document.querySelector('a[href*="virtual-reality"]');
  if (vrLink) {
    vrLink.addEventListener('mouseenter', () => {
      const badge = vrLink.querySelector('.new-badge');
      if (badge) {
        badge.style.transform = 'scale(1)';
      }
    });
  }
  
  // Special effects for documentation menu
  const docsLink = document.querySelector('a[href*="documentation"]');
  if (docsLink) {
    docsLink.addEventListener('mouseenter', () => {
      const badge = docsLink.querySelector('.help-badge');
      if (badge) {
        badge.style.transform = 'scale(1)';
      }
    });
  }
});
</script>