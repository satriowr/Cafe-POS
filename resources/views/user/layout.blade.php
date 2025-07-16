<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kopi Nala</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            background-color: #f8f9fa;
        }   

        body {
            display: flex;
            justify-content: center;
            align-items: center;
        }   

        .app-wrapper {
            width: 100%;
            max-width: 375px; /* default: iPhone 8 width */
            height: 100vh;
            border: 1px solid #ccc;
            display: flex;
            flex-direction: column;
            background-color: white;
            overflow: hidden;
        }
        
        @media (min-width: 768px) {
            .app-wrapper {
                max-width: 500px;
            }
        } 

        .content-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 1rem 4rem;
        }   

        .nav-bottom {
            border-top: 1px solid #ccc;
            background-color: white;
            display: flex;
            justify-content: space-around;
            padding: 0.5rem 0;
        }   

        .nav-bottom a {
            text-decoration: none;
            text-align: center;
            color: #666;
            font-size: 12px;
        }   

        .nav-bottom a.active {
            color: black;
        }   

        .nav-bottom svg {
            width: 20px;
            height: 20px;
            display: block;
            margin: 0 auto 2px;
        }
    </style>    

</head>
<body>
    <div class="app-wrapper">
        <div class="content-scroll">
            @yield('content')
        </div>

        <div class="nav-bottom">
            <!-- Menu -->
            <a href="{{ route('user.menu', ['token' => $token]) }}" class="{{ request()->routeIs('user.menu') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="{{ request()->routeIs('user.menu') ? 'black' : 'none' }}"
                     viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.5">
                    <path d="M2 3.5a.5.5 0 0 1 .5-.5H13.5a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5zm0 4a.5.5 0 0 1 .5-.5H13.5a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5zm.5 3.5a.5.5 0 0 0 0 1H13.5a.5.5 0 0 0 0-1H2.5z"/>
                </svg>
                Menu
            </a>        

            <!-- Keranjang -->
            <a href="{{ route('user.cart.show', ['token' => $token]) }}" class="{{ request()->routeIs('user.cart.show') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="{{ request()->routeIs('user.cart') ? 'black' : 'none' }}"
                     viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.5">
                    <path d="M5.929 1.757a.5.5 0 1 0-.858-.514L2.217 6H.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h.623l1.844 6.456A.75.75 0 0 0 3.69 15h8.622a.75.75 0 0 0 .722-.544L14.877 8h.623a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1.717L10.93 1.243a.5.5 0 1 0-.858.514L12.617 6H3.383zM4 10a1 1 0 0 1 2 0v2a1 1 0 1 1-2 0zm3 0a1 1 0 0 1 2 0v2a1 1 0 1 1-2 0zm4-1a1 1 0 0 1 1 1v2a1 1 0 1 1-2 0v-2a1 1 0 0 1 1-1"/>
                </svg>
                Keranjang
            </a>        

            <!-- Status -->
            <a href="{{ route('user.status', ['token' => $token]) }}" class="{{ request()->routeIs('user.status') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="{{ request()->routeIs('user.status') ? 'black' : 'none' }}"
                     viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.5">
                    <path d="M2 1.5a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H13v.586a2 2 0 0 1-.586 1.414L9.414 6l2.707 2.707A2 2 0 0 1 13 10.121V10.5h-.5a.5.5 0 0 1 0 1H13v.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1 0-1H3v-.586a2 2 0 0 1 .586-1.414L6.586 10 3.879 7.293A2 2 0 0 1 3 5.879V5.5h.5a.5.5 0 0 1 0-1H3v-.5a.5.5 0 0 1 .5-.5h10z"/>
                </svg>
                Status
            </a>
        </div>        

    </div>


</body>

</html>
