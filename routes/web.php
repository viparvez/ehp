<?php


use App\Custom\Custom;
use App\Client;
use App\Apartment;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return redirect('/home');
});

Auth::routes();

Route::group(['middleware' => ['auth']], function () {
    
    Route::get('/404', 'HomeController@_404')->name('404');
    Route::get('/500', 'HomeController@_500')->name('500');
    
    Route::get('/home', 'HomeController@index')->name('home');

    Route::get('/logout', 'HomeController@sess_out')->name('sess_out');

	Route::resources([
	    'vendors' => 'VendorController',
	    'facilities' => 'FacilitiesController',
	    'floors' => 'FloorController',
	    'apartments' => 'ApartmentController',
	    'preconditions' => 'PreconditionController',
	    'clients' => 'ClientController',
	    'admissions' => 'AdmissionController',
	    'deficiencyconcerns' => 'DefconController',
	    'deficiencycategories' => 'DefcatController',
	    'deficiencydetails' => 'DefdetailController',
	    'inspections' => 'InspectionController',
	    'users' => 'UserController',
	    'roles' => 'RoleController',
	    'permissions' => 'PermissionController',
	    'attendances' => 'AttendanceController',
	    'billings' => 'BillingController',
	    'events' => 'EventController',
	    'profile' => 'ProfileController',
	]);

	Route::get('/reports', 'ReportsController@index')->name('reports.index');

	Route::post('/profile/{id}/update/password', 'ProfileController@changePass')->name('profile.changePass');
	Route::post('/profile/{id}/update/image', 'ProfileController@updateImage')->name('profile.image-update');

	Route::get('/attendances/{date}/search', 'AttendanceController@showByDate')->name('attendances.showByDate');
	Route::POST('/attendances/search', 'AttendanceController@postByDate')->name('attendances.postByDate');

	Route::get('/vendors/{id}/facility', 'FacilitiesController@vendorFacilities')->name('vendorFacilities');

	Route::POST('/admissions/transfer', 'AdmissionController@transfer')->name('admissions.transfer');
	
	Route::get('/vendors/{id}/activation', 'VendorController@getVendorActivation')->name('getVendorActivation');
	Route::put('/vendors/{id}/activation', 'VendorController@postVendorActivation')->name('postVendorActivation');

	Route::get('/facilities/{id}/activation', 'FacilitiesController@getFacilityActivation')->name('getFacilityActivation');
	Route::put('/facilities/{id}/activation', 'FacilitiesController@postFacilityActivation')->name('postFacilityActivation');

	Route::get('/floors/{id}/activation', 'FloorController@getFloorActivation')->name('getFloorActivation');
	Route::put('/floors/{id}/activation', 'FloorController@postFloorActivation')->name('postFloorActivation');


	Route::get('/apartments/{id}/activation', 'ApartmentController@getAptActivation')->name('getAptActivation');
	Route::put('/apartments/{id}/activation', 'ApartmentController@postAptActivation')->name('postAptActivation');


	Route::get('/preconditions/{id}/activation', 'PreconditionController@getPrecActivation')->name('getPrecActivation');
	Route::put('/preconditions/{id}/activation', 'PreconditionController@postPrecActivation')->name('postPrecActivation');


	Route::get('/deficiencyconcerns/{id}/activation', 'DefconController@getDefconActivation')->name('getDefconActivation');
	Route::put('/deficiencyconcerns/{id}/activation', 'DefconController@postDefconActivation')->name('postDefconActivation');


	Route::get('/deficiencycategories/{id}/activation', 'DefcatController@getDefcatActivation')->name('getDefcatActivation');
	Route::put('/deficiencycategories/{id}/activation', 'DefcatController@postDefcatActivation')->name('postDefcatActivation');


	Route::get('/deficiencydetails/{id}/activation', 'DefdetailController@getDefdetailActivation')->name('getDefdetailActivation');
	Route::put('/deficiencydetails/{id}/activation', 'DefdetailController@postDefdetailActivation')->name('postDefdetailActivation');


	Route::get('/vendors/{id}/deletion', 'VendorController@getDeletion')->name('getVendorDeletion');
	Route::put('/vendors/{id}/deletion', 'VendorController@postDeletion')->name('postVendorDeletion');

	Route::get('/facilities/{id}/deletion', 'FacilitiesController@getDeletion')->name('getFacilityDeletion');
	Route::put('/facilities/{id}/deletion', 'FacilitiesController@postDeletion')->name('postFacilityDeletion');

	Route::get('/floors/{id}/deletion', 'FloorController@getDeletion')->name('getFloorDeletion');
	Route::put('/floors/{id}/deletion', 'FloorController@postDeletion')->name('postFloorDeletion');

	Route::get('/apartments/{id}/deletion', 'ApartmentController@getDeletion')->name('getApartmentDeletion');
	Route::put('/apartments/{id}/deletion', 'ApartmentController@postDeletion')->name('postApartmentDeletion');


	Route::get('/preconditions/{id}/deletion', 'PreconditionController@getDeletion')->name('getPreconditionDeletion');
	Route::put('/preconditions/{id}/deletion', 'PreconditionController@postDeletion')->name('postPreconditionDeletion');


	Route::get('/clients/{id}/deletion', 'ClientController@getDeletion')->name('getClientnDeletion');
	Route::put('/clients/{id}/deletion', 'ClientController@postDeletion')->name('postClientnDeletion');


	Route::get('/inspections/{id}/deletion', 'InspectionController@getDeletion')->name('getInspectionDeletion');
	Route::put('/inspections/{id}/deletion', 'InspectionController@postDeletion')->name('postInspectionDeletion');

	Route::get('/admissions/{id}/deletion', 'AdmissionController@getDeletion')->name('getAdmissionDeletion');
	Route::put('/admissions/{id}/deletion', 'AdmissionController@postDeletion')->name('postAdmissionDeletion');

	Route::get('/deficiencycategories/{id}/deletion', 'DefcatController@getDeletion')->name('getDefcatDeletion');
	Route::put('/deficiencycategories/{id}/deletion', 'DefcatController@postDeletion')->name('postDefcatDeletion');

	Route::get('/deficiencyconcerns/{id}/deletion', 'DefconController@getDeletion')->name('getDefconDeletion');
	Route::put('/deficiencyconcerns/{id}/deletion', 'DefconController@postDeletion')->name('postDefconDeletion');


	Route::get('/deficiencydetails/{id}/deletion', 'DefdetailController@getDeletion')->name('getDefdetDeletion');
	Route::put('/deficiencydetails/{id}/deletion', 'DefdetailController@postDeletion')->name('postDefdetDeletion');


	Route::get('/billing/{id}/delete', 'BillingController@getBillDeletion')->name('getBillDeletion');


	Route::get('/users/{id}/activation', 'UserController@getUserActivation')->name('getUserActivation');
	Route::put('/users/{id}/activation', 'UserController@postUserActivation')->name('postUserActivation');


	Route::get('/users/{id}/changepass', 'UserController@get_password_change')->name('getPassword');
	Route::put('/users/{id}/changepass', 'UserController@post_password_change')->name('postPassword');


	Route::get('/vendor/{id}/facility', 'FloorController@getVendorFacility')->name('getVendorFacility');

	Route::get('/facility/{id}/floor', 'ApartmentController@getFacilityFloor')->name('getFacilityFloor');

	Route::get('/facility/{id}/apartment/{date}', 'ApartmentController@getFacilityApartment')->name('getFacilityApartment');

	Route::get('/facility/apartment/{id}', 'ApartmentController@getApartment')->name('getApartment');

	Route::get('/facility/{id}/apartment', 'ApartmentController@facilityApts')->name('facilityApt');

	Route::get('/get/statelist','StateController@statelist')->name('statelist');

	Route::post('/client/ajaxsearch','ClientController@searchClient')->name('searchClient');

	Route::put('/client/{id}/status/update','ClientController@updateClientStatus')->name('clients.updateStatus');

	Route::get('/admissions/discharge/{id}','AdmissionController@dischargeForm')->name('admissions.getdischarge');

	Route::post('/admissions/discharge','AdmissionController@discharge')->name('admissions.putdischarge');

	Route::get('/inspections/formfield/{id}/{arg}','InspectionController@formfield')->name('inspections.formfield');

	Route::get('/inspections/defdetails/{id}','InspectionController@defdetails')->name('inspections.defdetails');

	Route::get('/inspections/getconcern/{id}','InspectionController@getconcern')->name('inspections.getconcern');

	Route::get('/inspections/details/{id}','InspectionController@getDetails')->name('inspections.details');

	Route::get('/inspections/print/{id}','InspectionController@printLayout')->name('inspections.print');

	Route::POST('/inspections/followup/add','InspectionController@addFollowUpIns')->name('inspections.addFollowUpIns');

	Route::get('/inspections/new/form','InspectionController@newForm')->name('inspections.newForm');

	Route::POST('/attendances/form','AttendanceController@processForm')->name('attendances.processForm');
	Route::get('/attendances/{facility}/{date}','AttendanceController@showForm')->name('attendances.showForm');


	Route::get('/client/{id}/history', 'ClientController@getClientHistory')->name('clients.history');
	Route::get('/client/{id}/history/admissions', 'ClientController@getClientAdmHistory')->name('clients.admhistory');
	Route::get('/client/{id}/history/transfer', 'ClientController@getClientXferHistory')->name('clients.xferhistory');
	Route::get('/client/{id}/history/precondition', 'ClientController@getClientPrecHistory')->name('clients.prechistory');

	Route::post('/reports', 'ReportsController@export')->name('report.export');
	Route::get('/test', 'ReportsController@test')->name('console.test');

	Route::get('refresh-csrf', function(){
	    return csrf_token();
	});
	
});


Route::group(['prefix' => 'scheduler'], function () {
    
   	Route::get('/inspections/incomplete/drop','ScheduledTaskController@capAlert')->name('dropIncompleteInspections');

});


Route::get('/ajax', 'ClientController@getClientHistory2')->name('clients.history2');