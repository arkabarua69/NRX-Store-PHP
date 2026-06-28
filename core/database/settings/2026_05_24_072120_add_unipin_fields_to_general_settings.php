<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.unipin_partner_id');
        $this->migrator->add('general.unipin_secret_key');
        $this->migrator->add('general.unipin_server_url');
    }
};
