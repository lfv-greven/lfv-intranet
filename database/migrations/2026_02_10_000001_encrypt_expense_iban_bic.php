<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE expenses MODIFY iban TEXT NULL');
            DB::statement('ALTER TABLE expenses MODIFY bic TEXT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE expenses ALTER COLUMN iban TYPE TEXT');
            DB::statement('ALTER TABLE expenses ALTER COLUMN bic TYPE TEXT');
        }

        DB::table('expenses')
            ->select('id', 'iban', 'bic')
            ->orderBy('id')
            ->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    $iban = $this->maybeEncrypt($row->iban);
                    $bic = $this->maybeEncrypt($row->bic);

                    DB::table('expenses')
                        ->where('id', $row->id)
                        ->update([
                            'iban' => $iban,
                            'bic' => $bic,
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Keep encrypted values; do not attempt to decrypt in a rollback.
    }

    private function maybeEncrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            Crypt::decryptString($value);

            return $value;
        } catch (\Throwable) {
            return Crypt::encryptString($value);
        }
    }
};
