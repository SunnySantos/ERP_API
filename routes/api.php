<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AuthCustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\BranchSupplierInvoiceController;
use App\Http\Controllers\BranchSupplierOrderController;
use App\Http\Controllers\BranchSupplierOrderItem;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeScheduleController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfitController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SupplierItemsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/dry-items/{id}', [SupplierItemsController::class, 'showByDryCategory']);
    Route::get('/wet-items/{id}', [SupplierItemsController::class, 'showByWetCategory']);
    Route::get('/other-items/{id}', [SupplierItemsController::class, 'showByOtherCategory']);
    Route::resource('items', 'SupplierItemsController');
    Route::get('/branch-supplier-invoice-count-by-supplier-id/{id}', [BranchSupplierInvoiceController::class, 'countBySupplierId']);
    Route::get('/branch-supplier-order-count-by-supplier-id/{id}', [BranchSupplierOrderController::class, 'count']);
    Route::get('/items-count-by-category-supplier/{id}', [SupplierItemsController::class, 'countItemByCategoryAndSupplierId']);
    Route::get('/supplier-account/logout', [UserController::class, 'logoutSupplier']);
    Route::get('/super-admin-account/logout', [UserController::class, 'loginSuperAdmin']);
    Route::get('/branch-supplier-order-detail/{id}', [BranchSupplierOrderController::class, 'orderDetail']);
    Route::put('/branch-supplier-order/payment/{id}', [BranchSupplierOrderController::class, 'payment']);
    Route::get('/branch-supplier-order-pending-orders/{id}', [BranchSupplierOrderController::class, 'pendingOrders']);
    Route::get('/branch-supplier-order-processed-orders/{id}', [BranchSupplierOrderController::class, 'processedOrders']);
    Route::get('/branch-supplier-order-delivered-orders/{id}', [BranchSupplierOrderController::class, 'deliveredOrders']);
    Route::put('/branch-supplier-order/checkout/{id}', [BranchSupplierOrderController::class, 'checkout']);
    Route::resource('branch-supplier-order', 'BranchSupplierOrderController');

    Route::resource('/branch-supplier-order-items', BranchSupplierOrderItem::class);

    Route::put('/supplier-update-basic/{id}', [SupplierController::class, "updateBasic"]);
    Route::put('/supplier-update-company/{id}', [SupplierController::class, "updateCompany"]);
    Route::put('/supplier-update-username/{id}', [SupplierController::class, 'updateUsername']);
    Route::put('/supplier-update-password/{id}', [SupplierController::class, 'updatePassword']);
    // Route::get('/supplier-search/{search}', [SupplierController::class, 'search']);
    Route::get('/supplier-count', [SupplierController::class, 'count']);
    Route::get('/branch-supplier-invoice-show-by-supplier-id/{id}', [BranchSupplierInvoiceController::class, 'showBySupplierId']);
    Route::post('/branch-supplier-invoice-search/{id}', [BranchSupplierInvoiceController::class, 'search']);
    Route::get('/count-by-supplier-id/count', [BranchSupplierInvoiceController::class, 'count']);
    Route::get('/branch-supplier-invoice-overdue', [BranchSupplierInvoiceController::class, 'overdue']);
    Route::get('/branch-supplier-invoice-paid', [BranchSupplierInvoiceController::class, 'paid']);
    Route::get('/branch-supplier-invoice-unpaid', [BranchSupplierInvoiceController::class, 'unpaid']);
    Route::put('/branch-supplier-invoice-received/{id}', [BranchSupplierInvoiceController::class, 'received']);
    Route::resource('branch-supplier-invoice', 'BranchSupplierInvoiceController');
    Route::post('/supplier-import-csv', [SupplierController::class, 'importCSV']);
    Route::get('/supplier-export-csv', [SupplierController::class, 'exportCSV']);
    Route::resource('supplier', 'SupplierController');

    Route::get('/product-search/{name}', 'ProductController@search');
    Route::get('/product-count', 'ProductController@count');
    Route::post('/product', 'ProductController@store');
    Route::post('/custom-product', 'ProductController@storeCustom');
    Route::post('/update-product/{id}', 'ProductController@update');
    Route::get('/product/{id}', 'ProductController@show');
    Route::delete('/product/{id}', 'ProductController@destroy');
    Route::get('/product-dropdown', 'ProductController@dropdown');
    Route::get('/product', 'ProductController@index');
    Route::post('/product-import-csv', 'ProductController@importCSV');
    Route::get('/product-export-csv', 'ProductController@exportCSV');

    Route::get('/purchased-by-branch-id/{id}', 'BranchController@purchased');
    Route::get('/sales-by-branch-id/{id}', 'BranchController@sales');
    Route::get('/branch-dropdown', 'BranchController@dropdown');
    Route::get('/branch-count', 'BranchController@count');
    Route::post('/branch-import-csv', 'BranchController@importCSV');
    Route::get('/branch-export-csv', 'BranchController@exportCSV');
    Route::resource('branch', 'BranchController');



    Route::resource('stock-transfer', 'StockTransferController');

    Route::get('/department-dropdown', 'DepartmentController@dropdown');
    Route::get('/department/search/{search}', 'DepartmentController@search');
    Route::get('/department/count', 'DepartmentController@count');
    Route::resource('department', 'DepartmentController');

    Route::post('/career', [CareerController::class, 'store']);
    Route::put('/career/{id}', [CareerController::class, 'update']);

    Route::put('/applicant-update-pending/{id}', [ApplicantController::class, 'updatePending']);
    Route::get('/applicant-hired-count', [ApplicantController::class, 'hiredCount']);
    Route::get('/applicant-pending-count', [ApplicantController::class, 'pendingCount']);
    Route::get('/applicant-pending', [ApplicantController::class, 'pending']);
    Route::get('/applicant-hired', [ApplicantController::class, 'hired']);
    Route::delete('/applicant/{id}', [ApplicantController::class, 'destroy']);

    Route::put('/order-payment/{id}', 'OrderController@payment');
    Route::get('/order-pending-count', [OrderController::class, 'pendingCount']);
    Route::get('/order-delivered-count', [OrderController::class, 'deliveredCount']);
    Route::get('/orders-by-customer', 'OrderController@getByCustomerId');
    Route::get('/order-chart', 'OrderController@chart');
    Route::resource('order', 'OrderController');

    Route::get('/cart-count', 'CartController@count');
    Route::resource('cart', 'CartController');

    Route::post('/customer-update-avatar/{id}', [CustomerController::class, 'updateAvatar']);
    Route::put('/customer-remove-avatar/{id}', [CustomerController::class, 'removeAvatar']);
    Route::put('/customer-update-password/{id}', [CustomerController::class, 'updatePassword']);
    Route::put('/customer-update-basic-information/{id}', [CustomerController::class, 'updateBasicInformation']);
    Route::get('/customer-basic-information/{id}', [CustomerController::class, 'showBasicInformation']);
    Route::get('/customer-count', [CustomerController::class, 'count']);
    Route::get('/customer-export-csv', 'CustomerController@exportCSV');
    Route::resource('customer', 'CustomerController');


    Route::post('/chat-customer-to-employee', [ChatController::class, 'customerToEmployee']);
    Route::post('/chat-employee-to-customer', [ChatController::class, 'employeeToCustomer']);
    Route::post('/chat-employee-to-supplier', [ChatController::class, 'employeeToSupplier']);
    Route::post('/chat-supplier-to-employee', [ChatController::class, 'supplierToEmployee']);
    Route::get('/get-message-by-chat-id/{id}', [ChatController::class, 'getMessagesByChatId']);
    Route::get('/get-message-by-user-id/{id}', [ChatController::class, 'getMessagesByUserId']);
    Route::get('/get-message-by-user-id-with-pendings/{id}', [ChatController::class, 'getMessagesByUserIdAndPendings']);

    Route::get('/sum-expenses', [ExpensesController::class, 'count']);
    Route::get('/expenses-ave-by-year', [ExpensesController::class, 'getWholeYearExpenses']);
    Route::get('/expenses-ave-by-month', [ExpensesController::class, 'getExpensesByMonth']);
    Route::post('/expenses-report', [ExpensesController::class, 'report']);
    Route::get('/expenses', [ExpensesController::class, 'show']);
    Route::put('expenses/{id}', [ExpensesController::class, 'approve']);
    Route::post('/expense-import-csv', [ExpensesController::class, 'importCSV']);
    Route::get('/expense-export-csv', [ExpensesController::class, 'exportCSV']);
    Route::resource('expenses', 'ExpensesController');

    Route::get('/sales-ave-by-year', [SalesController::class, 'getWholeYearSales']);
    Route::get('/sales-ave-by-month', [SalesController::class, 'getSalesAveByMonth']);
    Route::get('/total-sales-by-month', [SalesController::class, 'totalSales']);

    Route::get('/total-revenue-by-year', [RevenueController::class, 'getWholeYearRevenue']);
    Route::get('/total-revenue-by-month', [RevenueController::class, 'getRevenueByMonth']);

    Route::get('/total-profit-by-month', [ProfitController::class, 'getProfitByMonth']);


    Route::get('/attendance-max-hours', [AttendanceController::class, 'maxHours']);
    Route::get('/attendance-min-hours', [AttendanceController::class, 'minHours']);
    Route::get('/attendance-ave-hours', [AttendanceController::class, 'aveHours']);
    Route::get('/attendance-total-hours', [AttendanceController::class, 'totalHours']);
    // Route::get('/search-attendance', [AttendanceController::class, 'search']);
    // Route::get('/attendance-by-branch', [AttendanceController::class, 'showByBranchId']);
    Route::post('/attendance-import-csv', [AttendanceController::class, 'importCSV']);
    Route::get('/attendance-export-csv', [AttendanceController::class, 'exportCSV']);
    Route::get('/present-today', 'AttendanceController@presentToday');
    Route::get('/absent-today', 'AttendanceController@absentToday');
    Route::get('/attendance-chart', 'AttendanceController@chart');
    Route::resource('attendance', 'AttendanceController');

    Route::get('/payroll-count', [PayrollController::class, 'count']);
    Route::resource('payroll', 'PayrollController');

    Route::get('/database-backup', [DatabaseBackupController::class, 'backup']);

    Route::get('/sales-report', [ReportController::class, 'sales']);
    Route::get('/expenses-report', [ReportController::class, 'expenses']);

    Route::get('/logout', [AuthCustomerController::class, 'logout']);


    Route::get('/admin-log-out', [UserController::class, 'logoutAdmin']);
    Route::get('/customer-log-out', [UserController::class, 'logoutCustomer']);

    Route::get('/create-channel', 'ChannelSubscriberController@createChannel');

    Route::post('/new-channel-message', 'ChatController@newChannelMessage');

    Route::get('/user-channels', 'ChatController@userChannels');
    Route::get('/get-messages', 'ChatController@getMessages');

    Route::post('/stock-import-csv', 'StockController@importCSV');
    Route::get('/stock-export-csv', 'StockController@exportCSV');
    Route::get('/stock-dropdown', 'StockController@dropdown');
    Route::get('/stock-count', 'StockController@count');
    Route::resource('stock', 'StockController');



    // Route::get('/employee-search/{key}', [EmployeeController::class, 'search']);
    Route::get('/employee-count', 'EmployeeController@count');
    Route::get('/employee-account', 'EmployeeController@account');
    Route::post('/employee-import-csv', 'EmployeeController@importCSV');
    Route::get('/employee-export-csv', 'EmployeeController@exportCSV');
    Route::resource('employee', 'EmployeeController');

    Route::post('/parcel-import-csv', 'ParcelController@importCSV');
    Route::get('/parcel-export-csv', 'ParcelController@exportCSV');
    Route::resource('parcel', 'ParcelController');

    Route::get('/ingredient-count', 'IngredientController@count');
    Route::get('/ingredient-search/{name}', 'IngredientController@search');
    Route::resource('ingredient', 'IngredientController');


    Route::get('/assets-by-group', 'CakeComponentController@getAssetsByGroup');
    Route::get('/assets-groups', 'CakeComponentController@getGroups');
    Route::post('/cake-components-import-csv', 'CakeComponentController@importCSV');
    Route::get('/cake-components-export-csv', 'CakeComponentController@exportCSV');
    Route::resource('cake-components', 'CakeComponentController');

    Route::delete('/remove-project-component/{id}', 'CakeProjectController@removeCakeProjectComponent');
    Route::get('/project-by-id/{id}', 'CakeProjectController@preview');
    Route::resource('project', 'CakeProjectController');
    Route::resource('project-assets', 'ProjectAssetController');

    Route::post('/deduction-import-csv', [DeductionController::class, 'importCSV']);
    Route::get('/deduction-export-csv', [DeductionController::class, 'exportCSV']);
    Route::delete('/checked-deductions/{ids}', [DeductionController::class, 'deleteChecked']);
    Route::resource('deduction', 'DeductionController');

    Route::post('/change-password', 'UserController@changePassword');

    Route::post('employee-schedule-import-csv', 'EmployeeScheduleController@importCSV');
    Route::get('employee-schedule-export-csv', 'EmployeeScheduleController@exportCSV');
    Route::resource('employee-schedule', 'EmployeeScheduleController');

    Route::get('/schedule-dropdown', [ScheduleController::class, 'dropdown']);
    Route::post('/schedule-import-csv', [ScheduleController::class, 'importCSV']);
    Route::get('/schedule-export-csv', [ScheduleController::class, 'exportCSV']);
    Route::delete('/checked-schedule/{ids}', [ScheduleController::class, 'deleteChecked']);
    Route::resource('schedule', 'ScheduleController');
});





