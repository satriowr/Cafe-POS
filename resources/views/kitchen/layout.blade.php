<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Kitchen Nala</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  @stack('styles')
  <style>
    body {
      overflow-x: hidden;
    }
    .sidebar {
      height: 100vh;
      width: 245px;
      position: fixed;
      top: 0;
      left: 0;
      background-color: #0E8636;
      padding-top: 1rem;
      box-shadow: 2px 0 10px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
    }
    .sidebar.hidden {
      transform: translateX(-100%);
    }
    .content {
      margin-left: 245px;
      padding: 2rem;
      transition: margin-left 0.3s ease;
    }
    .content.expanded {
      margin-left: 0;
    }
    .nav-link {
      color: #fff !important;
    }
    .nav-link.active {
      color: #fff !important;
      font-weight: bold;
    }
    .sidebar .nav-link {
      color: #333;
      margin: 0.5rem 0;
    }
    .navbar-niku {
      background-color: #fff;
      padding: 1rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      position: sticky;
      top: 0;
      z-index: 10000;
    }
    .navbar-niku h4 {
      margin: 0;
      cursor: pointer;
      user-select: none;
    }
    .rotate {
  display: inline-block;
  transition: transform 0.4s ease;
}

.rotate-anim {
  transform: rotate(180deg);
}
  </style>
</head>
<body>

  <!-- Navbar with toggle -->
  <div class="navbar-niku d-flex justify-content-between align-items-center">
  <img src="{{ asset('images/logo_nala.png') }}" width='55' height='40' alt="">

    <span id="datetime" class="text-muted small"></span>
  </div>

  <!-- Sidebar -->
  <div class="sidebar d-flex flex-column px-3" id="sidebar">
    <br><br>
    <nav class="nav flex-column mt-4">
        <a class="nav-link {{ Request::is('kitchen') ? 'active' : '' }}" href="/kitchen">
            <i class="fas fa-utensils {{ Request::is('kitchen') ? '' : 'text-muted' }}"></i> &nbsp Pesanan Pelanggan
        </a>
        <a class="nav-link {{ Request::is('logout') ? 'active' : '' }}" href="/logout">
            <i class="fas fa-sign-out-alt {{ Request::is('logout') ? '' : 'text-muted' }}"></i> &nbsp Keluar
        </a>
    </nav>
  </div>

  <!-- Main content -->
  <div class="content" id="mainContent">
    @yield('content')
  </div>

  <script>
  const sidebar = document.getElementById('sidebar');
  const content = document.getElementById('mainContent');
  const toggleTrigger = document.getElementById('sidebarToggle');

  toggleTrigger.addEventListener('click', () => {
    sidebar.classList.toggle('hidden');
    content.classList.toggle('expanded');

    if (sidebar.classList.contains('hidden')) {
      toggleTrigger.innerHTML = '&#9776;'; // ☰
      toggleTrigger.classList.add('rotate-anim');
    } else {
      toggleTrigger.innerText = 'Nala Kitchen';
      toggleTrigger.classList.remove('rotate-anim');
    }
  });
</script>

<script>
  function updateDateTime() {
    const now = new Date();
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    const dayName = days[now.getDay()];
    const date = now.getDate().toString().padStart(2, '0');
    const month = (now.getMonth() + 1).toString().padStart(2, '0');
    const year = now.getFullYear();

    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const seconds = now.getSeconds().toString().padStart(2, '0');

    const formatted = `${dayName}, ${date}-${month}-${year} | ${hours}:${minutes}:${seconds} WIB`;

    document.getElementById('datetime').textContent = formatted;
  }

  setInterval(updateDateTime, 1000);
  updateDateTime();
</script>

</body>
</html>
