<?php

namespace App\Http\Controllers;

use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\PaketSoal;
use App\Models\Transaksi;
use App\Models\BatchUjian;
use App\Models\JawabanSiswa;
use Carbon\Carbon;

class TestController extends Controller
{
    function index(){
        request()->session()->forget('siswa');
        request()->session()->forget('soal');
        request()->session()->forget('waktu_test');
        request()->session()->forget('waktu_pengerjaan');
        request()->session()->forget('point');
        request()->session()->forget('jawaban_siswa');
        // dd(request()->session()->forget('siswa'));
        return view('home.test.index');
    }
    function inputToken(Request $request){
        $batch = BatchUjian::with('transaksi')->orderBy('created_at', 'DESC')->get();
        $siswa = array();

        $i = 0;
        $u = 0;
        
        foreach($batch as $b){
            foreach($b->siswa as $siswa){
                if($siswa['token'] == $request->token){
                    if(array_key_exists('status', $siswa) && $siswa['status'] == 'selesai'){
                        return redirect()->route('test')->with(['error' => 'Anda sudah mengikuti tes']);
                    }else{
                        $paketSoal = PaketSoal::where('paket_soal_id', $b->transaksi['paket_soal_id'])->first();

                        $waktu_pengerjaan = $paketSoal->waktu_pengerjaan;
                        $waktu_pengerjaan_exp = explode(":", $waktu_pengerjaan);
                        $jam = $waktu_pengerjaan_exp[0] * 60;
                        $jam2 = $jam+$waktu_pengerjaan_exp[1];
    
                        $waktu_pelaksanaan = Carbon::parse($b->waktu_pelaksanaan)->toDateTimeString();
                        $maks_waktu_pengerjaan = Carbon::parse($b->waktu_pelaksanaan)->addMinutes($jam2)->toDateTimeString();
    
                        // BUG: Disini waktu masih menggunakan asia jakarta
                        $now = Carbon::parse(now('Asia/Jakarta'))->toDateTimeString();
    //                    dd($now >= $waktu_pelaksanaan && $now <= $maks_waktu_pengerjaan);
    
                        if ($now >= $waktu_pelaksanaan && $now <= $maks_waktu_pengerjaan) {
                            array_push($siswa, [
                                "paket_soal_id" => $b->transaksi['paket_soal_id'],
                                "batch_id" => $b->batch_id,
                                "user_id" => $b->transaksi['user_id'],
                                "nama_siswa" => $siswa['nama_siswa'],
                                "token" => $siswa['token'],
                                "tanggal_lahir" => $siswa['tanggal_lahir']
                            ]);
    
                            session(['siswa' => $siswa]);
                            return redirect()->route('test-validasi')->with(['success' => 'Token berhasil dimasukkan']);
                        }else if($now >= $maks_waktu_pengerjaan){
                            return redirect()->route('test')->with(['error' => 'Token sudah kadaluarsa']);
                        }else{
                            return redirect()->route('test')->with(['error' => 'Token dapat digunakan pada '.$b->waktu_pelaksanaan]);
                        }
                    }
                    
                }
                return redirect()->route('test')->with(['error' => 'Token yang anda masukkan salah, silahkan hubungi Guru anda']);
            }
        }

    }

    function validasiSiswa(Request $request){
        $data = $request->session()->get('siswa');
        return view('home.test.validasi', compact('data'));
    }

    function detail(Request $request){
//         dd($request->session()->get('siswa'));
        $siswa = $request->session()->get('siswa');
        $guru = User::select("name")->where('user_id', $siswa[0]['user_id'])->first();
        $paketSoal = PaketSoal::select("user_add", "nama_paket", "waktu_pengerjaan")->where('paket_soal_id', $siswa[0]['paket_soal_id'])->first();
        $batch = BatchUjian::select("waktu_pelaksanaan")->where('batch_id', $siswa[0]['batch_id'])->first();
        // dd($guru);

        return view('home.test.detail', compact('siswa', 'guru', 'paketSoal', 'batch'));
    }

