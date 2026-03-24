<?php
use Illuminate\Support\Facades\Route;

Route::get('/refresh-csrf', function () {

    return response()->json(['token' => csrf_token()]);

})->name('refresh.csrf');

Route::prefix('admin')->name('admin.')->namespace('App\Http\Controllers\Admin')->group(function () {

    Route::middleware('admin.guest')->group(function () {

        Route::get('/', 'AuthController@index')->name('login');

        Route::get('login', 'AuthController@index')->name('login.form'); 

        Route::post('login', 'AuthController@login')->name('adminlogin');

        Route::get('forgot-password', 'AuthController@forgotpassword')->name('forgotpassword');

        Route::post('send-forgot-password-email', 'AuthController@sendForgotPasswordEmail')->name('sendForgotPasswordEmail');

    });



    // Add authenticated admin routes

    Route::middleware('admin.auth')->group(function () {

        Route::get('dashboard', 'DashboardController@index')->name('dashboard');

        Route::get('profile', 'AuthController@profile')->name('profile');

        Route::post('logout', 'AuthController@logout')->name('logout');

        Route::get('change-password', 'AuthController@changepassword')->name('changepassword');

        Route::post('update-password', 'AuthController@updatePassword')->name('updatePassword');

        Route::post('update-profile', 'AuthController@update')->name('update');

        Route::get('user-details', 'AuthController@getUserDetails')->name('getUserDetails');

        Route::get('users', 'UserController@index')->name('users.index');

        Route::get('users/list', 'UserController@userList')->name('user-list');

        Route::post('users/toggle-status', 'UserController@toggleStatus')->name('user.toggleStatus');

        Route::put('users/{id}/phone', 'UserController@updatePhone')->name('user.updatePhone');

        Route::delete('users/{id}', 'UserController@destroy')->name('user.destroy');

        Route::get('users/{id}', 'UserController@show')->name('user.show');

        

        // Contact management routes

        Route::get('contacts', 'ContactUsController@index')->name('contact.index');

        Route::get('contacts/list', 'ContactUsController@getContactList')->name('contact-list');

        

        // FAQ management routes

        Route::get('faqs', 'FaqController@index')->name('faq.index');

        Route::get('faqs/list', 'FaqController@getFaqList')->name('faq-list');

        Route::get('faqs/create', 'FaqController@create')->name('faq.create');

        Route::post('faqs', 'FaqController@store')->name('faq.store');

        Route::get('faqs/{id}/edit', 'FaqController@edit')->name('faq.edit');

        Route::put('faqs/{id}', 'FaqController@update')->name('faq.update');

        Route::delete('faqs/{id}', 'FaqController@destroy')->name('faq.destroy');

        

        // Content management routes

        Route::get('contents', 'ContentController@index')->name('content.index');

        Route::put('contents/{id}', 'ContentController@update')->name('content.update');

        // Interest management routes

        Route::get('interests', 'InterestController@index')->name('interest.index');

        Route::get('interests/list', 'InterestController@getInterestList')->name('interest-list');

        Route::post('interests', 'InterestController@store')->name('interest.store');

        Route::get('interests/{id}/edit', 'InterestController@edit')->name('interest.edit');

        Route::put('interests/{id}', 'InterestController@update')->name('interest.update');

        Route::delete('interests/{id}', 'InterestController@destroy')->name('interest.destroy');


        Route::get('sub-interests', 'InterestController@subIndex')->name('sub-interest.index');

        Route::get('sub-interests/list', 'InterestController@getSubInterestList')->name('sub-interest-list');

        Route::post('sub-interests', 'InterestController@subStore')->name('sub-interest.store');

        Route::get('sub-interests/{id}/edit', 'InterestController@subEdit')->name('sub-interest.edit');

        Route::put('sub-interests/{id}', 'InterestController@subUpdate')->name('sub-interest.update');

        Route::delete('sub-interests/{id}', 'InterestController@subDestroy')->name('sub-interest.destroy');

        Route::get('interests/parents', 'InterestController@getParentInterests')->name('interest.parents');


        // Plan (GhostManagement) CRUD routes
        Route::get('ghosts', 'PlanController@index')->name('ghost.index');
        Route::post('ghosts', 'PlanController@store')->name('ghost.store');
        Route::get('ghosts/{id}/edit', 'PlanController@edit')->name('ghost.edit');
        Route::put('ghosts/{id}', 'PlanController@update')->name('ghost.update');
        Route::delete('ghosts/{id}', 'PlanController@destroy')->name('ghost.destroy');

        // Boost Management CRUD routes
        Route::get('boosts', 'PlanController@boostIndex')->name('boost.index');
        Route::post('boosts', 'PlanController@boostStore')->name('boost.store');
        Route::get('boosts/{id}/edit', 'PlanController@boostEdit')->name('boost.edit');
        Route::put('boosts/{id}', 'PlanController@boostUpdate')->name('boost.update');
        Route::delete('boosts/{id}', 'PlanController@boostDestroy')->name('boost.destroy');


        // Pin Management CRUD routes
        Route::get('pins', 'PlanController@pinIndex')->name('pin.index');
        Route::post('pins', 'PlanController@pinStore')->name('pin.store');
        Route::get('pins/{id}/edit', 'PlanController@pinEdit')->name('pin.edit');
        Route::put('pins/{id}', 'PlanController@pinUpdate')->name('pin.update');
        Route::delete('pins/{id}', 'PlanController@pinDestroy')->name('pin.destroy');

        // Transaction List routes
        Route::get('transaction/ghost', 'TransactionController@ghostTransactions')->name('transaction.ghost');
        Route::get('transaction/ghost/list', 'TransactionController@ghostTransactionList')->name('transaction-ghost-list');
        
        Route::get('transaction/boost', 'TransactionController@boostTransactions')->name('transaction.boost');
        Route::get('transaction/boost/list', 'TransactionController@boostTransactionList')->name('transaction-boost-list');
        
        Route::get('transaction/pin', 'TransactionController@pinTransactions')->name('transaction.pin');
        Route::get('transaction/pin/list', 'TransactionController@pinTransactionList')->name('transaction-pin-list');
    });

});