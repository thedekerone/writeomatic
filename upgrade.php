<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function beforeUpdate(): bool
{
    return true;
}


function afterUpdate()
{

    /*
    Yeni gelen tabloları migrate ediyoruz.
    --force sebebi ise environmentin productionda olduğunda are you sure? diye bir uyarı veriyor bunu atlamak
    */
    Artisan::call('migrate', [
        '--force' => true
    ]);

    //Yeni eklenen tüm tabloları burada sorguluyoruz. Eğer migrate başarılı ve tablo içerisi boş ise default dataları içerisine alacak. Alttakilerin tümü bu şekilde.
    if (Schema::hasTable('frontend_tools')) {
        if (count(\App\Models\FrontendTools::all())==0){
            $path5 = resource_path('/dev_tools/frontend_tools.sql');
            DB::unprepared(file_get_contents($path5));
        }
    }

    if (Schema::hasTable('faq')) {
        if (count(\App\Models\Faq::all())==0){
            $path6 = resource_path('/dev_tools/faq.sql');
            DB::unprepared(file_get_contents($path6));
        }
    }


    if (Schema::hasTable('frontend_future')) {
        if (count(\App\Models\FrontendFuture::all())==0){
            $path7 = resource_path('/dev_tools/frontend_future.sql');
            DB::unprepared(file_get_contents($path7));
        }
    }


    if (Schema::hasTable('howitworks')) {
        if (count(\App\Models\HowitWorks::all())==0){
            $path8 = resource_path('/dev_tools/howitworks.sql');
            DB::unprepared(file_get_contents($path8));
        }
    }


    if (Schema::hasTable('testimonials')) {
        if (count(\App\Models\Testimonials::all())==0){
            $path9 = resource_path('/dev_tools/testimonials.sql');
            DB::unprepared(file_get_contents($path9));
        }
    }

    if (Schema::hasTable('frontend_who_is_for')) {
        if (count(\App\Models\FrontendForWho::all())==0){
            $path10 = resource_path('/dev_tools/frontend_who_is_for.sql');
            DB::unprepared(file_get_contents($path10));
        }
    }

    if (Schema::hasTable('frontend_generators')) {
        if (count(\App\Models\FrontendGenerators::all())==0){
            $path11 = resource_path('/dev_tools/frontend_generators.sql');
            DB::unprepared(file_get_contents($path11));
        }
    }

    if (Schema::hasTable('clients')) {
        if (count(\App\Models\Clients::all())==0){
            $path12 = resource_path('/dev_tools/clients.sql');
            DB::unprepared(file_get_contents($path12));
        }
    }

    if (!Schema::hasTable('health_check_result_history_items')) {
        $path13 = resource_path('/dev_tools/health_check_result_history_items.sql');
        DB::unprepared(file_get_contents($path13));
    }

    if (Schema::hasTable('email_templates')) {
        if (count(\App\Models\EmailTemplates::all()) == 0) {
            $path14 = resource_path('/dev_tools/email_templates.sql');
            DB::unprepared(file_get_contents($path14));
        }
    }


    return true;

}

?>
