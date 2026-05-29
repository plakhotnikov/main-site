<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\Admin\CategoriesController;
use App\Controllers\Admin\ChartsController;
use App\Controllers\Admin\ClientsController;
use App\Controllers\Admin\ConsultantsController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\IndustriesController;
use App\Controllers\Admin\ReportsController;
use App\Controllers\Admin\RequestsController as AdminRequestsController;
use App\Controllers\Admin\ServicesController;
use App\Controllers\Admin\SpecializationsController;
use App\Controllers\Admin\StatusesController;
use App\Controllers\Admin\UsersController;
use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\ConsultantController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;

final class Router
{
    private const ROUTES = [
        // Public
        'home'      => [HomeController::class, 'index'],
        'services'  => [HomeController::class, 'services'],
        'about'     => [HomeController::class, 'about'],
        'contacts'  => [HomeController::class, 'contacts'],

        // Auth
        'login'     => [AuthController::class, 'loginForm'],
        'login_do'  => [AuthController::class, 'login'],
        'logout'    => [AuthController::class, 'logout'],
        'register'  => [AuthController::class, 'registerForm'],
        'register_do' => [AuthController::class, 'register'],

        // Client
        'client_dashboard'        => [ClientController::class, 'dashboard'],
        'client_requests'         => [ClientController::class, 'requests'],
        'client_request_create'   => [ClientController::class, 'createForm'],
        'client_request_store'    => [ClientController::class, 'store'],
        'client_request_view'     => [ClientController::class, 'view'],
        'client_report_download'  => [ClientController::class, 'downloadReport'],

        // Consultant
        'consultant_dashboard'         => [ConsultantController::class, 'dashboard'],
        'consultant_requests'          => [ConsultantController::class, 'requests'],
        'consultant_request_view'      => [ConsultantController::class, 'view'],
        'consultant_change_status'     => [ConsultantController::class, 'changeStatus'],
        'consultant_add_consultation'  => [ConsultantController::class, 'addConsultation'],
        'consultant_create_report'     => [ConsultantController::class, 'createReportForm'],
        'consultant_save_report'       => [ConsultantController::class, 'saveReport'],
        'consultant_report_download'   => [ConsultantController::class, 'downloadReport'],

        // Admin
        'admin_dashboard'           => [DashboardController::class, 'index'],
        'admin_chart'               => [ChartsController::class, 'render'],

        'admin_users'               => [UsersController::class, 'index'],
        'admin_users_form'          => [UsersController::class, 'form'],
        'admin_users_save'          => [UsersController::class, 'save'],
        'admin_users_delete'        => [UsersController::class, 'delete'],
        'admin_users_bulk_delete'   => [UsersController::class, 'bulkDelete'],

        'admin_clients'             => [ClientsController::class, 'index'],
        'admin_clients_form'        => [ClientsController::class, 'form'],
        'admin_clients_save'        => [ClientsController::class, 'save'],
        'admin_clients_delete'      => [ClientsController::class, 'delete'],

        'admin_consultants'         => [ConsultantsController::class, 'index'],
        'admin_consultants_form'    => [ConsultantsController::class, 'form'],
        'admin_consultants_save'    => [ConsultantsController::class, 'save'],
        'admin_consultants_delete'  => [ConsultantsController::class, 'delete'],

        'admin_services'            => [ServicesController::class, 'index'],
        'admin_services_form'       => [ServicesController::class, 'form'],
        'admin_services_save'       => [ServicesController::class, 'save'],
        'admin_services_delete'     => [ServicesController::class, 'delete'],

        'admin_categories'          => [CategoriesController::class, 'index'],
        'admin_categories_form'     => [CategoriesController::class, 'form'],
        'admin_categories_save'     => [CategoriesController::class, 'save'],
        'admin_categories_delete'   => [CategoriesController::class, 'delete'],

        'admin_industries'          => [IndustriesController::class, 'index'],
        'admin_industries_form'     => [IndustriesController::class, 'form'],
        'admin_industries_save'     => [IndustriesController::class, 'save'],
        'admin_industries_delete'   => [IndustriesController::class, 'delete'],

        'admin_specializations'         => [SpecializationsController::class, 'index'],
        'admin_specializations_form'    => [SpecializationsController::class, 'form'],
        'admin_specializations_save'    => [SpecializationsController::class, 'save'],
        'admin_specializations_delete'  => [SpecializationsController::class, 'delete'],

        'admin_statuses'            => [StatusesController::class, 'index'],
        'admin_statuses_form'       => [StatusesController::class, 'form'],
        'admin_statuses_save'       => [StatusesController::class, 'save'],
        'admin_statuses_delete'     => [StatusesController::class, 'delete'],

        'admin_requests'              => [AdminRequestsController::class, 'index'],
        'admin_request_view'          => [AdminRequestsController::class, 'view'],
        'admin_request_assign'        => [AdminRequestsController::class, 'assign'],
        'admin_request_delete'        => [AdminRequestsController::class, 'delete'],
        'admin_requests_bulk_delete'  => [AdminRequestsController::class, 'bulkDelete'],

        'admin_reports'             => [ReportsController::class, 'index'],
        'admin_report_download'     => [ReportsController::class, 'download'],
    ];

    public static function dispatch(string $page): void
    {
        $route = self::ROUTES[$page] ?? null;
        if ($route === null) {
            (new ErrorController())->notFound($page);
            return;
        }
        [$class, $method] = $route;
        $controller = new $class();
        $controller->{$method}();
    }
}
