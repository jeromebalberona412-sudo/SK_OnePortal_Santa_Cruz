<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Authentication\Providers\AuthenticationServiceProvider::class,
    App\Modules\Dashboard\Providers\DashboardServiceProvider::class,
    App\Modules\Profile\Providers\ProfileServiceProvider::class,
    App\Modules\Homepage\Providers\HomepageServiceProvider::class,
    App\Modules\Kabataan_Portal\Providers\KabataanPortalServiceProvider::class,
    App\Modules\Chatbot\Providers\ChatbotServiceProvider::class,
    App\Modules\KKProfiling\Providers\KKProfilingServiceProvider::class,
];
