<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->string('url_pagina')->nullable()->change();
            $table->string('url_imagen')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('sponsors')
            ->whereNull('url_pagina')
            ->orWhereNull('url_imagen')
            ->update([
                'url_pagina' => DB::raw("COALESCE(url_pagina, '')"),
                'url_imagen' => DB::raw("COALESCE(url_imagen, '')"),
            ]);

        Schema::table('sponsors', function (Blueprint $table) {
            $table->string('url_pagina')->nullable(false)->change();
            $table->string('url_imagen')->nullable(false)->change();
        });
    }
};
