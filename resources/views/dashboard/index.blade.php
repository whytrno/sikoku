<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    {{ auth()->user()->role }}
    <a href="{{ route('admin-logout') }}">Logout</a>

    <h1>DASHBOARD</h1>

    {{-- NAVBAR --}}
    <a href="{{ route('dashboard-paketSoal') }}">Paket soal</a>
    <a href="{{ route('dashboard-batch') }}">Batch ujian</a>
</body>

</html>
