<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Plantilla Base</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  @yield('css')
  <style>
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .wrapper {
      display: flex;
      flex: 1;
      overflow-y: hidden;   /* solo oculta el scroll vertical */
      
    }
    .sidebar {
      min-width: 250px;
      max-width: 250px;
      background-color: #f8f9fa;
      border-right: 1px solid #dee2e6;
      transition: all 0.3s;
      overflow-y: auto;
    }
    .sidebar .nav-link {
      font-weight: 500;
      color: #333;
    }
    .sidebar .nav-link:hover {
      background-color: #e9ecef;
      border-radius: 8px;
    }
    .content {
      flex-grow: 1;
      padding: 20px;
       overflow-x: auto;  
    }
    .table-container {
      flex-grow: 1;
      padding: 20px;
       overflow-x: auto;  
    }
    @media (max-width: 768px) {
      .sidebar {
        position: absolute;
        left: -250px;
        top: 56px;
        height: calc(100% - 56px);
        z-index: 1000;
      }
      .sidebar.show {
        left: 0;
      }
    }
  </style>
</head>
<body>

  <!-- Navbar superior -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
    <div class="container-fluid">
      <button class="btn btn-outline-secondary d-md-none" id="toggleSidebar">☰</button>
      <a class="navbar-brand ms-2" href="#">🌐 ICI</a>
      <div class="d-flex ms-auto align-items-center">
        {{-- <span class="me-3">👤 Bienvenido, {{ Auth::user()->name }}</span> --}}
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="btn btn-light btn-sm">🚪Cerrar sesión</button>
        </form>
      </div>
    </div>
  </nav>

  <div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar bg-light" id="sidebarMenu">
      <nav class="nav flex-column p-3">
        @auth  
            @if(auth()->user()->is_admin)
                <!-- Menú 1 -->
                <a class="nav-link d-flex justify-content-between align-items-center" 
                data-bs-toggle="collapse" href="#menu1" role="button" aria-expanded="false" aria-controls="menu1">
                ⚙️ Opciones de Hotel <span>▾</span>
                </a>
                <div class="collapse ps-3" id="menu1">

                    <a href="#" class="nav-link">🗸 Opcion Menu1</a>
                    <a href="#" class="nav-link">🗸 Opcion Menu1</a>
                </div>

                <!-- Menú 2 -->
                <a class="nav-link d-flex justify-content-between align-items-center" 
                data-bs-toggle="collapse" href="#menu2" role="button" aria-expanded="false" aria-controls="menu2">
                ⚙️ Configurar <span>▾</span>
                </a>
                <!-- En el sidebar -->
                <div class="collapse ps-3" id="menu1">
                    <a href="{{ route('habitaciones.index') }}" class="nav-link">🗸 Gestionar Habitaciones</a>
                </div>

            @endif
        @endauth
      </nav>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                    {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-warning alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

      {{-- @include('partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs]) --}}

      @yield('content')
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>    
  @yield('scripts')

  <script>
    const toggleSidebar = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebarMenu');
    
    toggleSidebar.addEventListener('click', () => {
      sidebar.classList.toggle('show');
    });
  </script>


</body>
</html>
