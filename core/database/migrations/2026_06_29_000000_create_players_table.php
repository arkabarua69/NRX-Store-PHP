<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('nickname')->nullable();
            $table->timestamps();
        });

        $dbPath = database_path('../data/ff-topup.db');
        if (file_exists($dbPath)) {
            try {
                $sqlite = DB::connection('sqlite');
                $sqlite->setDatabasePath($dbPath);
                $sqlite->purge('sqlite');

                $players = $sqlite->table('players')->get();
                if ($players->count() > 0) {
                    $insertData = [];
                    foreach ($players as $p) {
                        $uid = $p->uid ?? $p->UID ?? '';
                        if (!empty($uid)) {
                            $insertData[] = [
                                'uid' => $uid,
                                'nickname' => $p->nickname ?? $p->Nickname ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    if (!empty($insertData)) {
                        DB::table('players')->insert($insertData);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not migrate players from SQLite: ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
