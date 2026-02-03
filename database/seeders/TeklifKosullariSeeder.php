<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeklifKosulu;

class TeklifKosullariSeeder extends Seeder
{
    public function run(): void
    {
        TeklifKosulu::create([
            'baslik' => 'Standart Koşullar',
            'sira' => 1,
            'varsayilan' => true,
            'icerik' => '<p><strong>TEKLİF KOŞULLARI</strong></p>
<ul>
<li>Fiyatlarımıza %20 K.D.V Hariç olup, Ödeme Peşin olarak yapılacaktır. Kredi kartı ile ödemelerde vade farkı uygulanır.</li>
<li>Teklifimizi kabul etmeniz halinde sipariş için lütfen gönderilen teklifi onaylayıp imzalayarak tarafımıza e-mail veya faks ile gönderiniz</li>
<li>Dövizli Tekliflerde Serbest Piyasa Satış Kuru Dikkate alınmaktadır</li>
<li>TL Tekliflerde Ödeme Vadesine göre Serbest Piyasa USD/EUR satış kuru dikkate alınarak TL tekliflendirme yapılacaktır.</li>
<li>Cari Mutabakat döviz tutarı üzerinden yapılacaktır.</li>
<li>Teslim Süresi: <strong>5 iş günü</strong></li>
<li>TL ödemelerde ödemenin yapıldığı gün ki Serbest Piyasa USD/EUR Satış Kuru dikkate alınacaktır</li>
<li>Teklifimiz <strong>15 gün</strong> geçerlidir.</li>
<li>Fortilogger 1 yıl süre ile ücretsiz gelmektedir. Üreticinin sonraki yıllarda da bu hizmeti devam ettirecek şekilde bir taahhütü yoktur.</li>
<li><strong>DİKKAT!</strong> Lisansı zamanında yenilenmeyen cihazlarda. Eksik yapılan yenileme süresi kadar lisansda kesinti olacaktır. Bu kesinti 6 aydan fazla olamaz.</li>
</ul>'
        ]);

        TeklifKosulu::create([
            'baslik' => 'Lisans Satış Koşulları',
            'sira' => 2,
            'varsayilan' => false,
            'icerik' => '<p><strong>LİSANS SATIŞ KOŞULLARI</strong></p>
<ul>
<li>Lisans bedelleri <strong>yıllık</strong> olup, yenileme tarihi geldiğinde tekrar faturalandırılır.</li>
<li>Lisans yenileme bildirimleri <strong>30 gün önceden</strong> e-posta ile gönderilir.</li>
<li>Zamanında yenilenmeyen lisanslar için hizmet desteği <strong>durdurulur</strong>.</li>
<li>Lisans anahtarları e-posta ile iletilecek olup, <strong>kayıt altında tutulmalıdır</strong>.</li>
<li>Fiyatlar yıllık olarak %10-15 oranında artış gösterebilir.</li>
<li>Teknik destek lisans süresi boyunca <strong>7/24 e-posta</strong> ile sağlanır.</li>
<li>Acil durumlarda <strong>telefon desteği</strong> için ek ücret talep edilebilir.</li>
</ul>'
        ]);
    }
}
