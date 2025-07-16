<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kopi Nala</title>
</head>
<body>

<h2>Menu untuk Meja {{ $table_number }}</h2>

@foreach($menus as $menu)
    @if($menu->is_available)
        <!-- tampilkan menu -->
    @endif
@endforeach

</body>
</html>