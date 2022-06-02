<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

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


Route::prefix('/')->group(function () {
    Route::controller(HomeController::class)->group(function () {
        Route::get('/', 'index')->name('index');

        Route::middleware(['auth'])->group(function () {
            Route::controller(DashboardController::class)->group(function () {
                Route::prefix('/dashboard')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/paketsoal', 'paketSoal')->name('dashboard-paketSoal');
                    Route::get('/paketsoal/{id}', 'detailPaketSoal')->name('dashboard-detailPaket');
                });
            });
            Route::controller(AdminController::class)->group(function () {
                Route::prefix('/admin')->group(function () {
                    Route::get('/', 'index')->name('index');

                    Route::prefix('/soal')->group(function () {
                        Route::get('/{tipe}', 'soal')->name('admin-soal');
                        Route::get('/tambah-soal/{tipe}', 'tambahSoal')->name('admin-tambahSoal');
                        Route::post('/tambah-soal', 'prosesTambahSoal')->name('admin-prosesTambahSoal');

                        Route::get('/ubah-soal/{id}/{tipe}', 'ubahSoal')->name('admin-ubahSoal');
                        Route::post('/ubah-soal', 'prosesUbahSoal')->name('admin-prosesUbahSoal');

                        Route::get('/ubah-jawaban/{id}/{tipe}', 'ubahJawaban')->name('admin-ubahJawaban');
                        Route::post('/ubah-jawaban', 'prosesUbahJawaban')->name('admin-prosesUbahJawaban');

                        Route::get('/tambah-jawaban/{id}/{tipe}', 'tambahJawaban')->name('admin-tambahJawaban');
                        Route::post('/tambah-jawaban', 'prosesTambahJawaban')->name('admin-prosesTambahJawaban');

                        Route::get('/hapus-soal/{id}/{tipe}', 'prosesHapusSoal')->name('admin-prosesHapusSoal');
                    });
                });
            });
        });

        Route::middleware(['guest'])->group(function () {
            Route::prefix('/paketsoal')->group(function () {
                Route::get('/', 'paketsoal')->name('paketsoal');
                Route::get('/detail/{id}', 'detailPaketSoal')->name('detailPaket');
                Route::get('/pilih/{id}', 'pilihPaketSoal')->name('pilihPaket');
            });
        });

    });

    Route::controller(AuthController::class)->group(function () {
        Route::middleware(['auth'])->group(function () {
            Route::get('/logout', 'logout')->name('admin-logout');
        });
        Route::middleware(['guest'])->group(function () {
            Route::get('/masuk', 'masuk')->name('masuk');
            Route::post('/masuk', 'prosesMasuk')->name('masuk');
            Route::get('/daftar', 'daftar')->name('daftar');
            Route::post('/daftar', 'prosesDaftar')->name('daftar');
        });
    });
});


// Route::prefix('/profile')->group(function () {
//     Route::controller(HomeController::class)->group(function () {
//         Route::middleware(['auth'])->group(function () {
//             Route::get('/', 'profile')->name('profile');

//             Route::controller(AuthController::class)->group(function () {
//                 Route::post('/logout', 'logout')->name('admin-logout');
//             });
//         });
//     });
// });

// Route::prefix('/dashboard')->group(function () {
//     Route::controller(DashboardController::class)->group(function () {
//         Route::middleware(['auth'])->group(function () {
//             Route::get('/', 'index')->name('admin-dashboard');
//             Route::prefix('/user')->group(function () {
//                 Route::controller(UserController::class)->group(function () {
//                     Route::get('/', 'index')->name('admin-user');
//                 });
//             });
//             Route::post('/logout', 'logout')->name('admin-logout');
//         });
//     });
// });

