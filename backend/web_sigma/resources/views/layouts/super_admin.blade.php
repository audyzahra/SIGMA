<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>
        @yield('title', 'SIGMA Super Admin')
    </title>


    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>


    <script>

        tailwind.config = {

            theme: {

                extend: {

                    colors: {

                        sigma: {

                            red: '#DC2626',

                            orange: '#F97316',

                            green: '#16A34A',

                            dark: '#111827'

                        }

                    }

                }

            }

        }

    </script>

</head>


<body class="bg-gray-100">


<div class="flex min-h-screen">


    <!-- SIDEBAR -->

    <aside class="w-72 bg-sigma-dark text-white">


        <div class="p-6 border-b border-gray-700">


            <div class="flex items-center gap-3">


                <div class="bg-red-600 w-12 h-12 rounded-xl flex items-center justify-center text-2xl">

                    🔥

                </div>


                <div>

                    <h1 class="text-2xl font-bold">
                        SIGMA
                    </h1>


                    <p class="text-xs text-gray-400">
                        Pusat Komando Super Admin
                    </p>

                </div>


            </div>


        </div>



        <nav class="p-5 space-y-3">


            <a href="#"
            class="block px-4 py-3 rounded-xl bg-red-600">

                🏠 Dashboard

            </a>



            <a href="#"
            class="block px-4 py-3 rounded-xl hover:bg-red-600">

                👥 Manajemen User

            </a>



            <a href="#"
            class="block px-4 py-3 rounded-xl hover:bg-orange-500">

                🔐 Role Permission

            </a>



            <a href="#"
            class="block px-4 py-3 rounded-xl hover:bg-orange-500">

                🗺️ Data Wilayah

            </a>



            <a href="#"
            class="block px-4 py-3 rounded-xl hover:bg-orange-500">

                🤖 AI Configuration

            </a>



            <a href="#"
            class="block px-4 py-3 rounded-xl hover:bg-red-600">

                ⚙️ Pengaturan

            </a>



        </nav>


    </aside>




    <!-- CONTENT -->

    <div class="flex-1">


        <header class="bg-white shadow px-8 py-5 flex justify-between items-center">


            <div>

                <h2 class="text-xl font-bold">

                    @yield('page-title')

                </h2>


                <p class="text-gray-500 text-sm">

                    Sistem Intelijen Geospasial Mitigasi Karhutla

                </p>

            </div>




            <div class="flex items-center gap-5">


                <div class="text-right">

                    <p class="font-semibold">
                        Super Admin
                    </p>


                    <p class="text-green-600 text-sm">
                        ● Online
                    </p>


                </div>


                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-xl">
                        Keluar
                    </button>
                </form>


            </div>


        </header>



        <main class="p-8">

            @yield('content')

        </main>



    </div>



</div>


</body>

</html>
