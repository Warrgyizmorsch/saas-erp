<!DOCTYPE html>
<html>

<head>

    <title>ERP SaaS</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body>

    <div style="display:flex; min-height:100vh;">

        <!-- SIDEBAR -->

        <div style="
        width:250px;
        background:#111827;
        color:white;
        padding:20px;
    ">

            <h2>ERP SaaS</h2>

            <hr>

            <ul style="list-style:none; padding:0;">

                <li style="margin-bottom:10px;">
                    <a href="/dashboard" style="color:white;">
                        Dashboard
                    </a>
                </li>

                @php
$modules = \App\Models\TenantModule::where(
    'tenant_id',
    tenant()->id
)->where('enabled', true)->get();
                @endphp

                @foreach($modules as $module)

                    @php

                        $menu = include(
                            base_path(
                                'Modules/' .
                                $module->module .
                                '/config/menu.php'
                            )
                        );

                    @endphp

                    <div style="margin-bottom:20px;">

                        <h3>
                            {{ $menu['icon'] }}
                            {{ $menu['name'] }}
                        </h3>

                        <ul style="
                                list-style:none;
                                padding-left:15px;
                            ">

                            @foreach($menu['items'] as $item)

                                <li style="margin-bottom:8px;">

                                    <a href="{{ $item['route'] }}" style="color:white;">
                                        {{ $item['title'] }}
                                    </a>

                                </li>

                            @endforeach

                        </ul>

                    </div>

                @endforeach

            </ul>

        </div>

        <!-- CONTENT -->

        <div style="
        flex:1;
        padding:30px;
        background:#f3f4f6;
    ">

            @yield('content')

        </div>

    </div>

</body>

</html>