Route::get('/cake-model', 'CakeModelController@index');
Route::get('/cake-model/{id}', 'CakeModelController@show');
Route::post('/cake-model', 'CakeModelController@store');
Route::post('/update-cake-model/{id}', 'CakeModelController@update');
Route::delete('/cake-model/{id}', 'CakeModelController@destroy');


Route::get('/most-purchased-by-branch-id/{id}', [BranchController::class, 'mostPurchased']);



Route::get('/position-dropdown', [PositionController::class, 'dropdown']);
Route::post('/position-import-csv', [PositionController::class, 'importCSV']);
Route::get('/position-export-csv', [PositionController::class, 'exportCSV']);
Route::resource('position', 'PositionController');





Route::get('/category-dropdown', [CategoryController::class, 'dropdown']);
Route::post('/category-import-csv', [CategoryController::class, 'importCSV']);
Route::get('/category-export-csv', [CategoryController::class, 'exportCSV']);
Route::resource('category', 'CategoryController');

Route::resource('overtime', 'OvertimeController');

Route::post('/carrier-import-csv', [CarrierController::class, 'importCSV']);
Route::get('/carrier-export-csv', [CarrierController::class, 'exportCSV']);
Route::resource('carrier', 'CarrierController');





Route::post('/customer-sign-up', [UserController::class, 'registerCustomer']);
Route::post('/customer-sign-in', [UserController::class, 'loginCustomer']);