    function mulaiTest(Request $request, $id){
        // dd($request->session()); 
        $data = $request->session()->get('siswa');
        $batch = BatchUjian::where('batch_id', $data[0]['batch_id'])->first();
        $paketSoal = PaketSoal::where('paket_soal_id', $data[0]['paket_soal_id'])->first();

        session(['batch_id' => $batch->batch_id]);
        session(['paket_soal_id' => $paketSoal->paket_soal_id]);
        // START RANDOMIZE SOAL
        $random_soal = $paketSoal->soal;
        // $soal_awal = array($random_soal);
        shuffle($random_soal);

        for($i = 0, $iMax = count($random_soal); $i < $iMax; $i++){
            for($u = 0, $uMax = count($random_soal); $u < $uMax; $u++){
                shuffle($random_soal[$i]['jawaban']);
            }
        }
        // for($i = 0, $iMax = count($random_soal); $i < $iMax; $i++){
        //     for($n = 0, $nMax = count($random_soal[$i]['jawaban']); $n < $nMax; $n++){
        //         // dd($random_soal[0]['jawaban']);
        //         // shuffle($random_soal[$n]['jawaban']);
        //     }
        // }

        // $request->session()->forget('soal');
        // dd(request()->session()->get('soal'));
        $soal_session = $request->session()->get('soal');
        if(!isset($soal_session)){
            session(['soal' => $random_soal]);
        }

        // Pembanding soal randomize dengan soal awal
        // dd($random_soal[0]['pertanyaan'].' = '.$random_soal[0]['jawaban'][0]['key_pilgan_id'].'|'.$random_soal[0]['jawaban'][1]['key_pilgan_id']
        //     .' = '.
        //     $soal_awal[0][0]['pertanyaan'].' = '.$soal_awal[0][0]['jawaban'][0]['key_pilgan_id'].'|'.$soal_awal[0][0]['jawaban'][1]['key_pilgan_id']);
        // END RANDOMIZE SOAL

        $waktu_awal_str = now("Asia/Jakarta")->toTimeString(); // 11.30
        $waktu_awal = strtotime(now("Asia/Jakarta")->toTimeString()); // 11.30

        $paketSoal2 = $paketSoal->waktu_pengerjaan;
        $waktu_akhir = strtotime($paketSoal2); // 02:30:00

        $countdown = $waktu_awal+$waktu_akhir;
//        $countdown = $waktu_awal+$waktu_akhir;

//        $request->session()->forget('waktu_test');
//        $request->session()->forget('waktu_pengerjaan');
        if(session()->get('waktu_test') === null){
            session([
                'waktu_test' => [
                    'waktu_mulai_siswa' => $waktu_awal,
                    'waktu_akhir' => $countdown,
                ]
            ]);
        }

        if(session()->get('waktu_test') !== null){
            session([
                'waktu_pengerjaan' => date('h:i:s', session()->get('waktu_test')['waktu_akhir']-$waktu_awal),
            ]);
        }
        // TODO: Ketika waktu pengerjaan habis, maka tidak bisa mengerjakan
//        dd(session()->get('waktu_test'));
//        dd(date('h:i:s', $waktu_akhir));
        // dd($random_soal);

        // LOGIKA COUNTDOWN NYA YAITU, WAKTU SAAT USER CLICK MULAI TES, DITAMBAHKAN WAKTU PENGERJAAN
        // CONTOH: WAKTU KLIK MULAI TEST = 09:00:00, BATAS WAKTU PENGERJAAN PAKET SOAL = 02:30:00, Maka WAKTU COUNTDOWN = 11:30:00
        // LALU WAKTU COUNTDOWN - WAKTU MULAI TEST (STATIS SESUAI USER CLICK)
        // dd(date('h:i:s', strtotime($countdown)-$waktu_awal));

        // dd($waktu_awal_str.' + '.$paketSoal2.' = '.$countdown.' | Waktu klik: '.$waktu_awal_str.' batas mengerjakan: '.$countdown);
        // $request->session()->forget('jawaban_siswa');
        if($request['pilihan']){
            session()->push('jawaban_siswa' ,[
                'soal_pg_id' => $request['soal_pg_id'],
                'pertanyaan' => $request['pertanyaan'],
                'jawaban' => [
                    'pilihan' => $request['pilihan'],
                    'value_pilihan' => $request['value_pilihan'],
                ]
            ]);
        }

        $jawaban_siswa = $request->session()->get('jawaban_siswa');
        $poin = 0;
        if(isset($jawaban_siswa)){
            foreach($jawaban_siswa as $siswa){
                $poin += $siswa['jawaban']['value_pilihan'];
            }
        }
        session(['point' => $poin]);
        return view('home.test.mulai', compact('random_soal', 'id'));
    }

    // function selesai(Request $request){
    //     JawabanSiswa::create([
    //         "batch_id" => $request->session()->get('id'),
    //         "paket_soal_id" => 1,
    //         "jawaban" => ['awdsw'],
    //         "result" => 412,
    //     ]);
    // }

    function kirimJawabanTest(Request $request){
        // dd($request->session());
        $jawaban = $request->session()->get('jawaban_siswa');
        $poin = $request->session()->get('point');
        
        $token = $request->session()->get('siswa')['token'];

        JawabanSiswa::create([
            "batch_id" => $request->session()->get('batch_id'),
            "paket_soal_id" => 1,
            "token" => $token,
            "jawaban" => $jawaban,
            "result" => $poin,
        ]);
        
        $batch = BatchUjian::where('batch_id', $request->session()->get('batch_id'))->first();
        $siswa_update = $batch->siswa;

        $index = array_search($token, array_column($siswa_update, 'token'));

        $siswa_update[$index]['status'] = "selesai";
        
        $batch->update([
            'siswa' => $siswa_update
        ]);

        request()->session()->forget('siswa');
        request()->session()->forget('soal');
        request()->session()->forget('waktu_test');
        request()->session()->forget('waktu_pengerjaan');
        request()->session()->forget('point');
        request()->session()->forget('jawaban_siswa');
        return redirect()->route('test');
    }

    function hapus_session_jawaban_siswa(Request $request){
        $request->session()->forget('jawaban_siswa');
        return redirect()->route('test-mulai', 1);
    }
}
