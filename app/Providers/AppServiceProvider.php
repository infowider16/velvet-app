<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Services\AuthServiceInterface;
use App\Services\Admin\AuthServices;
use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Repositories\Eloquent\AdminRepository;
use App\Contracts\Services\AdminUserServiceInterface;
use App\Services\Admin\UserServices;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Contracts\Services\SocialLoginServiceInterface;
use App\Services\SocialLoginService;
use App\Contracts\Repositories\ContentRepositoryInterface;
use App\Repositories\Eloquent\ContentRepository;
use App\Contracts\Repositories\FaqRepositoryInterface;
use App\Repositories\Eloquent\FaqRepository;
use App\Contracts\Repositories\ContactUsRepositoryInterface;
use App\Repositories\Eloquent\ContactUsRepository;
use App\Contracts\Services\ContactUsServiceInterface;
use App\Services\ContactUsService;
use App\Contracts\Services\AdminContactUsServiceInterface;
use App\Services\Admin\ContactUsService as AdminContactUsService;
use App\Contracts\Services\AdminFaqServiceInterface;
use App\Services\Admin\FaqService;
use App\Contracts\Services\AdminContentServiceInterface;
use App\Services\Admin\ContentService;
use App\Contracts\Repositories\InterestRepositoryInterface;
use App\Repositories\Eloquent\InterestRepository;
use App\Contracts\Services\InterestServiceInterface;
use App\Services\Admin\InterestService;
use App\Contracts\Repositories\PlanRepositoryInterface;
use App\Repositories\Eloquent\PlanRepository;
use App\Contracts\Services\PlanServiceInterface;
use App\Services\Admin\PlanService;
use App\Contracts\Repositories\BoostRepositoryInterface;
use App\Repositories\Eloquent\BoostRepository;
use App\Contracts\Repositories\PinRepositoryInterface;
use App\Repositories\Eloquent\PinRepository;
use App\Contracts\Repositories\FriendshipRepositoryInterface;
use App\Repositories\Eloquent\FriendshipRepository;
use App\Contracts\Repositories\MessageRepositoryInterface;
use App\Repositories\Eloquent\MessageRepository;
use App\Contracts\Repositories\GroupRepositoryInterface;
use App\Repositories\Eloquent\GroupRepository;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind service interfaces
        $this->app->bind(AuthServiceInterface::class, AuthServices::class);
        $this->app->bind(AdminUserServiceInterface::class, UserServices::class);
        $this->app->bind(SocialLoginServiceInterface::class, SocialLoginService::class);
        $this->app->bind(ContactUsServiceInterface::class, ContactUsService::class);
        $this->app->bind(AdminContactUsServiceInterface::class, AdminContactUsService::class);
        $this->app->bind(AdminFaqServiceInterface::class, FaqService::class);
        $this->app->bind(AdminContentServiceInterface::class, ContentService::class);
        $this->app->bind(InterestServiceInterface::class, InterestService::class);
        $this->app->bind(PlanServiceInterface::class, PlanService::class);
        
        
        // Bind repository interfaces
        $this->app->bind(AdminRepositoryInterface::class, AdminRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ContentRepositoryInterface::class, ContentRepository::class);
        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
        $this->app->bind(ContactUsRepositoryInterface::class, ContactUsRepository::class);
        $this->app->bind(InterestRepositoryInterface::class, InterestRepository::class);
        $this->app->bind(PlanRepositoryInterface::class, PlanRepository::class);
        $this->app->bind(BoostRepositoryInterface::class, BoostRepository::class);
        $this->app->bind(PinRepositoryInterface::class, PinRepository::class);
        $this->app->bind(FriendshipRepositoryInterface::class, FriendshipRepository::class);
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
        $this->app->bind(GroupRepositoryInterface::class, GroupRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