Route::post('/supplier-account/login', [UserController::class, 'loginSupplier']);
Route::post('/admin-account/login', [UserController::class, 'loginAdmin']);

Route::post('/customer-verify-name', [CustomerController::class, 'verifyName']);
Route::post('/customer-verify-phone', [CustomerController::class, 'verifyPhone']);
Route::post('/customer-verify-account', [CustomerController::class, 'verifyAccount']);

Route::get('/product-list', 'ProductController@catalogue');


Route::get('/career-count', [CareerController::class, 'count']);
Route::get('/career-list/{count}', [CareerController::class, 'list']);
Route::get('/career-search/{search}', [CareerController::class, 'search']);
Route::get('/career/{id}', [CareerController::class, 'show']);
Route::get('/career', [CareerController::class, 'index']);
Route::post('/applicant', [ApplicantController::class, 'store']);



// Route::get('/order/pending/count', [CustomerOrderController::class, 'pendingCount']);
// Route::get('/order/total-purchased', [CustomerOrderController::class, 'totalPurchased']);



Route::put('/employee/update-enable/{id}', [EmployeeController::class, "updateEnable"]);
Route::put('/employee/update-job/{id}', [EmployeeController::class, "updateJob"]);
Route::put('/employee/update-basic/{id}', [EmployeeController::class, "updateBasic"]);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
