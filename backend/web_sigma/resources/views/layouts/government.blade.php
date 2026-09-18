<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>
@yield('title','SIGMA Government')
</title>


<script src="https://cdn.tailwindcss.com"></script>


<script>

tailwind.config = {

theme: {

extend: {

colors: {

sigma:{
red:'#DC2626',
orange:'#F97316',
green:'#16A34A',
dark:'#111827'
}

}

}

}

}

</script>


</head>


<body class="bg-gray-100">


<div class="flex min-h-screen">


<aside class="w-72 bg-gray-900 text-white">


<div class="p-6 border-b border-gray-700">


<h1 class="text-3xl font-bold">
🔥 SIGMA
</h1>


<p class="text-sm text-gray-400">
Pusat Komando Pemerintah
</p>


</div>



<nav class="p-5 space-y-3">


<a href="#"
class="block px-4 py-3 rounded-xl bg-red-600">

🏠 Dashboard

</a>


<a href="#"
class="block px-4 py-3 rounded-xl hover:bg-orange-500">

🗺️ Monitoring GIS

</a>



<a href="#"
class="block px-4 py-3 rounded-xl hover:bg-orange-500">

🔥 Kejadian Karhutla

</a>



<a href="#"
class="block px-4 py-3 rounded-xl hover:bg-orange-500">

⚠️ Early Warning

</a>



<a href="#"
class="block px-4 py-3 rounded-xl hover:bg-red-600">

🚒 Tim Lapangan

</a>



</nav>


</aside>




<div class="flex-1">


<header class="bg-white shadow px-8 py-5 flex justify-between">


<div>

<h2 class="text-xl font-bold">

@yield('page-title')

</h2>


<p class="text-sm text-gray-500">

Command Center Mitigasi Karhutla

</p>


</div>



<button class="bg-red-600 text-white px-5 py-2 rounded-xl">

Keluar

</button>


</header>



<main class="p-8">

@yield('content')

</main>



</div>



</div>


</body>

</html>
