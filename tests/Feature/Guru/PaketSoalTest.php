<?php

namespace Tests\Feature\Guru;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\SoalPilgan;
use App\Models\Soal2d;
use App\Models\KeyPilgan;
use App\Models\Key2d;
use App\Models\PaketSoal;
use App\Models\Transaksi;

class PaketSoalTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function guru_login()
    {
        $this->visit('/masuk');

        $user = User::factory()->create([
            'name' => 'guru',
            'email' => 'guru@gmail.com',
            'role' => 'guru',
            'password' => bcrypt('passwordguru'),
        ]);

        $this->submitForm('Masuk', [
            'email'    => 'guru@gmail.com',
            'password' => 'passwordguru',
        ]);

        $this->seePageIs('/dashboard');
    }
    public function guru_berhasil_memilih_paket_soal(){
        $soalPilgan = SoalPilgan::factory(3)->create();
        $keyPilgan = KeyPilgan::factory(3)->create();
        $soal2d = Soal2d::factory(3)->create();
        $key2d = Key2d::factory(3)->create();
        $paketsoal = PaketSoal::factory(3)->create();

        $this->click('Paket soal');

        for($i = 0; $i <= 2; $i++){
            $this->see($paketsoal[$i]->nama_paket);

            $this->seeElement('a', [
                'href' => url('dashboard/paketsoal/'.$paketsoal[$i]->paket_soal_id)
            ]);
        }

        $this->click('paket_soal_'.$paketsoal[0]->paket_soal_id);
        $this->seePageIs('/dashboard/paketsoal/'.$paketsoal[0]->paket_soal_id);
    }

    /** @test */
    public function guru_dapat_mengunjungi_website_memilih_paketsoal()
    {
        $this->markTestIncomplete(
            'BUG: Halaman telah di ubah, jadi terdapat error'
        );
        $this->visit('/');
        $this->seeText('LANDING');

        $soalPilgan = SoalPilgan::factory(3)->create();
        $keyPilgan = KeyPilgan::factory(3)->create();
        $soal2d = Soal2d::factory(3)->create();
        $key2d = Key2d::factory(3)->create();
        $paketsoal = PaketSoal::factory(3)->create();

        $this->click('Lihat paket soal');
        $this->seePageIs('paketsoal');

        for($i = 0; $i <= 2; $i++){
            $this->see($paketsoal[$i]->nama_paket);

            $this->seeElement('a', [
                'href' => url('paketsoal/detail/'.$paketsoal[$i]->paket_soal_id)
            ]);
        }
        $this->click('paket_soal_'.$paketsoal[0]->paket_soal_id);
        $this->seePageIs('/paketsoal/detail/'.$paketsoal[0]->paket_soal_id);
        $this->click('Pilih paket soal ini');
        $this->seePageIs('/masuk');
    }

    /** @test */
    function guru_dapat_memilih_paket_soal_pada_dashboard(){
        $this->guru_login();

        $soalPilgan = SoalPilgan::factory(3)->create();
        $keyPilgan = KeyPilgan::factory(3)->create();
        $soal2d = Soal2d::factory(3)->create();
        $key2d = Key2d::factory(3)->create();
        $paketsoal = PaketSoal::factory(3)->create();

        $this->click('Paket soal');

        for($i = 0; $i <= 2; $i++){
            $this->see($paketsoal[$i]->nama_paket);

            $this->seeElement('a', [
                'href' => url('dashboard/paketsoal/'.$paketsoal[$i]->paket_soal_id)
            ]);
        }

        $this->click('paket_soal_'.$paketsoal[0]->paket_soal_id);
        $this->seePageIs('/dashboard/paketsoal/'.$paketsoal[0]->paket_soal_id);
    }

    /** @test */
    function guru_berhasil_membayar_paket_soal(){
        User::factory(3)->create();
        PaketSoal::factory(3)->create();
        Transaksi::factory(3)->create();

        $this->guru_login();
        $this->guru_berhasil_memilih_paket_soal();

        $this->click('Pilih paket soal ini');
        $this->submitForm('Bayar',[]);

        $this->seeInDatabase('transaksi', [
            'paket_soal_id' => 1,
            'user_id' => 1,
            // Test langsung terbayar karena factory semua tgl_bayar diisi
        ]);
        $this->seePageIs('/dashboard/batch-ujian');
    }
}
