<?php

namespace App\Filament\Resources\AutoVoucherResource\Pages;

use App\Filament\Resources\AutoVoucherResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;
use Throwable;

class CreateAutoVoucher extends CreateRecord
{
    protected static string $resource = AutoVoucherResource::class;

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $data = $this->form->getState();

            $data = $this->mutateFormDataBeforeCreate($data);

            foreach ($data['code'] as $code) {
                $data['code'] = $code;
                $data['code'] = is_array($data['code']) ? $data['code'] : [$data['code']];


                $this->record = $this->handleRecordCreation($data);
                $this->form->model($this->getRecord())->saveRelationships();
            }
            $this->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
            $this->rollBackDatabaseTransaction() :
            $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->rememberData();

        $this->getCreatedNotification()?->send();

        if ($another) {
            // Ensure that the form record is anonymized so that relationships aren't loaded.
            $this->form->model($this->getRecord()::class);
            $this->record = null;

            $this->fillForm();

            return;
        }

        $redirectUrl = $this->getRedirectUrl();

        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }
}
