<?php

use App\Http\Controllers\Administrator\{
    DashboardController,
    SubscriptionController,
    MyAccountController
    
};
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::prefix('administrator')->middleware(['auth','role:1'])->group(function(){
     Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('administrator.dashboard');
     Route::get('change-password', [\App\Http\Controllers\Auth\PasswordController::class, 'editPassword'])->name('administrator.change-password');
     Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'editprofile'])->name('administrator.profile');
     Route::post('/updateprofile', [App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('update.profile');
});

Route::prefix('administrator')->middleware(['auth', 'role:1'])->group(function () {
    /* Subscription Plans */
    // Route::get('administrator/', [SubscriptionController::class, 'index'])->name('administrator.dashboard');
    Route::get('subscription', [SubscriptionController::class, 'index'])->name('administrator.subscription');
    Route::post('subscription/update', [SubscriptionController::class, 'statusUpdate'])->name('administrator.subscription.update');
    Route::get('subscription/add', [SubscriptionController::class, 'create'])->name('administrator.subscription.add');
    Route::post('subscription/store', [SubscriptionController::class, 'store'])->name('administrator.subscription.store');
    Route::post('subscription/update', [SubscriptionController::class, 'update'])->name('administrator.subscription.update');
    Route::get('subscription/edit/{id}', [SubscriptionController::class, 'edit'])->name('administrator.subscription.edit');
    Route::post('subscription/statusUpdate', [SubscriptionController::class, 'statusUpdate'])->name('administrator.subscription.statusUpdate');
    Route::post('subscription/destroy', [SubscriptionController::class, 'delete'])->name('administrator.subscription.destroy');
    Route::get('subscription/downloadsubscriptionpdf', [SubscriptionController::class, 'downloadsubscriptionpdf'])->name('administrator.downloadsubscriptionpdf');

     /* My Account */
     Route::get('account', [MyAccountController::class, 'index'])->name('administrator.accounts');
     Route::get('account/add', [MyAccountController::class, 'create'])->name('administrator.account.add');
     Route::post('account/store', [MyAccountController::class, 'store'])->name('administrator.account.store');
     Route::get('account/edit/{id}', [MyAccountController::class, 'edit'])->name('administrator.account.edit');
     Route::post('account/statusUpdate', [MyAccountController::class, 'statusUpdate'])->name('administrator.account.statusUpdate');
     Route::post('account/destroy', [MyAccountController::class, 'delete'])->name('administrator.account.destroy');
     Route::post('account/update', [MyAccountController::class, 'update'])->name('administrator.account.update');
     Route::get('account/downloadHotelpdf', [MyAccountController::class, 'downloadaccountpdf'])->name('administrator.downloadaccountpdf');
     Route::get('account/subscribe/{account}', [MyAccountController::class, 'subscribe'])->name('administrator.subscribe');
     Route::post('account/storesubscribe', [MyAccountController::class, 'storesubscribe'])->name('administrator.store.subscribe');
     Route::post('account/subscriptionPrice', [MyAccountController::class, 'getsubscriptionprice'])->name('administrator.getsubscriptionprice');
     Route::post('account/accountsubscriptionpaymentdetails', [MyAccountController::class, 'accountsubscriptionpaymentdetails'])->name('administrator.accountsubscriptionpaymentdetails');
     Route::post('account/updatepassword', [MyAccountController::class, 'updatepassword'])->name('administrator.user.updatepassword');
     
});
